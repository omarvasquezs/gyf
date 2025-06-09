<?php

namespace App\Http\Controllers;

use App\Models\DisenoLuna;
use Illuminate\Http\Request;

class DisenoLunaController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $query = DisenoLuna::query();

        if ($request->filled('nombre')) {
            $query->where('nombre', 'like', '%' . $request->nombre . '%');
        }

        return $query->orderBy('id', 'desc')->paginate($perPage);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);
        return DisenoLuna::create($request->all());
    }

    public function show($id)
    {
        return DisenoLuna::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $disenoLuna = DisenoLuna::findOrFail($id);
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);
        $disenoLuna->update($request->all());
        return $disenoLuna;
    }

    public function destroy($id)
    {
        $disenoLuna = DisenoLuna::findOrFail($id);
        $disenoLuna->delete();
        return response()->json(null, 204);
    }
}
