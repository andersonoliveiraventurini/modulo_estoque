<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthenticateUser
{
    public function __invoke(array $credentials): void
    {
        $user = User::where('email', $credentials['email'])->first();

        if ($user && $user->blocked) {
            throw ValidationException::withMessages([
                'email' => __('Sua conta está bloqueada. Entre em contato com o administrador.'),
            ]);
        }

        if (! Auth::attempt($credentials, $credentials['remember'] ?? false)) {
            throw ValidationException::withMessages([
                'email' => __('As credenciais fornecidas estão incorretas.'),
            ]);
        }
    }
}
