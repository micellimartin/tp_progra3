<?php

namespace App\Middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
// use Psr\Http\Message\ResponseInterface as Response;

use \Firebase\JWT\JWT;

//Valida que se envie un token de autenticacion 
class AuthMiddleware
{
    /**
     * Example middleware invokable class
     *
     * @param  ServerRequest  $request PSR-7 request
     * @param  RequestHandler $handler PSR-15 request handler
     *
     * @return Response
     */
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $tokenRecibido = false;
        //Obtengo los headers
        $headers = $request->getHeaders();
        $response = new Response();

        foreach($headers as $key => $value) 
        {
            if($key == 'token')
            {
                $tokenRecibido = true;
                break;
            }
        }

        if($tokenRecibido == true)
        {
            $token = $request->getHeaders()['token'][0];

            if(ValidarEmpleadoMiddleware::validarTokenJWT($token))
            {
                $response = $handler->handle($request);
                $existingContent = (string) $response->getBody();
                $resp = new Response();
                $resp->getBody()->write(($existingContent));
                $response = $resp;
            }
            else
            {
                $response->withStatus(403);
                $response->getBody()->write(json_encode("El token enviado no es valido"));
            }
        }
        else
        {
            $response->withStatus(403);
            $response->getBody()->write(json_encode("Error: Debe enviar el token recibido cuando se logea"));
        }

        return $response;
    }

    public static function validarTokenJWT($token)
    {
        $retorno = "empty";
        $key = "tp_programacion3";

        try 
        {
            //Si tuvo exito en decodificar es porque el token es autentico lo que signifca que el usuario existe, se logeo con exito y le devolvi el token
            //Por lo tanto valido que es un usuario existente y logeado y no es necesario mas validaciones
            $tokenDecoficado = JWT::decode($token, $key, array('HS256'));
            $retorno = true;
        } 
        catch (\Throwable $th) 
        {
            $retorno = false;       
        }

        return $retorno;
    }
}
