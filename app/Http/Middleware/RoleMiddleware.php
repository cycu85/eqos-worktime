<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware do kontroli dostępu na podstawie ról użytkowników
 *
 * Sprawdza czy zalogowany użytkownik ma jedną z wymaganych ról
 * przed udzieleniem dostępu do zasobu.
 */
class RoleMiddleware
{
    /**
     * Obsłuż przychodzące żądanie HTTP
     *
     * Sprawdza czy użytkownik jest zalogowany i ma jedną z wymaganych ról.
     *
     * @param Request $request Żądanie HTTP
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     * @param string ...$roles Lista wymaganych ról (admin, kierownik, lider, pracownik)
     * @return Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        
        if (!in_array($user->role, $roles)) {
            abort(403, 'Nie masz uprawnień do dostępu do tej strony.');
        }

        return $next($request);
    }
}
