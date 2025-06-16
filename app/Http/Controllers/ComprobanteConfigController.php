<?php

namespace App\Http\Controllers;

use App\Models\ComprobanteConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ComprobanteConfigController extends Controller
{
    /**
     * Display the current configuration.
     */
    public function index()
    {
        $config = ComprobanteConfig::getConfig();
        return response()->json($config);
    }

    /**
     * Update the configuration.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'nullable|string|max:255',
            'sub_title' => 'nullable|string|max:255', // Added sub_title validation
            'company_address' => 'nullable|string|max:255',
            'address_2' => 'nullable|string|max:255', // Added address_2 validation
            'company_ruc' => 'nullable|string|max:20',
            'company_phone' => 'nullable|string|max:20',
            'company_email' => 'nullable|email|max:255',
            'company_website' => 'nullable|url|max:255',
            'font_family' => 'nullable|string|max:50',
            'font_size' => 'nullable|integer|min:1',
            'header_alignment' => 'nullable|string|in:left,center,right',
            'show_logo' => 'required|boolean',
            'details_alignment' => 'nullable|string|in:left,center,right',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $config = ComprobanteConfig::getConfig();
            $data = $request->except('logo');

            // Handle logo upload
            if ($request->hasFile('logo')) {
                // Delete old logo if exists
                if ($config->logo_path && Storage::disk('public')->exists($config->logo_path)) {
                    Storage::disk('public')->delete($config->logo_path);
                }
                
                $file = $request->file('logo');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('comprobante-logos', $filename, 'public');
                $data['logo_path'] = $path;
            }

            $config->update($data);

            return response()->json([
                'message' => 'Configuración guardada exitosamente.',
                'config' => $config
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al guardar la configuración: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the logo.
     */
    public function removeLogo()
    {
        try {
            $config = ComprobanteConfig::getConfig();
            
            if ($config->logo_path && Storage::disk('public')->exists($config->logo_path)) {
                Storage::disk('public')->delete($config->logo_path);
            }
            
            $config->logo_path = null;
            $config->show_logo = false;
            $config->save();

            return response()->json([
                'message' => 'Logo eliminado correctamente',
                'config' => $config
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al eliminar el logo: ' . $e->getMessage()
            ], 500);
        }
    }
}
