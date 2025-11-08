<?php

namespace App\Http\Controllers;

use App\Models\PlanCuenta; // modelo que apunta a tu tabla plan_cuentas

class PlanCuentaController extends Controller
{
    public function index()
    {
        // Devuelve todas las cuentas contables ordenadas por código
        return PlanCuenta::orderBy('codigo')->get();
    }
}
