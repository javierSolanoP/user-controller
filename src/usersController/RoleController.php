<?php

namespace App\Http\Controllers\Modulo_usuarios\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Modulo_usuarios\Class\Role as ClassRole;
use App\Models\Role;
use Exception;
use Illuminate\Http\Request;

class RoleController extends Controller
{
   
    // Metodo para retornar todos los registros de la tabla de la DB: 
    public function index()
    {
        // Realizamos la consulta a la tabla de la DB: 
        $model = Role::select('nombre_role as role')->get();

        // Retornamos la respuesta: 
        return ['query' => true, 'roles' => $model];
    }

    // Metodo para registrar un nuevo role en la tabla de la DB: 
    public function store(Request $request)
    {
        // Si el argumento contiene caracteres de tipo mayusculas, los pasamos a tipo minusculas. Para seguir una nomenclatura estandar: 
        $nombre_role = strtolower($request->input(key: 'nombre_role'));

        if(!empty($nombre_role)){

             // Realizamos la consulta a la tabla de la DB: 
            $model = Role::where('nombre_role', $nombre_role);

            // Vaidamos que no exista el role en el sistema: 
            $validarRole = $model->first();

            // Sino existe, validamos el argumento para proceder al registro del mismo: 
            if(!$validarRole){

                // Instanciamos la clase 'Role', para validar el argumento: 
                $role = new ClassRole(nombre_role: $nombre_role);

                // Asignamos a la sesion 'registrar' la instancia 'role', con su propiedad cargada de data: 
                $_SESSION['registrar'] = $role;

                // Validamos el argumento: 
                $validarRoleArgm = $role->registerData();

                // Si el argumento ha sido validado, realizamos el registro: 
                if($validarRoleArgm['registrar']){

                    try{

                        // Realizamos el registro:
                        Role::create(['nombre_role' => $nombre_role]);

                        // Retornamos la respuesta: 
                        return $validarRoleArgm;
                        
                    }catch(Exception $e){
                        // Retornamos el error: 
                        return ['registrar' => false, 'error' => $e->getMessage()];
                    }


                }else{
                    //  Retornamos el error: 
                    return $validarRoleArgm;
                }

            }else{
                // Retornamos el error: 
                return ['registrar' => false, 'error' => 'Ya existe ese role en el sistema.'];
            }

        }else{
            // Retornamos el error: 
            return ['registrar' => false, 'error' => "Campo 'nombre_role': No deben estar vacios."];
        }
        
    }

    // Metodo para retornar un registro en especifico de la tabla de la DB: 
    public function show($role)
    {
        // Si el argumento contiene caracteres de tipo mayusculas, los pasamos a tipo minusculas. Para seguir una nomenclatura estandar: 
        $nombre_role = strtolower($role);

        // Realizamos la consulta a la tabla de la DB:
        $model = Role::select('id_role', 'nombre_role as role')->where('nombre_role', $nombre_role);

        // Validamos que exista el registro en la tabla de la DB: 
        $validarRole = $model->first();

        // Si existe, lo retornamos: 
        if($validarRole){

            // Retornamos la respuesta: 
            return ['query' => true, 'role' => $validarRole];

        }else{
            // Retornamos el error: 
            return ['query' => false, 'error' => 'No existe ese role en el sistema.'];
        }
    }

    // Metodo para actualizar un registro de la tabla de la DB: 
    public function update(Request $request, $role)
    {
        // Si los argumentos contienen caracteres de tipo mayusculas, los pasamos a tipo minusculas. Para seguir una nomenclatura estandar: 
        $nombre_role     = strtolower($role);
        $new_nombre_role = strtolower($request->input(key: 'new_nombre_role'));

        if(!empty($nombre_role) 
        && !empty($new_nombre_role)){

            // Realizamos la consulta a la tabla de la DB: 
            $model = Role::where('nombre_role', $nombre_role);

            // Vaidamos que no exista el role en el sistema: 
            $validarRole = $model->first();

            // Si existe, validamos el argumento para proceder al registro del mismo: 
            if($validarRole){

                // Instanciamos la clase 'Role', para validar el argumento: 
                $role = new ClassRole(nombre_role: $new_nombre_role);

                // Asignamos a la sesion 'registrar' la instancia 'role', con su propiedad cargada de data: 
                $_SESSION['registrar'] = $role;

                // Validamos el argumento: 
                $validarRoleArgm = $role->updateData();

                // Si el argumento ha sido validado, realizamos el registro: 
                if($validarRoleArgm['registrar']){

                    try{

                        // Realizamos el registro:
                        $model->update(['nombre_role' => $new_nombre_role]);

                        // Retornamos la respuesta: 
                        return $validarRoleArgm;
                        
                    }catch(Exception $e){
                        // Retornamos el error: 
                        return ['registrar' => false, 'error' => $e->getMessage()];
                    }


                }else{
                    //  Retornamos el error: 
                    return $validarRoleArgm;
                }

            }else{
                // Retornamos el error: 
                return ['registrar' => false, 'error' => 'No existe ese role en el sistema.'];
            }

        }else{
            // Retornamos el error: 
            return ['registrar' => false, 'error' => "Campo 'nombre_role' o 'new_nombre_role': No deben estar vacios."];
        }

    }

    // Metodo para eliminar un registro de la tabla de la DB: 
    public function destroy($role)
    {
        // Si el argumento contiene caracteres de tipo mayusculas, los pasamos a tipo minusculas. Para seguir una nomenclatura estandar: 
        $nombre_role = strtolower($role);

        // Realizamos la consulta a la tabla de la DB:
        $model = Role::where('nombre_role', $nombre_role);

        // Validamos que exista el registro en la tabla de la DB: 
        $validarRole = $model->first();

        // Si existe, lo eliminamos: 
        if($validarRole){

            try{

                // Eliminamos el registro de la tabla de la DB: 
                $model->delete();

                // Retornamos la respuesta: 
                return ['delete' => true];

            }catch(Exception $e){
                // Retornamos el error: 
                return ['delete' => true, 'error' => $e->getMessage()];
            }
            
        }else{
            // Retornamos el error: 
            return ['delete' => false, 'error' => 'No existe ese role en el sistema.'];
        }
    }
}
