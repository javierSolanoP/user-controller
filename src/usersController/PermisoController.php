<?php

namespace App\Http\Controllers\Modulo_usuarios\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Modulo_usuarios\Class\Permiso as ClassPermiso;
use App\Models\Permiso;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermisoController extends Controller
{
    // Metodo para retornar todos los registros de la tabla de la DB: 
    public function index()
    {
        // Realizamos la consulta a la tabla de la DB: 
        $model = DB::table('permisos')

                // Realizamos la consulata a la tabla del modelo 'Role': 
                ->join('roles', 'roles.id_role', '=', 'permisos.role_id')

                // Seleccionamos los campos que se requieren: 
                ->select('roles.nombre_role', 'permisos.nombre_permiso')

                // Traemos todos los registros que se encuentren en la tabla del modelo 'Permiso': 
                ->get();

        // Retornamos la respuesta: 
        return ['query' => true, 'permisos' => $model];

    }

    // Metodo para registrar un nuevo permiso en la tabla de la DB: 
    public function store(Request $request)
    {
        // Si los argumentos contienen caracteres de tipo mayusculas, los pasamos a tipo minusculas. Para seguir una nomenclatura estandar: 
        $nombre_role    = strtolower($request->input(key: 'nombre_role'));
        $nombre_permiso = strtolower($request->input(key: 'nombre_permiso'));

        if(!empty($nombre_role) 
        && !empty($nombre_permiso)){

            // Instanciamos el controlador del modelo 'Role', para validar que exista el role: 
            $role = new RoleController; 

            // Validamos que exista el role: 
            $validarRole = $role->show(role: $nombre_role);

            // Si existe, realizamos la consulta a la tabla de la DB: 
            if($validarRole['query']){

                // Realizamos la consulta a la tabla de la DB: 
                $model = Permiso::where('nombre_permiso', $nombre_permiso);

                // Validamos que no exista ese permiso en la tabla de la DB: 
                $validarPermiso = $model->first();

                // Sino existe, validamos el argumento para proceder a realizar el registro: 
                if(!$validarPermiso){

                    // Instanciamos la clase 'Permiso', para validar el argumento: 
                    $permiso = new ClassPermiso(nombre_permiso: $nombre_permiso);

                    // Asignamos a la sesion 'registrar' la instancia, con sus propiedades cargadas de data: 
                    $_SESSION['registrar'] = $permiso; 

                    // Validamos el argumento: 
                    $validarPermisoArgm = $permiso->registerData();

                    // Si el argumento ha sido validado, realizamos el registro: 
                    if($validarPermisoArgm['registrar']){

                        try{

                            // Realizamos el registro: 
                            Permiso::create(['nombre_permiso' => $nombre_permiso,
                                             'role_id' => $validarRole['role']['id_role']]);

                            // Retornamos el error: 
                            return $validarPermisoArgm;

                        }catch(Exception $e){
                            // Retornamos el error: 
                            return ['register' => false, 'error' => $e->getMessage()]; 
                        }

                    }else{
                        // Retornamos el error: 
                        $validarPermisoArgm;
                    }

                }else{
                    // Retornamos el error: 
                    return ['registrar' => false, 'error' => 'Ya existe ese permiso en el sistema.'];
                }

            }else{
                // Retornamos el error: 
                return ['registrar' => false, 'error' => $validarRole['error']];
            }

        }else{
            // Retornamos el error: 
            return ['registrar' => false, 'error' => "Campo 'nombre_permiso' o 'nombre_role': No deben estar vacios."];
        }

    }

    // Metodo para retornar un registro en especifico de la tabla de la DB: 
    public function show($permiso)
    {
        // Si el argumento contiene caracteres de tipo mayusculas, los pasamos a tipo minusculas. Para seguir una nomenclatura estandar: 
        $nombre_permiso = strtolower($permiso);

        // Realizamos la consulta a la tabla de la DB: 
        $model = DB::table('permisos')

                // Filtramos el registro solicitado: 
                ->where('nombre_permiso', $nombre_permiso)

                // Realizamos la consulta a la tabla del modelo 'Role': 
                ->join('roles', 'roles.id_role', '=', 'permisos.role_id')

                // Seleccionamos los campos que requerimos: 
                ->select('roles.nombre_role as role', 'permisos.nombre_permiso as permiso');

        // Validamos que exista el permiso en la tabla de la DB: 
        $validarPermiso = $model->first();

        // Si existe, retornamos el registro: 
        if($validarPermiso){

            // Retornamos la respuesta: 
            return ['query' => true, 'permiso' => $validarPermiso];

        }else{
            // Retornamos el error: 
            return ['query' => false, 'error' => 'No existe ese permiso en el sistema.'];
        }

    }
    
    // Metodo para actualizar un reistro en especifico de la tabla de la DB: 
    public function update(Request $request, $nombre_permiso)
    {
        // Si el argumento contiene caracteres de tipo mayusculas, los pasamos a tipo minusculas. Para seguir una nomenclatura estandar: 
        $new_nombre_role    = strtolower($request->input(key: 'new_nombre_role'));
        $new_nombre_permiso = strtolower($request->input(key: 'new_nombre_permiso'));

        if(!empty($nombre_permiso) 
        && !empty($new_nombre_permiso)
        && !empty($new_nombre_role)){

            // Instanciamos el controlador del modelo 'Role', para validar que exista el role: 
            $role = new RoleController; 

            // Validamos que exista el role: 
            $validarRole = $role->show(role: $new_nombre_role);

            // Si existe, realizamos la consulta a la tabla de la DB: 
            if($validarRole['query']){

                // Realizamos la consulta a la tabla de la DB: 
                $model = Permiso::where('nombre_permiso', $nombre_permiso);

                // Validamos que exista ese permiso en la tabla de la DB: 
                $validarPermiso = $model->first();

                // Si existe, validamos el argumento para proceder a realizar el registro: 
                if($validarPermiso){

                    // Instanciamos la clase 'Permiso', para validar el argumento: 
                    $permiso = new ClassPermiso(nombre_permiso: $new_nombre_permiso);

                    // Asignamos a la sesion 'registrar' la instancia, con sus propiedades cargadas de data: 
                    $_SESSION['registrar'] = $permiso; 

                    // Validamos el argumento: 
                    $validarPermisoArgm = $permiso->updateData();

                    // Si el argumento ha sido validado, realizamos el registro: 
                    if($validarPermisoArgm['registrar']){

                        try{

                            // Realizamos el registro: 
                            $model->update(['nombre_permiso' => $new_nombre_permiso,
                                            'role_id' => $validarRole['role']['id_role']]);

                            // Retornamos el error: 
                            return $validarPermisoArgm;

                        }catch(Exception $e){
                            // Retornamos el error: 
                            return ['register' => false, 'error' => $e->getMessage()]; 
                        }

                    }else{
                        // Retornamos el error: 
                        $validarPermisoArgm;
                    }

                }else{
                    // Retornamos el error: 
                    return ['registrar' => false, 'error' => 'No existe ese permiso en el sistema.'];
                }

            }else{
                // Retornamos el error: 
                return ['registrar' => false, 'error' => $validarRole['error']];
            }

        }else{
            // Retornamos el error: 
            return ['registrar' => false, 'error' => "Campo 'nombre_permiso' o 'new_nombre_role' o 'new_nombre_permiso': No deben estar vacios."];
        }

    }

    // Metodo para eliminar un regitro especifico de la tabla de la DB: 
    public function destroy($permiso)
    {
        // Si el argumento contiene caracteres de tipo mayusculas, los pasamos a tipo minusculas. Para seguir una nomenclatura estandar: 
        $nombre_permiso    = strtolower($permiso);

        // Realizamos la consulta a la tabla de la DB: 
        $model = Permiso::where('nombre_permiso', $nombre_permiso);

        // Validamos que exista el permiso en la tabla de la DB: 
        $validarPermiso = $model->first();

        // Si existe, eliminamos el registro: 
        if($validarPermiso){

            try{

                // Eliminamos el registro: 
                $model->delete();

                // Retornamos la respuesta: 
                return ['delete' => true, 'permiso' => $validarPermiso];


            }catch(Exception $e){
                // Retornamos el error: 
                return ['delete' => false, 'error' => $e->getMessage()];
            }
            
        }else{
            // Retornamos el error: 
            return ['delete' => false, 'error' => 'No existe ese permiso en el sistema.'];
        }

    }
}
