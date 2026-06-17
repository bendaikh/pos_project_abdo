<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Printer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'brand',
        'model',
        'connection_type',
        'ip_address',
        'subnet_mask',
        'gateway',
        'port',
        'usage',
        'description',
        'is_active',
        'ticket_config',
        'kitchen_config',
        'advanced_config',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'port' => 'integer',
        'ticket_config' => 'array',
        'kitchen_config' => 'array',
        'advanced_config' => 'array',
    ];

    public static function defaultTicketConfig(): array
    {
        return [
            'content' => [
                ['key' => 'logo', 'label' => 'Logo', 'enabled' => true],
                ['key' => 'company_name', 'label' => 'Nom de la société', 'enabled' => true],
                ['key' => 'address', 'label' => 'Adresse', 'enabled' => true],
                ['key' => 'phone', 'label' => 'Téléphone', 'enabled' => true],
                ['key' => 'ice', 'label' => 'ICE', 'enabled' => true],
                ['key' => 'qr_code', 'label' => 'QR Code', 'enabled' => true],
                ['key' => 'datetime', 'label' => 'Date et heure', 'enabled' => true],
                ['key' => 'ticket_number', 'label' => 'N° ticket', 'enabled' => true],
                ['key' => 'customer_info', 'label' => 'Informations client', 'enabled' => true, 'fields' => [
                    'name' => true, 'phone' => true, 'address' => true, 'ice' => true, 'if' => true,
                ]],
                ['key' => 'subtotal', 'label' => 'Sous-total', 'enabled' => true],
                ['key' => 'tax', 'label' => 'TVA', 'enabled' => true],
                ['key' => 'discount', 'label' => 'Remise', 'enabled' => true],
                ['key' => 'total', 'label' => 'Total', 'enabled' => true],
                ['key' => 'footer', 'label' => 'Pied de page', 'enabled' => true],
            ],
            'paper_width' => '80',
            'copies' => 1,
            'alignment' => 'center',
            'auto_cut' => true,
            'open_drawer' => true,
            'auto_print_on_payment' => false,
        ];
    }

    public static function defaultKitchenConfig(): array
    {
        return [
            'content' => [
                ['key' => 'restaurant_name', 'label' => 'Nom du restaurant', 'enabled' => true],
                ['key' => 'order_type', 'label' => 'Type de commande', 'enabled' => true],
                ['key' => 'order_number', 'label' => 'Numéro de commande', 'enabled' => true],
                ['key' => 'datetime', 'label' => 'Date et heure', 'enabled' => true],
                ['key' => 'user', 'label' => 'Utilisateur', 'enabled' => true],
                ['key' => 'table', 'label' => 'Emplacement ticket (table)', 'enabled' => true],
                ['key' => 'items', 'label' => 'Articles', 'enabled' => true],
                ['key' => 'quantities', 'label' => 'Quantités', 'enabled' => true],
                ['key' => 'kitchen_notes', 'label' => 'Notes cuisine', 'enabled' => true],
                ['key' => 'separator', 'label' => 'Séparateur', 'enabled' => true],
            ],
            'paper_width' => '80',
            'copies' => 1,
            'alignment' => 'center',
            'line_spacing' => 'normal',
            'auto_print_on_validate' => true,
            'group_by_category' => false,
            'category_ids' => [],
        ];
    }

    public static function defaultAdvancedConfig(): array
    {
        return [
            'encoding' => 'UTF-8',
            'code_page' => 'cp850',
            'print_density' => 'normal',
            'beep_on_print' => false,
            'retry_on_failure' => true,
            'max_retries' => 3,
            'timeout_seconds' => 10,
        ];
    }
}
