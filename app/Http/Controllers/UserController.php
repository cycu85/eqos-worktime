<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * Kontroler do zarządzania użytkownikami
 *
 * Obsługuje CRUD operacje dla użytkowników systemu,
 * włączając role-based permissions i walidację.
 */
class UserController extends Controller
{
    /**
     * Wyświetl listę użytkowników z filtrowaniem i sortowaniem
     *
     * @param Request $request Żądanie HTTP zawierające parametry filtrów
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);
        
        // Build query
        $query = User::withCount('tasks');
        
        // Apply search
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }
        
        // Apply role filter
        if ($request->filled('role')) {
            $query->where('role', $request->get('role'));
        }
        
        // Apply sorting
        $sortBy = $request->get('sort', 'name');
        $sortOrder = $request->get('direction', 'asc');
        
        $allowedSorts = ['name', 'email', 'role', 'created_at', 'last_login_at', 'tasks_count'];
        if (in_array($sortBy, $allowedSorts)) {
            if ($sortBy === 'tasks_count') {
                $query->orderBy('tasks_count', $sortOrder);
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }
        } else {
            $query->orderBy('name', 'asc');
        }
        
        $users = $query->paginate(15)->appends($request->query());
        
        // Get filter options
        $roles = [
            'admin' => 'Administrator',
            'kierownik' => 'Kierownik',
            'lider' => 'Lider',
            'pracownik' => 'Pracownik'
        ];
        
        return view('users.index', compact('users', 'roles'));
    }

    /**
     * Wyświetl formularz tworzenia nowego użytkownika
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $this->authorize('create', User::class);
        
        return view('users.create');
    }

    /**
     * Zapisz nowego użytkownika w bazie danych
     *
     * @param Request $request Dane użytkownika z formularza
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->authorize('create', User::class);
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:admin,kierownik,lider,pracownik'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return redirect()->route('users.index')->with('success', 'Użytkownik został utworzony pomyślnie.');
    }

    /**
     * Wyświetl szczegóły użytkownika
     *
     * @param User $user Użytkownik
     * @return \Illuminate\View\View
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);
        
        $user->load(['tasks' => function ($query) {
            $query->with('vehicles')->orderBy('start_date', 'desc');
        }]);
        
        return view('users.show', compact('user'));
    }

    /**
     * Wyświetl formularz edycji użytkownika
     *
     * @param User $user Użytkownik
     * @return \Illuminate\View\View
     */
    public function edit(User $user)
    {
        $this->authorize('update', $user);
        
        return view('users.edit', compact('user'));
    }

    /**
     * Zaktualizuj użytkownika w bazie danych
     *
     * @param Request $request Dane użytkownika z formularza
     * @param User $user Użytkownik do aktualizacji
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:admin,kierownik,lider,pracownik'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ]);

        if ($validated['password']) {
            $user->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        return redirect()->route('users.index')->with('success', 'Użytkownik został zaktualizowany pomyślnie.');
    }

    /**
     * Usuń użytkownika z bazy danych
     *
     * @param User $user Użytkownik do usunięcia
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);
        
        // Nie można usunąć siebie
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'Nie możesz usunąć swojego własnego konta.');
        }
        
        // Sprawdź czy użytkownik ma przypisane zadania
        if ($user->tasks()->count() > 0) {
            return redirect()->route('users.index')->with('error', 'Nie można usunąć użytkownika, który ma przypisane zadania.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Użytkownik został usunięty pomyślnie.');
    }
}
