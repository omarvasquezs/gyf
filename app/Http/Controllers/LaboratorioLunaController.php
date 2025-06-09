<?php

namespace App\Http\Controllers;

use App\Models\LaboratorioLuna;
use Illuminate\Http\Request;

class LaboratorioLunaController extends Controller
{
    public function index()
    {
        return LaboratorioLuna::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);
        return LaboratorioLuna::create($request->all());
    }

    public function show($id)
    {
        return LaboratorioLuna::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $laboratorioLuna = LaboratorioLuna::findOrFail($id);
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);
        $laboratorioLuna->update($request->all());
        return $laboratorioLuna;
    }

    public function destroy($id)
    {
        $laboratorioLuna = LaboratorioLuna::findOrFail($id);
        $laboratorioLuna->delete();
        return response()->json(null, 204);
    }
}
