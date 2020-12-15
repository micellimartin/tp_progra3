<?php

namespace App\Middlewares;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class JsonMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler) 
    {
        //obtiene peticion
        $response = $handler->handle($request);
        //obtiene el body por si lo quiero modificar
        //$existingContent = (string) $response->getBody();
        //$response = new Response();
    
        //Transforme el request en una respuesta, la modifico y la retorno
        //Ahora si ejecutas la consulta en postman y vas a headers la ppiedad content-type dice json en vez de texto
        $response = $response->withHeader('Content-type', 'application/json');
        
        return $response;
    }
}

?>