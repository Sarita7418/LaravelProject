<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'user_id',
        'numero_factura',
        'cuf',
        'fecha_emision',
        'monto_total',
        'estado', // 'VALIDA', 'ANULADA'
    ];

    // Relación: Una factura pertenece a un Cliente
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    // Relación: Una factura la hizo un Usuario (Vendedor)
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relación: Una factura tiene muchos detalles (productos)
    public function detalles()
    {
        return $this->hasMany(DetalleFactura::class);
    }
}