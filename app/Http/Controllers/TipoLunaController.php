<?php

namespace App\Http\Controllers;

use App\Models\TipoLuna;
use Illuminate\Http\Request;

class TipoLunaController extends Controller
{
    public function index()
    {
        return TipoLuna::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);
        return TipoLuna::create($request->all());
    }

    public function show($id)
    {
        return TipoLuna::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $tipoLuna = TipoLuna::findOrFail($id);
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);
        $tipoLuna->update($request->all());
        return $tipoLuna;
    }

    public function destroy($id)
    {
        $tipoLuna = TipoLuna::findOrFail($id);
        $tipoLuna->delete();
        return response()->json(null, 204);
    }
}
