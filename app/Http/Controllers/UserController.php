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

}
