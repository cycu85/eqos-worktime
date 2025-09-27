<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserAbsence;
use Illuminate\Auth\Access\Response;

class UserAbsencePolicy
{
    /**
     * Określ czy użytkownik może przeglądać nieobecności
     */
    public function viewAny(User $user): bool
    {
        // Wszyscy zalogowani użytkownicy mogą przeglądać nieobecności
        // (filtrowanie odbywa się w kontrolerze)
        return true;
    }

    /**
     * Określ czy użytkownik może zobaczyć konkretną nieobecność
     */
    public function view(User $user, UserAbsence $userAbsence): bool
    {
        // Admin i kierownik mogą zobaczyć wszystkie
        if ($user->isAdmin() || $user->isKierownik()) {
            return true;
        }

        // Użytkownik może zobaczyć własne nieobecności
        if ($userAbsence->user_id === $user->id) {
            return true;
        }

        // Lider może zobaczyć nieobecności swojego zespołu
        // TODO: Zaimplementować logikę zespołu dla liderów
        if ($user->isLider()) {
            return $userAbsence->user_id === $user->id;
        }

        return false;
    }

    /**
     * Określ czy użytkownik może tworzyć nieobecności
     */
    public function create(User $user): bool
    {
        // Wszyscy zalogowani użytkownicy mogą tworzyć nieobecności
        return true;
    }

    /**
     * Określ czy użytkownik może edytować nieobecność
     */
    public function update(User $user, UserAbsence $userAbsence): bool
    {
        // Admin i kierownik mogą edytować wszystkie
        if ($user->isAdmin() || $user->isKierownik()) {
            return true;
        }

        // Właściciel może edytować własne nieobecności tylko gdy są oczekujące
        if ($userAbsence->user_id === $user->id) {
            return $userAbsence->status === 'oczekujaca';
        }

        return false;
    }

    /**
     * Określ czy użytkownik może usunąć nieobecność
     */
    public function delete(User $user, UserAbsence $userAbsence): bool
    {
        // Admin i kierownik mogą usuwać wszystkie
        if ($user->isAdmin() || $user->isKierownik()) {
            return true;
        }

        // Właściciel może usuwać własne nieobecności tylko gdy są oczekujące
        if ($userAbsence->user_id === $user->id) {
            return $userAbsence->status === 'oczekujaca';
        }

        return false;
    }

    /**
     * Określ czy użytkownik może zatwierdzać/odrzucać nieobecności
     */
    public function approve(User $user, UserAbsence $userAbsence): bool
    {
        // Tylko admin i kierownik mogą zatwierdzać
        if (!($user->isAdmin() || $user->isKierownik())) {
            return false;
        }

        // Nie można zatwierdzać własnych nieobecności
        if ($userAbsence->user_id === $user->id) {
            return false;
        }

        // Można zatwierdzać tylko oczekujące nieobecności
        return $userAbsence->status === 'oczekujaca';
    }

    /**
     * Określ czy użytkownik może przywracać nieobecności
     */
    public function restore(User $user, UserAbsence $userAbsence): bool
    {
        return $user->isAdmin();
    }

    /**
     * Określ czy użytkownik może trwale usuwać nieobecności
     */
    public function forceDelete(User $user, UserAbsence $userAbsence): bool
    {
        return $user->isAdmin();
    }
}
