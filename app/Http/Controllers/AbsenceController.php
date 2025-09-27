<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserAbsence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class AbsenceController extends Controller
{
    /**
     * Lista nieobecności
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Budowa zapytania bazowego
        $query = UserAbsence::with(['user', 'approver']);

        // Filtrowanie uprawnień
        if ($user->isPracownik()) {
            // Pracownik widzi tylko swoje nieobecności
            $query->where('user_id', $user->id);
        } elseif ($user->isLider()) {
            // Lider widzi swoje oraz swojego zespołu (jeśli ma dostęp)
            // TODO: Zaimplementować logikę zespołu dla liderów
            $query->where('user_id', $user->id);
        }
        // Admin i kierownik widzą wszystkie (bez dodatkowych warunków)

        // Filtrowanie
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('end_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('start_date', '<=', $request->date_to);
        }

        // Sortowanie i paginacja
        $absences = $query->orderBy('start_date', 'desc')->paginate(15);

        // Dane do filtrów
        $users = ($user->isAdmin() || $user->isKierownik())
            ? User::active()->orderBy('name')->get()
            : collect([$user]);

        $types = [
            'urlop' => 'Urlop',
            'choroba' => 'Choroba',
            'szkolenie' => 'Szkolenie',
            'inne' => 'Inne'
        ];

        $statuses = [
            'oczekujaca' => 'Oczekująca',
            'zatwierdzona' => 'Zatwierdzona',
            'odrzucona' => 'Odrzucona'
        ];

        // Przygotuj dane dla kalendarza - użyj tego samego zapytania co dla listy ale tylko zatwierdzone
        $calendarQuery = clone $query;
        // Pokazuj wszystkie nieobecności w kalendarzu z różnymi kolorami dla statusów

        $calendarAbsences = $calendarQuery->with('user')
            ->get()
            ->map(function($absence) {
                return [
                    'id' => $absence->id,
                    'title' => $absence->user->name . ' - ' . ucfirst($absence->type) . ' (' . $this->getStatusLabel($absence->status) . ')',
                    'start' => $absence->start_date->format('Y-m-d'),
                    'end' => $absence->end_date->copy()->addDay()->format('Y-m-d'), // FullCalendar kończy dzień wcześniej
                    'color' => $this->getAbsenceColorByStatus($absence->type, $absence->status),
                    'user' => $absence->user->name,
                    'type' => $absence->type,
                    'status' => $absence->status,
                    'description' => $absence->description,
                ];
            });

        return view('absences.index', compact('absences', 'users', 'types', 'statuses', 'calendarAbsences'));
    }

    /**
     * Formularz dodawania nieobecności
     */
    public function create()
    {
        $user = Auth::user();

        // Określ dostępnych użytkowników
        $users = ($user->isAdmin() || $user->isKierownik())
            ? User::active()->orderBy('name')->get()
            : collect([$user]);

        $types = [
            'urlop' => 'Urlop',
            'choroba' => 'Choroba',
            'szkolenie' => 'Szkolenie',
            'inne' => 'Inne'
        ];

        return view('absences.create', compact('users', 'types'));
    }

    /**
     * Zapisz nową nieobecność
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'user_id' => [
                'required',
                'exists:users,id',
                function ($attribute, $value, $fail) use ($user) {
                    // Sprawdź uprawnienia do tworzenia nieobecności dla użytkownika
                    if (!$user->isAdmin() && !$user->isKierownik() && $value != $user->id) {
                        $fail('Nie masz uprawnień do dodawania nieobecności dla tego użytkownika.');
                    }
                },
            ],
            'start_date' => 'required|date',
            'end_date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->start_date && \Carbon\Carbon::parse($value)->lt(\Carbon\Carbon::parse($request->start_date))) {
                        $fail('Data zakończenia nie może być wcześniejsza niż data rozpoczęcia.');
                    }
                },
            ],
            'type' => 'required|in:urlop,choroba,szkolenie,inne',
            'description' => 'nullable|string|max:1000',
        ]);

        // Sprawdź kolizje z istniejącymi nieobecnościami
        $existingAbsence = UserAbsence::where('user_id', $validated['user_id'])
            ->where('status', '!=', 'odrzucona')
            ->where(function ($query) use ($validated) {
                $query->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                    ->orWhereBetween('end_date', [$validated['start_date'], $validated['end_date']])
                    ->orWhere(function ($subQuery) use ($validated) {
                        $subQuery->where('start_date', '<=', $validated['start_date'])
                                ->where('end_date', '>=', $validated['end_date']);
                    });
            })
            ->exists();

        if ($existingAbsence) {
            return back()->withErrors(['start_date' => 'W tym okresie już istnieje nieobecność dla tego użytkownika.']);
        }

        UserAbsence::create($validated);

        return redirect()->route('absences.index')
            ->with('success', 'Nieobecność została dodana pomyślnie.');
    }

    /**
     * Wyświetl szczegóły nieobecności
     */
    public function show(UserAbsence $absence)
    {
        $this->authorize('view', $absence);

        return view('absences.show', compact('absence'));
    }

    /**
     * Formularz edycji nieobecności
     */
    public function edit(UserAbsence $absence)
    {
        $this->authorize('update', $absence);

        $user = Auth::user();

        $users = ($user->isAdmin() || $user->isKierownik())
            ? User::active()->orderBy('name')->get()
            : collect([$absence->user]);

        $types = [
            'urlop' => 'Urlop',
            'choroba' => 'Choroba',
            'szkolenie' => 'Szkolenie',
            'inne' => 'Inne'
        ];

        return view('absences.edit', compact('absence', 'users', 'types'));
    }

    /**
     * Aktualizuj nieobecność
     */
    public function update(Request $request, UserAbsence $absence)
    {
        $this->authorize('update', $absence);

        $user = Auth::user();

        $validated = $request->validate([
            'user_id' => [
                'required',
                'exists:users,id',
                function ($attribute, $value, $fail) use ($user, $absence) {
                    if (!$user->isAdmin() && !$user->isKierownik() && $value != $user->id) {
                        $fail('Nie masz uprawnień do zmiany właściciela nieobecności.');
                    }
                },
            ],
            'start_date' => 'required|date',
            'end_date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->start_date && \Carbon\Carbon::parse($value)->lt(\Carbon\Carbon::parse($request->start_date))) {
                        $fail('Data zakończenia nie może być wcześniejsza niż data rozpoczęcia.');
                    }
                },
            ],
            'type' => 'required|in:urlop,choroba,szkolenie,inne',
            'description' => 'nullable|string|max:1000',
        ]);

        // Sprawdź kolizje (pomijając obecną nieobecność)
        $existingAbsence = UserAbsence::where('user_id', $validated['user_id'])
            ->where('id', '!=', $absence->id)
            ->where('status', '!=', 'odrzucona')
            ->where(function ($query) use ($validated) {
                $query->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                    ->orWhereBetween('end_date', [$validated['start_date'], $validated['end_date']])
                    ->orWhere(function ($subQuery) use ($validated) {
                        $subQuery->where('start_date', '<=', $validated['start_date'])
                                ->where('end_date', '>=', $validated['end_date']);
                    });
            })
            ->exists();

        if ($existingAbsence) {
            return back()->withErrors(['start_date' => 'W tym okresie już istnieje nieobecność dla tego użytkownika.']);
        }

        $absence->update($validated);

        return redirect()->route('absences.index')
            ->with('success', 'Nieobecność została zaktualizowana pomyślnie.');
    }

    /**
     * Usuń nieobecność
     */
    public function destroy(UserAbsence $absence)
    {
        $this->authorize('delete', $absence);

        $absence->delete();

        return redirect()->route('absences.index')
            ->with('success', 'Nieobecność została usunięta.');
    }

    /**
     * Zatwierdź nieobecność
     */
    public function approve(UserAbsence $absence)
    {
        $this->authorize('approve', $absence);

        $absence->update([
            'status' => 'zatwierdzona',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Nieobecność została zatwierdzona.');
    }

    /**
     * Odrzuć nieobecność
     */
    public function reject(UserAbsence $absence)
    {
        $this->authorize('approve', $absence);

        $absence->update([
            'status' => 'odrzucona',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Nieobecność została odrzucona.');
    }

    /**
     * Pobierz kolor dla typu nieobecności
     *
     * @param string $type
     * @return string
     */
    private function getAbsenceColor($type)
    {
        return match($type) {
            'urlop' => '#3b82f6', // blue
            'choroba' => '#ef4444', // red
            'szkolenie' => '#8b5cf6', // purple
            'inne' => '#6b7280', // gray
            default => '#6b7280'
        };
    }

    /**
     * Pobierz kolor dla nieobecności uwzględniając status
     *
     * @param string $type
     * @param string $status
     * @return string
     */
    private function getAbsenceColorByStatus($type, $status)
    {
        $baseColor = $this->getAbsenceColor($type);

        return match($status) {
            'zatwierdzona' => $baseColor, // Pełny kolor dla zatwierdzonych
            'oczekujaca' => $baseColor . '80', // 50% przezroczystość dla oczekujących
            'odrzucona' => '#94a3b8', // Szary kolor dla odrzuconych
            default => $baseColor
        };
    }

    /**
     * Pobierz etykietę statusu nieobecności
     *
     * @param string $status
     * @return string
     */
    private function getStatusLabel($status)
    {
        return match($status) {
            'oczekujaca' => 'Oczekująca',
            'zatwierdzona' => 'Zatwierdzona',
            'odrzucona' => 'Odrzucona',
            default => ucfirst($status)
        };
    }
}
