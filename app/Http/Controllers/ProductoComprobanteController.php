<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductoComprobante;
use App\Models\ProductoComprobanteItem;
use App\Models\Comprobante;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductoComprobanteController extends Controller
{
    public function index()
    {
        $productoComprobantes = ProductoComprobante::whereNull('comprobante_id')->get();
        return response()->json(['data' => $productoComprobantes]);
    }

    public function show($id)
    {
        $productoComprobante = ProductoComprobante::findOrFail($id);
        $items = ProductoComprobanteItem::where('producto_comprobante_id', $id)->with('stock')->get();
        return response()->json(['productoComprobante' => $productoComprobante, 'items' => $items]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombres' => 'required|string',
            'telefono' => 'nullable|string', // Changed 'required' to 'nullable'
            'correo' => 'nullable|string|email',
            'monto_total' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.stock_id' => 'required|integer|exists:stock,id',
            'items.*.cantidad' => 'required|integer|min:1',
            'items.*.precio' => 'required|numeric|min:0'
        ]);

        try {
            $productoComprobante = ProductoComprobante::create([
                'nombres' => $request->nombres,
                'telefono' => $request->telefono,
                'correo' => $request->correo,
                'monto_total' => $request->monto_total,
                'comprobante_id' => null
            ]);

            foreach ($request->items as $item) {
                ProductoComprobanteItem::create([
                    'producto_comprobante_id' => $productoComprobante->id,
                    'stock_id' => $item['stock_id'],
                    'cantidad' => $item['cantidad'],
                    'precio' => $item['precio']
                ]);
            }

            return response()->json(['producto_comprobante' => $productoComprobante, 'message' => 'Producto comprobante and items created successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error creating producto comprobante/items: ' . $e->getMessage()], 500);
        }
    }
    
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            
            $productoComprobante = ProductoComprobante::findOrFail($id);
            
            // Check if this producto comprobante has already been processed (has a comprobante_id)
            if ($productoComprobante->comprobante_id) {
                return response()->json(['error' => 'No se puede eliminar una solicitud que ya ha sido procesada'], 400);
            }
            
            // Get all items to restore stock quantities
            $items = ProductoComprobanteItem::where('producto_comprobante_id', $id)->get();
            
            // Restore stock quantities
            foreach ($items as $item) {
                $stock = Stock::findOrFail($item->stock_id);
                $stock->num_stock += $item->cantidad; // Add back the quantity
                $stock->save();
                
                Log::info('Stock restored:', [
                    'stock_id' => $stock->id,
                    'descripcion' => $stock->descripcion,
                    'cantidad_restored' => $item->cantidad,
                    'new_stock' => $stock->num_stock
                ]);
            }
            
            // Delete the items first (foreign key constraint)
            ProductoComprobanteItem::where('producto_comprobante_id', $id)->delete();
            
            // Delete the producto comprobante
            $productoComprobante->delete();
            
            DB::commit();
            
            return response()->json(['message' => 'Solicitud eliminada y stock restaurado correctamente']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting producto comprobante: ' . $e->getMessage());
            return response()->json(['error' => 'Error al eliminar la solicitud: ' . $e->getMessage()], 500);
        }
    }
}
