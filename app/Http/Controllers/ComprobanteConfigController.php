<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ComprobanteConfig;
use Illuminate\Support\Facades\Storage;

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
        $request->validate([
            'company_name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'ruc' => 'required|string|max:20',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|string|email|max:255',
            'website' => 'nullable|string|max:255',
            'font_family' => 'required|string|max:100',
            'font_size' => 'required|integer|min:6|max:20',
            'header_alignment' => 'required|in:left,center,right',
            'show_logo' => 'boolean',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            $config = ComprobanteConfig::getConfig();
            
            // Handle logo upload
            if ($request->hasFile('logo')) {
                // Delete old logo if exists
                if ($config->logo_path && Storage::disk('public')->exists($config->logo_path)) {
                    Storage::disk('public')->delete($config->logo_path);
                }
                
                $file = $request->file('logo');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('comprobante-logos', $filename, 'public');
                $config->logo_path = $path;
                $config->show_logo = true;
            }

            // Update other fields
            $config->company_name = $request->company_name;
            $config->address = $request->address;
            $config->ruc = $request->ruc;
            $config->phone = $request->phone;
            $config->email = $request->email;
            $config->website = $request->website;
            $config->font_family = $request->font_family;
            $config->font_size = $request->font_size;
            $config->header_alignment = $request->header_alignment;
            
            if ($request->has('show_logo')) {
                $config->show_logo = $request->show_logo;
            }

            $config->save();

            return response()->json([
                'message' => 'Configuración actualizada correctamente',
                'config' => $config
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al actualizar la configuración: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove logo from configuration.
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
