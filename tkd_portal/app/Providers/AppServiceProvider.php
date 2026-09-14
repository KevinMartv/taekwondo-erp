<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::define('is-admin', fn (User $user) => $user->esAdmin());

        Gate::define('is-alumno', fn (User $user) => $user->esAlumno());
    }
}