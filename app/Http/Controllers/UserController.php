<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

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
}
