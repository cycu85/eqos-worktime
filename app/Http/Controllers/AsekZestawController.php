<?php

namespace App\Http\Controllers;

use App\Models\AsekZestaw;
use Illuminate\Http\Request;

/**
 * Kontroler do wyświetlania zestawów z zewnętrznej bazy ASEK
 * Tylko odczyt danych
 */
class AsekZestawController extends Controller
{
    /**
     * Wyświetl listę zestawów z filtrowaniem i sortowaniem
     */
    public function index(Request $request)
    {
        $query = AsekZestaw::query();

        // Filtrowanie po nazwie/opisie
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('who_use', 'like', "%{$search}%");
            });
        }

        // Filtrowanie po statusie
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Sortowanie
        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');

        $allowedSortFields = ['id', 'name', 'who_use', 'status', 'date_mod'];
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'name';
        }

        $query->orderBy($sortField, $sortDirection);

        $zestawy = $query->paginate(20)->withQueryString();

        return view('asek.zestawy.index', compact('zestawy'));
    }

    /**
     * Wyświetl szczegóły zestawu wraz z elementami
     */
    public function show(int $id)
    {
        $zestaw = AsekZestaw::findOrFail($id);
        $tickets = $zestaw->tickets()->orderBy('name')->get();

        return view('asek.zestawy.show', compact('zestaw', 'tickets'));
    }
}
