<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComprobanteConfig extends Model
{
    protected $table = 'comprobante_config';
    
    protected $fillable = [
        'company_name',
        'sub_title', // Added sub_title
        'address',
        'address_2', // Added address_2
        'ruc',
        'phone',
        'email',
        'website',
        'font_family',
        'font_size',
        'header_alignment',
        'show_logo',
        'logo_path',
        'details_alignment'
    ];

    protected $casts = [
        'show_logo' => 'boolean',
        'font_size' => 'integer'
    ];

    /**
     * Get the configuration or create default one if doesn't exist
     */
    public static function getConfig()
    {
        $config = self::first();
        
        if (!$config) {
            $config = self::create([
                'company_name' => 'G & F oftalmólogas. S.A.C.',
                'sub_title' => null, // Added sub_title
                'address' => 'Calle almenara 124 interior 201 surquillo',
                'address_2' => null, // Added address_2
                'ruc' => '20613814265',
                'phone' => '940 213 168',
                'font_family' => 'Courier New',
                'font_size' => 8,
                'header_alignment' => 'center',
                'show_logo' => false,
                'details_alignment' => 'center'
            ]);
        }
        
        return $config;
    }
}
