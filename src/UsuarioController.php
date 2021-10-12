<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Usuario as ClassUsuario;
use App\Models\Usuario;
use Exception;
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
        $validateUser = $model->get();

        // Si existen, retornamos los registros: 
        if(count($validateUser) != 0){

            // Retornamos la respuesta:
            return ['query' => true, 'users' => $validateUser];

        }else{
            // Retornamos el error:
            return ['query' => false, 'error' => 'No existen usuarios en el sistema.'];
        }
    }

    // Metodo para registrar un usuario en la tabla de la DB: 
    public function store(Request $request)
    {
        // Si los argumentos contienen caracteres de tipo mayusculas, los pasamos a minusculas. Para seguir una nomenclatura estandar:
        $name      = strtolower($request->input(key: 'name'));
        $last_name = strtolower($request->input(key: 'last_name'));
        $role_name = strtolower($request->input(key: 'role')); 

        // Validamos que los argumentos no esten vacios:
        if(!empty($request->input(key: 'identification'))
        && !empty($name)
        && !empty($last_name)
        && !empty($request->input(key: 'email'))
        && !empty($request->input(key: 'password'))
        && !empty($request->input(key: 'confirmPassword'))
        && !empty($request->input(key: 'telephone'))
        && !empty($role_name)){

            // Instanciamos el contolador del modelo 'Role'. Para validar que exista el role: 
            $roleController = new RoleController;

            // Validamos que exista el role: 
            $validateRole = $roleController->show(role: $role_name);

            // Si existe, extraemos su 'id' y validamos que no exista el usuario: 
            if($validateRole['query']){

                // Extraemos su id: 
                $role_id = $validateRole['role']['id'];

                // Realizamos la consulta a la tabla de la DB:
                $model = Usuario::where('identification', $request->input(key: 'identification'));

                // Validamos que exista el registro en la tabla de la DB:
                $validateUser = $model->first();

                // Sino existe, validamos los argumentos: 
                if(!$validateUser){

                    // Instanciamos la clase 'Usuario'. Para validar los argumentos: 
                    $user = new ClassUsuario(
                                             identification: $request->input(key: 'identification'), 
                                             name: $name,
                                             last_name: $last_name,
                                             email: $request->input(key: 'email'),
                                             password: $request->input(key: 'password'),
                                             confirmPassword: $request->input(key: 'confirmPassword'),
                                             telephone: $request->input(key: 'telephone')
                                            );

                    // Asignamos a la sesion 'validate' la instancia 'user. Con sus propiedades cargadas de informacion: 
                    $_SESSION['validate'] = $user; 

                    // Validamos los argumentos: 
                    $validateUserArgm = $user->validateData();
 
                    // Si los argumentos han sido validados, realizamos el registro: 
                    if($validateUserArgm['register']){

                        try{

                            Usuario::create(['identification' => $request->input(key: 'identification'),
                                             'name' => $name,
                                             'last_name' => $last_name,
                                             'email' => $request->input(key: 'email'),
                                             'password' => bcrypt($request->input(key: 'password')),
                                             'telephone' => $request->input(key: 'telephone'),
                                             'session' => 'Inactiva',
                                             'role_id' => $role_id]);

                            // Retornamos la respuesta:
                            return $validateUserArgm;

                        }catch(Exception $e){
                            // Retornamos el error:
                            return ['register' => false, 'error' => $e->getMessage()];
                        }

                    }else{
                        // Retornamos el error:
                        return $validateUserArgm;
                    }

                }else{
                    // Retornamos el error:
                    return ['register' => false, 'error' => 'Ya existe ese usuario en el sistema.'];
                }

            }else{
                // Retornamos el error:
                return ['register' => false, 'error' => $validateRole['error']];
            }

        }else{
            // Retornamos el error:
            return ['register' => false, 'error' => "Campo 'identification' o 'name' o 'last_name' o 'email' o 'password' o 'confirmPassword' o 'telephone' o 'role': NO deben estar vacios."];
        }
    }

    // Metodo para retornar un usuario especifico: 
    public function show($identification)
    {
        // Realizamos la consulta a la tabla de la DB:
        $model = DB::table('usuarios')

                // Filtramos el usuario requerido: 
                ->where('identification', $identification)

                // Realizamos la consulta a la tabla del modelo 'Role: 
                ->join('roles', 'roles.id_role', '=', 'usuarios.role_id')

                // Seleccionamos los campos que se requiren: 
                ->select('usuarios.identification', 'usuarios.name', 'usuarios.last_name', 'usuarios.email', 'usuarios.telephone', 'roles.role_name as role');

        // Validamos que exista el registro en la tabla de la DB:
        $validateUser = $model->first();

        // Si existe, retornamos el registro: 
        if($validateUser){

            // Retornamos la respuesta:
            return ['query' => true, 'user' => $validateUser];

        }else{
            // Retornamos el error:
            return ['query' => false, 'error' => 'No existe ese usuario en el sistema.'];
        }
    }

    // Metodo para actualizar un usuario en la tala de la DB: 
    public function update(Request $request, $identification)
    {
        // Si los argumentos contienen caracteres de tipo mayusculas, los pasamos a minusculas. Para seguir una nomenclatura estandar:
        $name      = strtolower($request->input(key: 'name'));
        $last_name = strtolower($request->input(key: 'last_name'));
        $role_name = strtolower($request->input(key: 'role')); 
 
        // Validamos que los argumentos no esten vacios:
        if(!empty($name)
        && !empty($last_name)
        && !empty($request->input(key: 'email'))
        && !empty($request->input(key: 'password'))
        && !empty($request->input(key: 'confirmPassword'))
        && !empty($request->input(key: 'telephone'))
        && !empty($role_name)){
 
            // Instanciamos el contolador del modelo 'Role'. Para validar que exista el role: 
            $roleController = new RoleController;
 
            // Validamos que exista el role: 
            $validateRole = $roleController->show(role: $role_name);
 
            // Si existe, extraemos su 'id' y validamos que no exista el usuario: 
            if($validateRole['query']){
 
                // Extraemos su id: 
                $role_id = $validateRole['role']['id'];
 
                // Realizamos la consulta a la tabla de la DB:
                $model = Usuario::where('identification', $identification);
 
                // Validamos que exista el registro en la tabla de la DB:
                $validateUser = $model->first();
 
                // Si existe, validamos los argumentos: 
                if($validateUser){
 
                    // Instanciamos la clase 'Usuario'. Para validar los argumentos: 
                    $user = new ClassUsuario(
                                              name: $name,
                                              last_name: $last_name,
                                              email: $request->input(key: 'email'),
                                              password: $request->input(key: 'password'),
                                              confirmPassword: $request->input(key: 'confirmPassword'),
                                              telephone: $request->input(key: 'telephone')
                                            );
 
                    // Asignamos a la sesion 'validate' la instancia 'user. Con sus propiedades cargadas de informacion: 
                    $_SESSION['validate'] = $user; 
 
                    // Validamos los argumentos: 
                    $validateUserArgm = $user->validateData();
  
                    // Si los argumentos han sido validados, realizamos el registro: 
                    if($validateUserArgm['register']){
 
                        try{
 
                            $model->update(['name' => $name,
                                            'last_name' => $last_name,
                                            'email' => $request->input(key: 'email'),
                                            'password' => bcrypt($request->input(key: 'password')),
                                            'telephone' => $request->input(key: 'telephone'),
                                            'role_id' => $role_id]);
 
                            // Retornamos la respuesta:
                            return $validateUserArgm;
 
                        }catch(Exception $e){
                            // Retornamos el error:
                            return ['register' => false, 'error' => $e->getMessage()];
                        }
 
                    }else{
                        // Retornamos el error:
                        return $validateUserArgm;
                    }
 
                }else{
                    // Retornamos el error:
                    return ['register' => false, 'error' => 'No existe ese usuario en el sistema.'];
                }
 
            }else{
                // Retornamos el error:
                return ['register' => false, 'error' => $validateRole['error']];
            }
 
        }else{
            // Retornamos el error:
            return ['register' => false, 'error' => "Campo 'name' o 'last_name' o 'email' o 'password' o 'confirmPassword' o 'telephone' o 'role': NO deben estar vacios."];
        }
    }

    // Metodo para eliminar un usuario de la tabla de la DB: 
    public function destroy($identification)
    {
        // Realizamos la consulta a la tabla de la DB:
        $model = Usuario::where('identification', $identification);

        // Validamos que exista el registro en la tabla de la DB:
        $validateUser = $model->first();

        // Si existe, eliminamos el registro: 
        if($validateUser){

            try{

                // Eliminamos el registro: 
                $model->delete();

                // Retornamos la respuesta:
                return ['delete' => true];

            }catch(Exception $e){
                // Retornamos el error:
                return ['delete' => false, 'error' => $e->getMessage()];
            }

        }else{
            // Retornamos el error:
            return ['delete' => false, 'error' => 'No existe ese usuario en el sistema.'];
        }
    }
}
