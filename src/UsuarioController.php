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
            return response(content: ['query' => true, 'users' => $validateUser], status: 200);

        }else{
            // Retornamos el error:
            return response(content: ['query' => false, 'error' => 'No existen usuarios en el sistema.'], status: 404);
        }
    }

    // Metodo para registrar un usuario en la tabla de la DB: 
    public function store(Request $request)
    {
        // Si los argumentos contienen caracteres de tipo mayusculas, los pasamos a minusculas. Para seguir una nomenclatura estandar:
        $name      = strtolower($request->input(key: 'name'));
        $last_name = strtolower($request->input(key: 'last_name'));
        $role_name = strtolower($request->input(key: 'role'));
        $status_session = strtolower($request->input(key: 'status_session'));  

        // Validamos que los argumentos no esten vacios:
        if(!empty($request->input(key: 'identification'))
        && !empty($name)
        && !empty($last_name)
        && !empty($request->input(key: 'email'))
        && !empty($request->input(key: 'password'))
        && !empty($request->input(key: 'confirmPassword'))
        && !empty($request->input(key: 'telephone'))
        && !empty($role_name)
        && !empty($status_session)){

            // Instanciamos el contolador del modelo 'Role'. Para validar que exista el role: 
            $roleController = new RoleController;

            // Validamos que exista el role: 
            $validateRole = $roleController->show(role: $role_name);

            $contentRole = $validateRole->getOriginalContent();

            // Si existe, extraemos su 'id' y validamos que no exista el usuario: 
            if($contentRole['query']){

                // Extraemos su id: 
                $role_id = $contentRole['role']['id'];

                // Validamos que exista el registro en la tabla de la DB:
                $validateUser = Usuario::where('identification', $request->input(key: 'identification'))->first();

                // Validamos que el 'email' no pertenezca a un usuario de la tabla de la DB: 
                $validateEmail = Usuario::where('email', $request->input(key: 'email'))->first();

                // Sino existe, validamos que no exista el email en la tabla de la DB: 
                if(!$validateUser){

                    // Sino existe ese email, validamos los argumentos: 
                    if(!$validateEmail){

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

                            // Instanciamos el controlador del modelo 'Session'. Para validar que exista esa sesion: 
                            $sessionController = new SessionController;

                            // Validamos que exista la sesion: 
                            $validateSession = $sessionController->show(status_session: $status_session);

                            // Obtenemos el contenido de la sesion: 
                            $contentSession = $validateSession->getOriginalContent();

                            // Si existe, extraemos su 'id' y validamos que exista el usuario: 
                            if($contentSession['query']){

                                // Extraemos el 'id':
                                $session_id = $contentSession['session']['id'];

                                try{

                                    Usuario::create(['identification' => $request->input(key: 'identification'),
                                                    'name' => $name,
                                                    'last_name' => $last_name,
                                                    'email' => $request->input(key: 'email'),
                                                    'password' => bcrypt($request->input(key: 'password')),
                                                    'telephone' => $request->input(key: 'telephone'),
                                                    'role_id' => $role_id,
                                                    'session_id' => $session_id]);
    
                                    // Retornamos la respuesta:
                                    return response(content: $validateUserArgm, status: 201);
    
                                }catch(Exception $e){
                                    // Retornamos el error:
                                    return response(content: ['register' => false, 'error' => $e->getMessage()], status: 500);
                                }

                            }else{
                                // Retornamos el error:
                                return ['update_session' => false, 'error' => $contentSession['error']];
                            }

                        }else{
                            // Retornamos el error:
                            return response(content: $validateUserArgm, status: 403);
                        }

                    }else{
                        // Retornamos el error:
                        return response(content: ['register' => false, 'error' => 'Ese email ya existe en el sistema.'], status: 403);
                    }

                }else{
                    // Retornamos el error:
                    return response(content: ['register' => false, 'error' => 'Ya existe ese usuario en el sistema.'], status: 403);
                }

            }else{
                // Retornamos el error:
                return response(content: ['register' => false, 'error' => $contentRole['error']], status: 404);
            }

        }else{
            // Retornamos el error:
            return response(content: ['register' => false, 'error' => "Campo 'identification' o 'name' o 'last_name' o 'email' o 'password' o 'confirmPassword' o 'telephone' o 'role' o 'status_session': NO deben estar vacios."], status: 403);
        }
    }

    // Metodo para retornar un usuario especifico: 
    public function show($email)
    {
        // Realizamos la consulta a la tabla de la DB:
        $model = DB::table('usuarios')

                // Filtramos el usuario requerido: 
                ->where('email', $email)

                // Realizamos la consulta a la tabla del modelo 'Role: 
                ->join('roles', 'roles.id_role', '=', 'usuarios.role_id')

                // Seleccionamos los campos que se requiren: 
                ->select('usuarios.identification', 'usuarios.name', 'usuarios.last_name', 'usuarios.email', 'usuarios.telephone', 'usuarios.session', 'roles.role_name as role');

        // Validamos que exista el registro en la tabla de la DB:
        $validateUser = $model->first();

        // Si existe, retornamos el registro: 
        if($validateUser){

            // Retornamos la respuesta:
            return response(content: ['query' => true, 'user' => $validateUser], status: 200);

        }else{
            // Retornamos el error:
            return response(content: ['query' => false, 'error' => 'No existe ese usuario en el sistema.'], status: 404);
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

            $contentRole = $validateRole->getOriginalContent();
 
            // Si existe, extraemos su 'id' y validamos que no exista el usuario: 
            if($contentRole['query']){
 
                // Extraemos su id: 
                $role_id = $contentRole['role']['id'];

                // Realizamos la consulta a la tabla de la DB:
                $model = Usuario::where('identification', $identification);
 
                // Validamos que exista el registro en la tabla de la DB:
                $validateUser = $model->first();

                // Validamos que el 'email' no pertenezca a un usuario de la tabla de la DB: 
                $validateEmail = Usuario::where('email', $request->input(key: 'email'))->first();
 
                // Si existe, validamos que no exista ese email en la tabla de la DB: 
                if($validateUser){

                    // Sino existe el email, validamos los argumentos: 
                    if(!$validateEmail){

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

                                // Actualizamos el registro: 
                                $model->update(['name' => $name,
                                                'last_name' => $last_name,
                                                'email' => $request->input(key: 'email'),
                                                'password' => bcrypt($request->input(key: 'password')),
                                                'telephone' => $request->input(key: 'telephone'),
                                                'role_id' => $role_id]);

                                // Retornamos la respuesta:
                                return response(content: $validateUserArgm, status: 204);

                            }catch(Exception $e){
                                // Retornamos el error:
                                return response(content: ['register' => false, 'error' => $e->getMessage()], status: 500);
                            }

                        }else{
                            // Retornamos el error:
                            return response(content: $validateUserArgm, status: 403);
                        }

                    }else{
                        // Retornamos el error:
                        return response(content: ['register' => false, 'error' => 'Ese email ya existe en el sistema.'], status: 403);
                    }
 
                }else{
                    // Retornamos el error:
                    return response(content: ['register' => false, 'error' => 'No existe ese usuario en el sistema.'], status: 404);
                }
 
            }else{
                // Retornamos el error:
                return response(content: ['register' => false, 'error' => $contentRole['error']], status: 404);
            }
 
        }else{
            // Retornamos el error:
            return response(content: ['register' => false, 'error' => "Campo 'name' o 'last_name' o 'email' o 'password' o 'confirmPassword' o 'telephone' o 'role': NO deben estar vacios."], status: 403);
        }
    }

    // Metodo para actualizar la sesion del usuario: 
    public function updateSession($email, $status_session)
    {
        // Instanciamos el controlador del modelo 'Session'. Para validar que exista esa sesion: 
        $sessionController = new SessionController;

        // Validamos que exista la sesion: 
        $validateSession = $sessionController->show(status_session: $status_session);

        // Obtenemos el contenido de la sesion: 
        $contentSession = $validateSession->getOriginalContent();

        // Si existe, extraemos su 'id' y validamos que exista el usuario: 
        if($contentSession['query']){

            // Extraemos el 'id': 
            $session_id = $contentSession['session']['id'];

            // Realizamos la consulta a la tabla de la DB:
            $model = Usuario::where('email', $email);

            // Validamos que exista el registro en la tabla de la DB:
            $validateUser = $model->first();

            // Si existe, actualizamos la sesion: 
            if($validateUser){

                try{

                    $model->update(['session_id' => $session_id]);

                    // Retornamos la respuesta:
                    return ['update_session' => true];

                }catch(Exception $e){
                    // Retornamos el error:
                    return ['update_session' => false, 'error' => $e->getMessage()];
                }

            }else{
                // Retornamos el error:
                return ['update_sesion' => false, 'error' => 'No existe el usuario en el sistema.'];
            }

        }else{
            // Retornamos el error:
            return ['update_session' => false, 'error' => $contentSession['error']];
        }
    }

    // Metodo para aniadir la URL del avatar del usuario: 
    public function addAvatar($email, $url)
    {
        // Realizamos la consulta a la tabla de la DB:
        $model = Usuario::where('email', $email);

        // Validamos que exista el registro en la tabla de la DB:
        $validateUser = $model->first();

        // Si existe, actualizamos el campo 'avatar' de la tabla de la DB: 
        if($validateUser){

            try{

                // Actualizamos el campo 'avatar': 
                $model->update(['avatar' => $url]);

                // Retornamos la respuesta:
                return response(content: ['add' => true], status: 204); 

            }catch(Exception $e){
                // Retornamos el error:
                return response(content: ['add' => false, 'error' => $e->getMessage()], status: 500);
            }

        }else{
            // Retornamos el error:
            return response(content: ['add' => false, 'error' => 'No existe ese usuario en el sistema.'], status: 404);
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
                return response(content: ['delete' => true], status: 204);

            }catch(Exception $e){
                // Retornamos el error:
                return response(content: ['delete' => false, 'error' => $e->getMessage()], status: 500);
            }

        }else{
            // Retornamos el error:
            return response(content: ['delete' => false, 'error' => 'No existe ese usuario en el sistema.'], status: 404);
        }
    }
}
