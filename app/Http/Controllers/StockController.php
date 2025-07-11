<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Imports\StockImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\UploadedFile;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $query = Stock::with(['marca', 'material']); // Changed from proveedor to marca
        $perPage = 10; // Default for CatalogoLentes

        // Text search for descripcion (previously producto)
        if ($request->filled('descripcion')) {
            $query->where('descripcion', 'like', '%' . $request->descripcion . '%');
        }
        
        // Price filters for CatalogoLentes
        if ($request->filled('precio_min')) {
            $query->where('precio', '>=', floatval($request->precio_min));
        }
        
        if ($request->filled('precio_max')) {
            $query->where('precio', '<=', floatval($request->precio_max));
        }

        // Single precio filter for ControlStockCrud
        if ($request->filled('precio') && !$request->filled('precio_min') && !$request->filled('precio_max')) {
            $query->where('precio', 'like', $request->precio . '%');
            $perPage = 10; // Set to 10 for ControlStockCrud
        }

        // Filter by tipo_producto (multi-select support)
        if ($request->filled('tipo_producto')) {
            $tipoProductos = explode(',', $request->tipo_producto);
            if (count($tipoProductos) === 1 && $tipoProductos[0] === '') {
                // 'Todos' selected, do not filter
            } else {
                $query->whereIn('tipo_producto', $tipoProductos);
            }
        }

        // Filter by marca instead of proveedor
        if ($request->filled('marca')) {
            $query->whereHas('marca', function ($q) use ($request) {
                $q->where('marca', 'like', '%' . $request->marca . '%');
            });
        }

        // Filter by codigo
        if ($request->filled('codigo')) {
            $query->where('codigo', 'like', '%' . $request->codigo . '%');
        }

        $result = $query->orderBy('created_at', 'desc')->paginate($perPage);
        return response()->json([
            'current_page' => $result->currentPage(),
            'data' => $result->items(),
            'first_page_url' => $result->url(1),
            'from' => $result->firstItem(),
            'last_page' => $result->lastPage(),
            'last_page_url' => $result->url($result->lastPage()),
            'next_page_url' => $result->nextPageUrl(),
            'per_page' => $result->perPage(),
            'prev_page_url' => $result->previousPageUrl(),
            'to' => $result->lastItem(),
            'total' => $result->total(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'descripcion' => 'nullable|string|max:255',
            'imagen' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'precio' => 'required|numeric|min:0',
            'tipo_producto' => 'required|in:l,m,c,u',
            // Lunas fields
            'id_tipo_luna' => 'required_if:tipo_producto,u|nullable|exists:tipo_lunas,id',
            'id_diseno_luna' => 'required_if:tipo_producto,u|nullable|exists:diseno_lunas,id',
            'id_laboratorio_luna' => 'required_if:tipo_producto,u|nullable|exists:laboratorios_lunas,id',
            'indice_luna' => 'required_if:tipo_producto,u|nullable',
            // Otros campos solo requeridos si NO es Lunas
            'id_marca' => 'required_unless:tipo_producto,u|nullable|exists:marcas,id',
            'codigo' => 'nullable|string|max:255',
            'genero' => 'nullable|in:H,M,N,U',
            'id_material' => 'nullable|exists:materiales,id',
            'fecha_compra' => 'nullable|date',
            'num_stock' => 'required_unless:tipo_producto,u|nullable|integer|min:0'
        ]);

        try {
            $validated['imagen'] = null;
            if ($request->hasFile('imagen')) {
                $file = $request->file('imagen');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = public_path('images/stock');
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }
                $file->move($path, $filename);
                $validated['imagen'] = $filename;
            }

            $stock = Stock::create([
                'descripcion' => $validated['descripcion'] ?? null,
                'imagen' => $validated['imagen'],
                'precio' => $validated['precio'],
                'tipo_producto' => $validated['tipo_producto'],
                // Lunas
                'id_tipo_luna' => $validated['tipo_producto'] === 'u' ? $validated['id_tipo_luna'] : null,
                'id_diseno_luna' => $validated['tipo_producto'] === 'u' ? $validated['id_diseno_luna'] : null,
                'id_laboratorio_luna' => $validated['tipo_producto'] === 'u' ? $validated['id_laboratorio_luna'] : null,
                'indice_luna' => $validated['tipo_producto'] === 'u' ? $validated['indice_luna'] : null,
                // Common fields for all product types
                'id_marca' => $validated['id_marca'] ?? null,
                'codigo' => $validated['codigo'] ?? null,
                'id_material' => $validated['id_material'] ?? null,
                // Non-Luna specific fields
                'genero' => $validated['tipo_producto'] !== 'u' ? ($validated['genero'] ?? null) : null,
                'fecha_compra' => $validated['tipo_producto'] !== 'u' ? ($validated['fecha_compra'] ?? null) : null,
                'num_stock' => $validated['tipo_producto'] === 'u' ? null : $validated['num_stock'],
            ]);

            return response()->json($stock, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $stock = Stock::with(['marca', 'material'])->findOrFail($id); // Changed from proveedor to marca
        return response()->json($stock);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'descripcion' => 'nullable|string|max:255',
            'precio' => 'required|numeric|min:0',
            'tipo_producto' => 'required|in:l,m,c,u',
            // Lunas fields
            'id_tipo_luna' => 'required_if:tipo_producto,u|nullable|exists:tipo_lunas,id',
            'id_diseno_luna' => 'required_if:tipo_producto,u|nullable|exists:diseno_lunas,id',
            'id_laboratorio_luna' => 'required_if:tipo_producto,u|nullable|exists:laboratorios_lunas,id',
            'indice_luna' => 'required_if:tipo_producto,u|nullable',
            // Otros campos solo requeridos si NO es Lunas
            'id_marca' => 'required_unless:tipo_producto,u|nullable|exists:marcas,id',
            'imagen' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'codigo' => 'nullable|string|max:255',
            'genero' => 'nullable|in:H,M,N,U',
            'id_material' => 'nullable|exists:materiales,id',
            'fecha_compra' => 'nullable|date',
            'num_stock' => 'required_unless:tipo_producto,u|nullable|integer|min:0'
        ]);

        try {
            $stock = Stock::findOrFail($id);
            $updateData = [
                'descripcion' => $validated['descripcion'] ?? $stock->descripcion,
                'precio' => $validated['precio'],
                'tipo_producto' => $validated['tipo_producto'],
                // Lunas
                'id_tipo_luna' => $validated['tipo_producto'] === 'u' ? $validated['id_tipo_luna'] : null,
                'id_diseno_luna' => $validated['tipo_producto'] === 'u' ? $validated['id_diseno_luna'] : null,
                'id_laboratorio_luna' => $validated['tipo_producto'] === 'u' ? $validated['id_laboratorio_luna'] : null,
                'indice_luna' => $validated['tipo_producto'] === 'u' ? $validated['indice_luna'] : null,
                // Common fields for all product types
                'id_marca' => $validated['id_marca'] ?? null,
                'codigo' => $validated['codigo'] ?? null,
                'id_material' => $validated['id_material'] ?? null,
                // Non-Luna specific fields
                'genero' => $validated['tipo_producto'] !== 'u' ? ($validated['genero'] ?? null) : null,
                'fecha_compra' => $validated['tipo_producto'] !== 'u' ? ($validated['fecha_compra'] ?? null) : null,
                'num_stock' => $validated['tipo_producto'] === 'u' ? null : $validated['num_stock'],
            ];
            if ($request->hasFile('imagen')) {
                if ($stock->imagen) {
                    $oldPath = public_path('images/stock/' . $stock->imagen);
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }
                $file = $request->file('imagen');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('images/stock'), $filename);
                $updateData['imagen'] = $filename;
            }
            $stock->update($updateData);
            return response()->json($stock);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar el producto: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $stock = Stock::findOrFail($id);
        
        // Delete image file
        if ($stock->imagen) {
            $path = public_path('images/stock/' . $stock->imagen);
            if (file_exists($path)) {
                unlink($path);
            }
        }

        $stock->delete();
        return response()->json(null, 204);
    }

    public function updateStock(Request $request)
    {
        try {
            $updates = $request->input('updates');
            Log::info('Stock update request received:', ['updates' => $updates]);
            
            DB::beginTransaction();
            
            foreach ($updates as $update) {
                $stock = Stock::findOrFail($update['id']);
                if ($stock->tipo_producto !== 'u') {
                    $stock->num_stock = $update['num_stock'];
                    $stock->save();
                    Log::info('Stock updated:', [
                        'id' => $stock->id,
                        'descripcion' => $stock->descripcion, // Updated from producto to descripcion
                        'old_stock' => $stock->getOriginal('num_stock'),
                        'new_stock' => $stock->num_stock
                    ]);
                }
            }
            
            DB::commit();
            return response()->json(['message' => 'Stock actualizado correctamente']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error actualizando stock: ' . $e->getMessage());
            return response()->json(['error' => 'Error al actualizar el stock: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Import stock from Excel/CSV file
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,txt',
        ]);

        try {
            $import = new \App\Imports\StockImport();
            \Maatwebsite\Excel\Facades\Excel::import($import, $request->file('file'));
            $result = [
                'imported' => $import->imported,
                'errors' => $import->errors,
                'message' => ($import->imported > 0 ? 'Importación completada.' : 'No se importaron registros.')
            ];
            return response()->json($result, empty($import->errors) ? 200 : 207);
        } catch (\Throwable $e) {
            Log::error('Stock import error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'imported' => 0,
                'errors' => [[ 'row' => [], 'errors' => [$e->getMessage()] ]],
                'message' => 'Error al importar: ' . $e->getMessage()
            ], 500);
        }
    }
}
