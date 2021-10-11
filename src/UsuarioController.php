<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsuarioController extends Controller
{
    // Metodo para retornar todos los registros de la tabla de la DB: 
    public function index()
    {
        // Realizamos la consulta a la tabla de la DB:
        $model = DB::table('usuarios')

                // Realizamos la consulta a la tabla del modelo 'Role': 
                ->join('roles', 'roles.id_role', '=', 'usuarios.role_id')

                // Seleccionamos los campos que se requiren: 
                ->select('usuarios.identification', 'usuarios.name', 'usuarios.last_name', 'usuarios.email', 'usuarios.telephone', 'roles.role_name as role');

        // Validamos que existan registros en la tabla de la DB:
        
    }
}
