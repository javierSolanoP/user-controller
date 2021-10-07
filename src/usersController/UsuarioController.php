<?php

namespace App\Http\Controllers\Modulo_usuarios\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Modulo_usuarios\Class\Usuario as ClassUsuario;
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

                // Seleccionamos los campos requeridos: 
                ->select('usuarios.identificacion', 'usuarios.nombre', 'usuarios.apellido', 'usuarios.email', 'usuarios.telefono', 'roles.nombre_role as role')

                // Traemos todos los registros de la tabla de la DB: 
                ->get();

        // Retornamos la respuesta: 
        return ['query' => true, 'usuarios' => $model];
    }

    // Metodo para registrar un nuevo permiso en la tabla de la DB: 
    public function store(Request $request)
    {
        // Si los argumentos contienen caracteres de tipo mayusculas, los pasamos a tipo minusculas. Para seguir una nomenclatura estandar: 
        $nombre         = strtolower($request->input(key: 'nombre'));
        $apellido       = strtolower($request->input(key: 'apellido'));
        $email          = strtolower($request->input(key: 'email'));
        $nombre_role    = strtolower($request->input(key: 'role'));

        // Validamos que los argumentos no esten vacios: 
        if(!empty($request->input(key: 'identificacion'))
        && !empty($nombre)
        && !empty($apellido)
        && !empty($email)
        && !empty($request->input(key: 'telefono'))
        && !empty($nombre_role)){

            // Instanciamos el controlador del modelo 'Role', para validar que exista el role: 
            $role = new RoleController; 

            // Validamos que exista el role: 
            $validarRole = $role->show(role: $nombre_role);

            // Si existe, realizamos la consulta a la tabla de la DB: 
            if($validarRole['query']){

                // Realizamos la consulta a la tabla de la DB: 
                $model = Usuario::where('identificacion', $request->input(key: 'identificacion'));

                // Validamos que no exista el usuario en el sistema: 
                $validarUsuario = $model->first();

                // Sino existe, validamos los argumentos: 
                if(!$validarUsuario){

                    // Instanciamos la clase 'Usuario', para validar los argumentos: 
                    $usuario = new ClassUsuario(identificacion: $request->input(key: 'identificacion'),
                                                nombre: $nombre,
                                                apellidos: $apellido,
                                                email: $email,
                                                telefono: $request->input(key: 'telefono'));

                    // Asignamos a la sesion 'registrar' la instancia 'Usuario', con sus propiedades cargadas de data: 
                    $_SESSION['registrar'] = $usuario; 

                    // Validamos los argumentos: 
                    $validarUsuarioArgm = $usuario->registerData();

                    // Si los argumentos han sido validados, procedemos a realizar el registro: 
                    if($validarUsuarioArgm['registrar']){

                        try{

                            // Realizamos el registro: 
                            Usuario::create(['identificacion' => $request->input(key: 'identificacion'),
                                            'nombre' => $nombre,
                                            'apellido' => $apellido,
                                            'email' => $email,
                                            'password' => bcrypt($request->input(key: 'identificacion')),
                                            'telefono' => $request->input(key: 'telefono'),
                                            'role_id' => $validarRole['role']['id_role']]);

                            // Retornamos la respuesta: 
                            return $validarUsuarioArgm;

                        }catch(Exception $e){
                            // Retornamos el error: 
                            return ['registrar' => false, 'error' => $e->getMessage()];
                        }

                    }else{
                        // Retornamos el error: 
                        return $validarUsuarioArgm;
                    }

                }else{
                    // Retornamos el error: 
                    return ['registrar' => false, 'error' => 'Ya existe el usuario en el sistema.'];
                }

            }else{
                // Retornamos el error: 
                return ['registrar' => false, 'error' => $validarRole['error']];
            }

        }else{
            // Retornamos el error: 
            return ['registrar' => false, 'error' => "Campo 'identificacion' o 'nombre' o 'apellido' o 'email' o 'telefono' o 'role': No deben estar vacios."];
        }

    }

    // Metodo para retornar un registro en especifico de la tabla de la DB: 
    public function show($identificacion)
    {
        // Realizamos la consulta a la tabla de la DB: 
        $model = DB::table('usuarios')

                // Fitramos el registro solicitado: 
                ->where('identificacion', $identificacion)

                // Realizamos la consulta a la tabla del modelo 'Role':
                ->join('roles', 'roles.id_role', '=', 'usuarios.role_id')

                // Seleccionamos los campos requeridos: 
                ->select('usuarios.identificacion', 'usuarios.nombre', 'usuarios.apellido', 'usuarios.email', 'usuarios.telefono', 'roles.nombre_role as role');

        // Validamos que exista el usuario: 
        $validarUsuario = $model->first();

        // Si existe el registro, retornamos la informacion del mismo: 
        if($validarUsuario){

            // Retornamos la respuesta: 
            return ['query' => true, 'usuario' => $validarUsuario];

        }else{
            // Retornamos el error: 
            return ['query' => false, 'error' => 'No existe ese usuario en el sistema.'];
        }
    }

    
    // Metodo para actualizar un reistro en especifico de la tabla de la DB: 
    public function update(Request $request, $identificacion)
    {
        // Si los argumentos contienen caracteres de tipo mayusculas, los pasamos a tipo minusculas. Para seguir una nomenclatura estandar: 
        $new_nombre         = strtolower($request->input(key: 'new_nombre'));
        $new_apellido       = strtolower($request->input(key: 'new_apellido'));
        $new_email          = strtolower($request->input(key: 'new_email'));
        $new_nombre_role    = strtolower($request->input(key: 'new_role'));

        // Validamos que los argumentos no esten vacios: 
        if(!empty($new_nombre)
        && !empty($new_apellido)
        && !empty($new_email)
        && !empty($request->input(key: 'new_telefono'))
        && !empty($new_nombre_role)){

            // Instanciamos el controlador del modelo 'Role', para validar que exista el role: 
            $role = new RoleController; 

            // Validamos que exista el role: 
            $validarRole = $role->show(role: $new_nombre_role);
 
            // Si existe, realizamos la consulta a la tabla de la DB: 
            if($validarRole['query']){

                // Realizamos la consulta a la tabla de la DB: 
                $model = Usuario::where('identificacion', $identificacion);

                // Validamos que exista el usuario en el sistema: 
                $validarUsuario = $model->first();

                // Si existe, validamos los argumentos: 
                if($validarUsuario){

                    // Instanciamos la clase 'Usuario', para validar los argumentos: 
                    $usuario = new ClassUsuario(nombre: $new_nombre,
                                                apellidos: $new_apellido,
                                                email: $new_email,
                                                telefono: $request->input(key: 'new_telefono'));

                    // Asignamos a la sesion 'registrar' la instancia 'Usuario', con sus propiedades cargadas de data: 
                    $_SESSION['registrar'] = $usuario; 

                    // Validamos los argumentos: 
                    $validarUsuarioArgm = $usuario->updateData();

                    // Si los argumentos han sido validados, procedemos a realizar el registro: 
                    if($validarUsuarioArgm['registrar']){

                        try{

                            // Realizamos el registro: 
                            $model->update(['nombre' => $new_nombre,
                                            'apellido' => $new_apellido,
                                            'email' => $new_email,
                                            'telefono' => $request->input(key: 'new_telefono'),
                                            'role_id' => $validarRole['role']['id_role'] ]);

                            // Retornamos la respuesta: 
                            return $validarUsuarioArgm;

                        }catch(Exception $e){
                            // Retornamos el error: 
                            return ['registrar' => false, 'error' => $e->getMessage()];
                        }

                    }else{
                        // Retornamos el error: 
                        return $validarUsuarioArgm;
                    }

                }else{
                    // Retornamos el error: 
                    return ['registrar' => false, 'error' => 'No existe el usuario en el sistema.'];
                }

            }else{
                // Retornamos el error: 
                return ['registrar' => false, 'error' => $validarRole['error']];
            }

        }else{
            // Retornamos el error: 
            return ['registrar' => false, 'error' => "Campo o 'new_nombre' o 'new_apellido' o 'new_email' o 'new_telefono' o 'new_role': No deben estar vacios."];
        }
    }

    // Metodo para eliminar un regitro especifico de la tabla de la DB: 
    public function destroy($identificacion)
    {
        // Realizamos la consulta a la tabla de la DB: 
        $model = Usuario::where('identificacion', $identificacion);

        // Validamos que exista el usuario: 
        $validarUsuario = $model->first();

        // Si existe el registro, lo eliminamos de la tabla de la DB:  
        if($validarUsuario){

            try{

                // Realizamos la eliminacion del registro: 
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

    public function FunctionName(Type $var = null)
    {
        
        
            
        
        
        
        
    }
}
