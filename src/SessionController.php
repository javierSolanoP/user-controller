<?php

namespace App\Http\Controllers;

use App\Models\Session;
use App\Models\Usuario;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SessionController extends Controller
{
    // Metodo para retornar todos los registros de la tabla de la DB: 
    public function index()
    {
        // Realizamos la consulta a la tabla de la DB:
        $model = Session::all();

        // Validamos que existan registros en la tabla de la DB:
        if(count($model) != 0){

            // Retornamos la respuesta:
            return response(content: ['query' => true, 'sessions' => $model], status: 200);

        }else{
            // Retornamos el error:
            return response(content: ['register' => false, 'error' => 'No existen sesiones en el sistema.'], status: 404);
        }
    }

    // Metodo para retornar los usuarios agrupados por estados de sesion: 
    public function getUsers()
    {
        // Realizamos la consulta a la tabla de los modelos 'Session' y 'User': 
        $model = DB::table('sessions')

                    // Realizamos la consulta a la tabla del modelo 'User': 
                    ->join('usuarios', 'usuarios.session_id', 'sessions.id_session')

                    // Seleccionamos los campos que se requieren: 
                    ->select('usuarios.email', 'sessions.status_session as status')

                    // Obtenemos los usuarios: 
                    ->get()

                    // Los agrupamos por estados de sesion: 
                    ->groupBy('status');

        // Validamos que exista los estados en la tabla de la DB:
        if(count($model) != 0){

            // Declaramos el arreglo 'registers'. Para almacenar los usuarios con indice numerico: 
            $registers = [];

            // Iteramos cada registro de los usuarios para almacenarlos en el arreglo: 
            foreach($model as $user){

                // Almacenamos el usuario en el arreglo: 
                $registers[] = $user;

            }

            // Retornamos la respuesta:
            return response(content: ['query' => true, 'users' => $registers]);

        }else{
            // Retornamos el error:
            return response(content: ['query' => false, 'error' => 'No existen usuarios con sesiones en el sistema.']);
        }
    }

    // Metodo para registar una sesion en la tabla de la DB: 
    public function store(Request $request)
    {
        // Si los argumentos contienen caracteres de tipo mayusculas, los pasamos a minusculas. Para seguir una nomenclatura estandar:
        $status = strtolower($request->input(key: 'status_session'));

        // Validamos que el argumento no este vacio:
        if(!empty($status)){

            // Realizamos la consulta a la tabla de la DB:
            $model = Session::where('status_session', $status);

            // Validamos que exista el registro en la tabla de la DB:
            $validateSession = $model->first();

            // Sino existe, actualizamos la sesion: 
            if(!$validateSession){

                try{

                    // Actualizamos la sesion: 
                    Session::create(['status_session' => $status]);

                    // Retornamos la respuesta:
                    return response(content: ['session' => true], status: 201);

                }catch(Exception $e){
                    // Retornamos el error:
                    return response(content: ['session' => false, 'error' => $e->getMessage()], status: 500);
                }

            }else{
                // Retornamos el error:
                return response(content: ['session' => false, 'error' => 'Ya existe esa sesion en el sistema.'], status: 403);
            }

        }else{
            // Retornamos el error:
            return response(content: ['register' => false, 'error' => "Campo 'status_session': NO debe estar vacio."], status: 403);
        }

    }

    // Metodo para retornar una sesion especifica: 
    public function show($status_session)
    {
        // Si los argumentos contienen caracteres de tipo mayusculas, los pasamos a minusculas. Para seguir una nomenclatura estandar:
        $status = strtolower($status_session);

        // Validamos que el argumento no este vacio:
        if(!empty($status)){

            // Realizamos la consulta a la tabla de la DB:
            $model = Session::select('id_session as id', 'status_session')->where('status_session', $status);

            // Validamos que exista el registro en la tabla de la DB:
            $validateSession = $model->first();

            // Si existe, retornamos la sesion: 
            if($validateSession){

                // Retornamos la respuesta:
                return response(content: ['query' => true, 'session' => $validateSession], status: 200);

            }else{
                // Retornamos el error:
                return response(content: ['query' => false, 'error' => 'No existe la sesion en el sistema.'], status: 404);
            }

        }else{
            // Retornamos el error:
            return response(content: ['query' => false, 'error' => "Campo 'status_session': NO debe estar vacio."], status: 403);
        }
    }

    // Metodo para retornar los usuarios de un estado de sesion especifico: 
    public function getSession($status_session)
    {
        // Si los argumentos contienen caracteres de tipo mayusculas, los pasamos a minusculas. Para seguir una nomenclatura estandar:
        $status = strtolower($status_session);

        // Validamos que el argumento no este vacio:
        if(!empty($status)){

            // Realizamos la consulta a la tabla de la DB:
            $model = Session::where('status_session', $status);

            // Validamos que exista el registro en la tabla de la DB:
            $validateSession = $model->first();

            // Si existe, realizamos la consulta a las tablas de la DB:  
            if($validateSession){

                // Realizamos la consulta a las tablas de los modelos 'Session' y 'User':
                $users = DB::table('sessions')

                            // Filtramos por el estado de sesion requerido: 
                            ->where('status_session', $status_session)

                            // Realizamos la consulta a la tabla del modelo 'User': 
                            ->join('usuarios', 'usuarios.session_id', '=', 'sessions.id_session')

                            // Seleccionamos los campos que se requieren: 
                            ->select('usuarios.email', 'sessions.status_session')

                            // Obtenemos todos los usuarios con ese estado de sesion: 
                            ->get();

                // Retornamos la respuesta:
                return response(content: ['query' => true, 'users' => $users], status: 200);

            }else{
                // Retornamos el error:
                return response(content: ['query' => false, 'error' => 'No existe esa sesion en el sistema.'], status: 403);
            }

        }else{
            // Retornamos el error:
            return response(content: ['query' => false, 'error' => "Campo 'status_session': NO debe estar vacio."], status: 403);
        }
    }

    // Metodo para eliminar una sesion en la tabla de la DB: 
    public function destroy($status_session)
    {
        // Si los argumentos contienen caracteres de tipo mayusculas, los pasamos a minusculas. Para seguir una nomenclatura estandar:
        $status = strtolower($status_session);

        // Validamos que el argumento no este vacio:
        if(!empty($status)){

            // Realizamos la consulta a la tabla de la DB:
            $model = Session::where('status_session', $status);

            // Validamos que exista el registro en la tabla de la DB:
            $validateSession = $model->first();

            // Si existe, eliminamos la sesion: 
            if($validateSession){

                try{

                    // Actualizamos la sesion: 
                    $model->delete();

                    // Retornamos la respuesta:
                    return response(content: ['delete' => true], status: 204);

                }catch(Exception $e){
                    // Retornamos el error:
                    return response(content: ['delete' => false, 'error' => $e->getMessage()], status: 500);
                }

            }else{
                // Retornamos el error:
                return response(content: ['delete' => false, 'error' => 'No existe esa sesion en el sistema.'], status: 403);
            }

        }else{
            // Retornamos el error:
            return response(content: ['delete' => false, 'error' => "Campo 'status_session': NO debe estar vacio."], status: 403);
        }

    }

}
