<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        return view('paginas.usuarios.index');
    }

    public function show($id)
    {
        $usuario = User::findOrFail($id);
        return view('paginas.usuarios.show', compact('usuario'));
    }

    public function edit($id)
    {
        $usuario = User::findOrFail($id);
        return view('paginas.usuarios.edit', compact('usuario'));
    }

    public function update(Request $request, $id)
    {
        $usuario = User::findOrFail($id);
        $usuario->update($request->all());
        return redirect()->route('usuarios.show', $usuario->id);
    }

    public function destroy($id)
    {
        $usuario = User::findOrFail($id);
        $usuario->delete();
        return redirect()->route('usuarios.index');
    }

    public function create()
    {
        return view('paginas.usuarios.create');
    }

    public function store(Request $request)
    {
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'cpf' => $request->cpf,
            'password' => bcrypt($request->password),
            'criado_por' => Auth::id(),
        ]);

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuário cadastrado com sucesso!');
    }

    public function editPassword(User $user)
    {
        return view('paginas.usuarios.edit-password', compact('user'));
    }

    public function updatePassword(UpdateUserRequest $request, User $user)
    {
        

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('paginas.usuarios.index')
            ->with('success', 'Senha do usuário atualizada com sucesso!');
    }

    public function toggleBlock(User $user)
    {
        $user->is_blocked = !$user->is_blocked;
        $user->save();

        $status = $user->is_blocked ? 'bloqueado' : 'desbloqueado';

        return redirect()->route('usuarios.index')
            ->with('success', "Usuário {$status} com sucesso!");
    }

}
