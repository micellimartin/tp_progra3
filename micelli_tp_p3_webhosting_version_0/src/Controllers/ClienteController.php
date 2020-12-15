<?php

//hacer composer dumpautoload -o 
namespace App\Controllers;

use \Firebase\JWT\JWT;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Models\Cliente;

class ClienteController
{
    //(GET)Obtener todos los clientes
    public function getAll(Request $request, Response $response, $args) 
    {
        $rta = Cliente::get();
        $response->getBody()->write(json_encode($rta));
        return $response;
    }

    //(GET)Obtener un cliente especifico
    public static function getOne(Request $request, Response $response, $args) 
    {
        //localhost/micelli_tp_p3/public/cliente/5
        $rta = Cliente::find($args["id"]);
     
        if($rta == null)
        {
            $response->getBody()->write(json_encode("No existe un cliente de id: $args[id]"));
        }
        else
        {
            $response->getBody()->write(json_encode($rta));
        }
  
        return $response;
    }

    //(POST)Dar de alta un cliente
    public function registro(Request $request, Response $response, $args)
    {       
        $parsedBody = $request->getParsedBody();
        
        if(!(empty($parsedBody["email"])) && !(empty($parsedBody["clave"])) && !(empty($parsedBody["nombre"])) && !(empty($parsedBody["apellido"])))
        {
            $validacionMail = ClienteController::validarMail($parsedBody["email"]);

            if($validacionMail == true)
            {
                $email = $parsedBody["email"];
                $clave = $parsedBody["clave"];
                $nombre = $parsedBody["nombre"];
                $apellido = $parsedBody["apellido"];
                
                $nuevoCliente = new Cliente();
                $nuevoCliente->email = $email;
                $nuevoCliente->clave = $clave;
                $nuevoCliente->nombre = $nombre;
                $nuevoCliente->apellido = $apellido;
            
                $nuevoCliente->save();
                $response->getBody()->write(json_encode("Registro de cliente exitoso!")); 
            }
            else
            {
                $response->getBody()->write(json_encode("Error: email repetido"));
            }
        }
        else
        {
            $response->getBody()->write(json_encode("Para registrarse como cliente tiene que enviar email, clave, nombre y apellido por body"));
        }
    
        return $response;
    }

    //Validar que el email no sea repetido
    public static function validarMail($email)
    {
        //mail no repetido
        $retorno = true;

        $clientes = Cliente::get();

        foreach($clientes as $cliente)
        {
            if($email == $cliente->email)
            {
                //mail repetido
                $retorno = false;
                break;
            }
        }

        return $retorno;
    }

    //(POST)Logearse como cliente
    public function login(Request $request, Response $response, $args)
    {
        $parsedBody = $request->getParsedBody();

        if(!(empty($parsedBody["email"])))
        {
            if(!(empty($parsedBody["clave"])))
            {   
                $cliente = "";

                $cliente = Cliente::where('email', "=" , $parsedBody["email"])->where('clave', "=" , $parsedBody["clave"])->get();

                //When TRUE, returned objects will be converted into associative arrays.
                //Sin el true, devolveria un objeto standar
                $clienteDecodifcado = json_decode($cliente, true);
                //Si no decodifico no puedo verificar si esta vacio el array
                //Si el get devolvio un array vacio es porque el usuario no existe
                if(!empty($clienteDecodifcado))
                {
                    $email = "";
                    $id = "";
                    $tipo_usuario = "";
                
                    foreach($clienteDecodifcado as $value)
                    {
                        $id = $value["id"];
                        $email = $value["email"];
                        $tipo_usuario = $value["tipo_usuario"];
                    }
                
                    $token = ClienteController::generarTokenJWT($id ,$email, $tipo_usuario);
                    $response->getBody()->write(json_encode($token));
                }
                else
                {
                    $response->getBody()->write(json_encode("ERROR: no existe un cliente con ese mail y clave"));
                }
            }
            else
            {
                $response->getBody()->write(json_encode("ERROR: para logearse como cliente necesita enviar clave por body"));
            }
        }
        else
        {
            $response->getBody()->write(json_encode("ERROR: para logearse como cliente necesita enviar email por body"));
        }

        return $response;
    }

    //Genera un jason web token con la info del cliente
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

    //Obtiene y muestra el menu
    public function mostrarInico(Request $request, Response $response, $args)
    {
        $rta = "Esta es la pagina de inicio. Aca se muestra la lista de rutas validas";
        $response->getBody()->write(json_encode($rta));
        return $response;
    }
}

?>