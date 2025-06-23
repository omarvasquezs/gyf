<?php

namespace App\Imports;

use App\Models\Stock;
use App\Models\Marca;
use App\Models\Material;
use App\Models\DisenoLuna;
use App\Models\TipoLuna;
use App\Models\LaboratorioLuna;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\Importable;

class StockImport implements OnEachRow, WithHeadingRow
{
    use Importable;

    public $errors = [];
    public $imported = 0;

    public function onRow(Row $row)
    {
        $data = $row->toArray();

        // Map human-friendly values to codes/IDs
        $data['tipo_producto'] = $this->mapTipoProducto($data['tipo_producto'] ?? null);
        $data['genero'] = $this->mapGenero($data['genero'] ?? null);

        // Defensive: if tipo_producto is not a single char, set to null to trigger validation error
        if (!is_string($data['tipo_producto']) || strlen($data['tipo_producto']) !== 1) {
            $data['tipo_producto'] = null;
        }
        if (!empty($data['genero']) && (!is_string($data['genero']) || strlen($data['genero']) !== 1)) {
            $data['genero'] = null;
        }

        // Map Luna fields by name if not numeric
        if (!empty($data['id_tipo_luna']) && !is_numeric($data['id_tipo_luna'])) {
            $tipo = TipoLuna::where('nombre', $data['id_tipo_luna'])->first();
            $data['id_tipo_luna'] = $tipo ? $tipo->id : null;
        }
        if (!empty($data['id_diseno_luna']) && !is_numeric($data['id_diseno_luna'])) {
            $diseno = DisenoLuna::where('nombre', $data['id_diseno_luna'])->first();
            $data['id_diseno_luna'] = $diseno ? $diseno->id : null;
        }
        if (!empty($data['id_laboratorio_luna']) && !is_numeric($data['id_laboratorio_luna'])) {
            $lab = LaboratorioLuna::where('nombre', $data['id_laboratorio_luna'])->first();
            $data['id_laboratorio_luna'] = $lab ? $lab->id : null;
        }

        // Handle foreign keys (marca, material) by name or id
        $id_marca = $data['id_marca'] ?? null;
        if (empty($id_marca) && !empty($data['marca'])) {
            $marca = Marca::firstOrCreate(['marca' => $data['marca']]);
            $id_marca = $marca->id;
        }
        $id_material = $data['id_material'] ?? null;
        if (empty($id_material) && !empty($data['material'])) {
            $material = Material::firstOrCreate(['material' => $data['material']]);
            $id_material = $material->id;
        }

        // Validate
        $validator = Validator::make($data, [
            'codigo' => 'required|string|max:255',
            'tipo_producto' => 'required|string|max:1',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric',
            'num_stock' => 'nullable|integer',
            'id_marca' => 'nullable|integer|exists:marcas,id',
            'id_material' => 'nullable|integer|exists:materiales,id',
            'fecha_compra' => 'nullable|date',
            'genero' => 'nullable|string|max:1',
            'id_tipo_luna' => 'nullable|integer',
            'id_diseno_luna' => 'nullable|integer',
            'id_laboratorio_luna' => 'nullable|integer',
            'indice' => 'nullable',
            'marca' => 'nullable|string',
            'material' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            $this->errors[] = [
                'row' => $data,
                'errors' => $validator->errors()->all(),
            ];
            return;
        }

        // Upsert by codigo if exists, else create
        Stock::updateOrCreate(
            ['codigo' => $data['codigo']],
            [
                'tipo_producto' => $data['tipo_producto'],
                'descripcion' => $data['descripcion'] ?? '',
                'precio' => $data['precio'],
                'id_marca' => $id_marca,
                'id_material' => $id_material,
                'fecha_compra' => $data['fecha_compra'] ?? null,
                'num_stock' => $data['num_stock'] ?? 0,
                'genero' => $data['genero'] ?? null,
                // Lunas fields
                'id_tipo_luna' => $data['id_tipo_luna'] ?? null,
                'id_diseno_luna' => $data['id_diseno_luna'] ?? null,
                'id_laboratorio_luna' => $data['id_laboratorio_luna'] ?? null,
                'indice_luna' => $data['indice'] ?? null,
            ]
        );
        $this->imported++;
    }

    public function customValidationMessages()
    {
        return [
            'codigo.required' => 'El código es obligatorio.',
            'tipo_producto.required' => 'El tipo de producto es obligatorio.',
            'precio.required' => 'El precio es obligatorio.',
        ];
    }

    private function mapTipoProducto($value)
    {
        if (!$value) return null;
        $map = [
            'l' => 'l', 'lentes de sol' => 'l', 'lente de sol' => 'l',
            'm' => 'm', 'montura' => 'm',
            'c' => 'c', 'lentes de contacto' => 'c', 'lente de contacto' => 'c',
            'u' => 'u', 'lunas' => 'u', 'luna' => 'u',
        ];
        $v = strtolower(trim($value));
        return $map[$v] ?? $value;
    }

    private function mapGenero($value)
    {
        if (!$value) return null;
        $map = [
            'h' => 'H', 'hombre' => 'H',
            'm' => 'M', 'mujer' => 'M',
            'n' => 'N', 'niño' => 'N', 'niña' => 'N',
            'u' => 'U', 'unisex' => 'U',
        ];
        $v = strtolower(trim($value));
        return $map[$v] ?? $value;
    }
}
