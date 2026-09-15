<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\ModuloException;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ModuloAlumnos;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Alta de cuenta nueva: siempre nace con nivel de acceso "alumno".
     */
    public function create(ModuloAlumnos $alumnos): View
    {
        return view('auth.register', [
            'niveles' => $this->nivelesDisponibles($alumnos),
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request, ModuloAlumnos $alumnos): RedirectResponse
    {
        $niveles = $this->nivelesDisponibles($alumnos);
        $expedienteDisponible = $niveles !== [];

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellido_paterno' => ['required', 'string', 'max:100'],
            'apellido_materno' => ['nullable', 'string', 'max:100'],
            'fecha_nacimiento' => ['required', 'date', 'before:today'],
            'telefono_contacto' => ['required', 'string', 'max:20'],
            'nivel_id' => [$expedienteDisponible ? 'required' : 'nullable', 'integer'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => trim($validated['nombre'].' '.$validated['apellido_paterno']),
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'alumno',
        ]);

        $aviso = null;

        if ($expedienteDisponible) {
            try {
                $expediente = $alumnos->crear([
                    'nombre' => $validated['nombre'],
                    'apellido_paterno' => $validated['apellido_paterno'],
                    'apellido_materno' => $validated['apellido_materno'] ?? null,
                    'fecha_nacimiento' => $validated['fecha_nacimiento'],
                    'telefono_contacto' => $validated['telefono_contacto'],
                    'nivel_id' => $validated['nivel_id'],
                    'fecha_ingreso' => Carbon::today()->toDateString(),
                ]);

                $user->update(['alumno_id' => $expediente['id'] ?? null]);
            } catch (ModuloException|ValidationException $e) {
                // La cuenta ya existe: el alumno completará su expediente al entrar.
                $aviso = 'Tu cuenta quedó creada, pero no pudimos registrar tu expediente todavía.';
            }
        } else {
            $aviso = 'El módulo de alumnos no está disponible: completa tu expediente más tarde.';
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('dashboard')->with('aviso', $aviso);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function nivelesDisponibles(ModuloAlumnos $alumnos): array
    {
        try {
            return $alumnos->niveles();
        } catch (ModuloException) {
            return [];
        }
    }
}
