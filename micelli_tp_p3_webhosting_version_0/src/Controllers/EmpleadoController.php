<?php

//hacer composer dumpautoload -o 
namespace App\Controllers;

use \Firebase\JWT\JWT;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Models\Empleado;

class EmpleadoController
{
    //(GET)Obtener todos los empleados
    public function getAll(Request $request, Response $response, $args) 
    {
        $rta = Empleado::get();
        $response->getBody()->write(json_encode($rta));
        return $response;
    }

    //(GET)Obtener un empleado especifico
    public static function getOne(Request $request, Response $response, $args) 
    {
        //localhost/micelli_segundo_parcial/public/empleado/5
        $rta = Empleado::find($args["id"]);
     
        if($rta == null)
        {
            $response->getBody()->write(json_encode("No existe un empleado de id: $args[id]"));
        }
        else
        {
            $response->getBody()->write(json_encode($rta));
        }
  
        return $response;
    }

    //(POST)Dar de alta un empleado
    public function registro(Request $request, Response $response, $args)
    {       
        $parsedBody = $request->getParsedBody();
        
        if(!(empty($parsedBody["email"])) && !(empty($parsedBody["clave"])) && !(empty($parsedBody["nombre"])) && !(empty($parsedBody["apellido"])))
        {
            $validacionMail = EmpleadoController::validarMail($parsedBody["email"]);

            if($validacionMail == true)
            {
                $email = $parsedBody["email"];
                $clave = $parsedBody["clave"];
                $nombre = $parsedBody["nombre"];
                $apellido = $parsedBody["apellido"];
                
                $nuevoEmpleado = new Empleado();
                $nuevoEmpleado->email = $email;
                $nuevoEmpleado->clave = $clave;
                $nuevoEmpleado->nombre = $nombre;
                $nuevoEmpleado->apellido = $apellido;
            
                $nuevoEmpleado->save();
                $response->getBody()->write(json_encode("Registro de empleado exitoso!")); 
            }
            else
            {
                $response->getBody()->write(json_encode("Error: email repetido"));
            }
        }
        else
        {
            $response->getBody()->write(json_encode("Para registrarse como empleado tiene que enviar email, clave, nombre y apellido por body"));
        }
    
        return $response;
    }

    //Validar que el email no sea repetido
    public static function validarMail($email)
    {
        //mail no repetido
        $retorno = true;

        $empleados = Empleado::get();

        foreach($empleados as $empleado)
        {
            if($email == $empleado->email)
            {
                //mail repetido
                $retorno = false;
                break;
            }
        }

        return $retorno;
    }

    //(POST)Logearse como empleado
    public function login(Request $request, Response $response, $args)
    {
        $parsedBody = $request->getParsedBody();

        if(!(empty($parsedBody["email"])))
        {
            if(!(empty($parsedBody["clave"])))
            {   
                $empleado = "";

                $empleado = Empleado::where('email', "=" , $parsedBody["email"])->where('clave', "=" , $parsedBody["clave"])->get();

                //When TRUE, returned objects will be converted into associative arrays.
                //Sin el true, devolveria un objeto standar
                $empleadoDecodifcado = json_decode($empleado, true);
                //Si no decodifico no puedo verificar si esta vacio el array
                //Si el get devolvio un array vacio es porque el usuario no existe
                if(!empty($empleadoDecodifcado))
                {
                    $email = "";
                    $id = "";
                    $tipo_usuario = "";
                
                    foreach($empleadoDecodifcado as $value)
                    {
                        $id = $value["id"];
                        $email = $value["email"];
                        $tipo_usuario = $value["tipo_usuario"];
                    }
                
                    $token = EmpleadoController::generarTokenJWT($id ,$email, $tipo_usuario);
                    $response->getBody()->write(json_encode($token));
                }
                else
                {
                    $response->getBody()->write(json_encode("ERROR: no existe un empleado con ese mail y clave"));
                }
            }
            else
            {
                $response->getBody()->write(json_encode("ERROR: para logearse como empleado necesita enviar clave por body"));
            }
        }
        else
        {
            $response->getBody()->write(json_encode("ERROR: para logearse como empleado necesita enviar email por body"));
        }

        return $response;
    }

    //Obtener el id de un empleado al azar
    public static function getEmpleadoIdRandom() 
    {
        $empleados = Empleado::get();

        $arrayIds = [];

        foreach($empleados as $empleado) 
        {
            //La transformo a array asociativo
            $empleadoDecodifcado = json_decode($empleado, true);
            array_push($arrayIds, $empleadoDecodifcado["id"]);
        }

        $indiceRandom = array_rand($arrayIds);
        $idRandom = $arrayIds[$indiceRandom];
        
        return $idRandom;
    }
    
    //Genera un jason web token con la info del empleado
    public static function generarTokenJWT($id, $email, $tipo_usuario)
    {
        $key = "tp_programacion3";

        $payload = array(
            "id" => $id,
            "email" => $email,
            "tipo_usuario" => $tipo_usuario
        );

        $token = JWT::encode($payload, $key);

        return $token;
    }
}

?>