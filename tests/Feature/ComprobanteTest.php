<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ComprobanteTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_crear_comprobante()
    {
        // Usa un usuario existente o crea uno con factory
        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user) // esto usa la sesión
            ->postJson('/api/comprobantes', [
                'tipo' => 'diario',
                'fecha' => now()->toDateString(),
                'glosa_general' => 'Prueba',
                'detalles' => [
                    ['cuenta_id' => 1, 'debe' => 100, 'haber' => 100],
                ],
            ]);

        $response->assertStatus(201);
    }

}
