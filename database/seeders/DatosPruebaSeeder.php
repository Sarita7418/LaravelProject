<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Producto;
use App\Models\StockActual;
use App\Models\Cliente;
use App\Models\User;

class DatosPruebaSeeder extends Seeder
{
    public function run()
    {
        // --------------------------------------------------------
        // 1. INFRAESTRUCTURA (Verificando si existen)
        // --------------------------------------------------------

        // EMPRESA: Buscamos si ya existe por NIT
        $empresa = DB::table('empresas')->where('nit', '1020304050')->first();
        if ($empresa) {
            $idEmpresa = $empresa->id;
        } else {
            $idEmpresa = DB::table('empresas')->insertGetId([
                'razon_social' => 'Mi Empresa S.R.L.',
                'nombre_comercial' => 'Mi Tienda',
                'nit' => '1020304050',
                'direccion_fiscal' => 'Av. Murillo No 500, La Paz',
                'created_at' => now()
            ]);
        }

        // SUCURSAL: Buscamos por nombre y empresa
        $sucursal = DB::table('sucursales')->where('id_empresa', $idEmpresa)->where('nombre', 'Casa Matriz')->first();
        if ($sucursal) {
            $idSucursal = $sucursal->id;
        } else {
            $idSucursal = DB::table('sucursales')->insertGetId([
                'id_empresa' => $idEmpresa,
                'nombre' => 'Casa Matriz',
                'direccion' => 'Calle Sagárnaga 123',
                'codigo_sucursal' => '0',
                'created_at' => now()
            ]);
        }
        
        // UBICACIÓN
        $ubicacion = DB::table('politicos_ubicacion')->where('descripcion', 'Almacén Central')->first();
        if ($ubicacion) {
            $idUbicacion = $ubicacion->id;
        } else {
            $idUbicacion = DB::table('politicos_ubicacion')->insertGetId([
                'descripcion' => 'Almacén Central',
                'tipo' => 'SUCURSAL', 
                'created_at' => now()
            ]);
        }

        // PROVEEDOR
        $proveedor = DB::table('personas')->where('ci', '99887766')->first();
        if ($proveedor) {
            $idProveedor = $proveedor->id;
        } else {
            $idProveedor = DB::table('personas')->insertGetId([
                'nombres' => 'Juan',
                'apellido_paterno' => 'Proveedor',
                'apellido_materno' => 'Global',
                'ci' => '99887766',
                'created_at' => now()
            ]);
        }

        // SUBDOMINIO LOTE ACTIVO (ID 27)
       DB::table('subdominios')->updateOrInsert(
    ['id' => 27],
    ['id_dominio' => 1, 'descripcion' => 'Lote Activo', 'created_at' => now()]
);

        $user = User::first();
        if (!$user) {
            $user = User::create([
                'name' => 'Admin Vendedor',
                'email' => 'admin@admin.com',
                'password' => bcrypt('password'),
            ]);
        }

        // --------------------------------------------------------
        // 2. COMPRA (Verificamos si ya existe para no duplicar)
        // --------------------------------------------------------
        
        $compra = DB::table('compras')->where('id_sucursal', $idSucursal)->where('id_proveedor', $idProveedor)->first();
        if ($compra) {
            $idCompra = $compra->id;
        } else {
            $idCompra = DB::table('compras')->insertGetId([
                'id_empresa' => $idEmpresa,
                'id_sucursal' => $idSucursal,
                'id_proveedor' => $idProveedor,
                'id_usuario' => $user->id,
                'fecha_compra' => now(),
                'total_compra' => 10000,
                'id_estado_compra' => 22, 
                'created_at' => now()
            ]);
        }

        // --------------------------------------------------------
        // 3. PRODUCTOS Y LOTES
        // --------------------------------------------------------

        // Si ya hay productos, asumimos que ya corrió el seeder y paramos para no duplicar stock
        if (Producto::where('codigo_interno', 'PROD-001')->exists()) {
            $this->command->info('⚠️ Los productos ya existen. Saltando creación para evitar duplicados.');
            return;
        }

        $prod1 = Producto::create([
            'nombre' => 'Laptop Gamer HP Victus',
            'codigo_interno' => 'PROD-001',
            'id_categoria' => 13,
            'rastrea_inventario' => true,
            'id_unidad_medida' => 15,
            'precio_entrada' => 5000.00,
            'precio_salida' => 6500.00,
            'stock_minimo' => 2,
            'id_estado_producto' => 22
        ]);

        $prod2 = Producto::create([
            'nombre' => 'Mouse Logitech G203',
            'codigo_interno' => 'PROD-002',
            'id_categoria' => 13,
            'rastrea_inventario' => true,
            'id_unidad_medida' => 15,
            'precio_entrada' => 150.00,
            'precio_salida' => 250.00,
            'stock_minimo' => 10,
            'id_estado_producto' => 22
        ]);

        // LOTE 1
        $idLote1 = DB::table('lotes')->insertGetId([
            'id_compra' => $idCompra,
            'id_producto' => $prod1->id,
            'numero_lote' => 'LOTE-LAP-001',
            'cantidad_inicial' => 5,
            'fecha_ingreso' => now()->subDays(10),
            'fecha_vencimiento' => now()->addYear(),
            'id_estado_lote' => 27, // ACTIVO
            'created_at' => now()
        ]);
        
        // LOTE 2
        $idLote2 = DB::table('lotes')->insertGetId([
            'id_compra' => $idCompra,
            'id_producto' => $prod2->id,
            'numero_lote' => 'LOTE-MOUSE-001',
            'cantidad_inicial' => 20,
            'fecha_ingreso' => now()->subDays(10),
            'fecha_vencimiento' => now()->addYear(),
            'id_estado_lote' => 27, // ACTIVO
            'created_at' => now()
        ]);

        StockActual::create([
            'id_producto' => $prod1->id,
            'id_lote' => $idLote1,
            'id_ubicacion' => $idUbicacion,
            'cantidad' => 5
        ]);

        StockActual::create([
            'id_producto' => $prod2->id,
            'id_lote' => $idLote2,
            'id_ubicacion' => $idUbicacion,
            'cantidad' => 20
        ]);

        $this->command->info('✅ Stock Inicial cargado correctamente.');

        // CLIENTE
        if (Cliente::count() == 0) {
            Cliente::create([
                'razon_social' => 'Juan Perez - Consultor',
                'nit_ci' => '6844552',
                'email' => 'juan@gmail.com'
            ]);
        }
    }
}