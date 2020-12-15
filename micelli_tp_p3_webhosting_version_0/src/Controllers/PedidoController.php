<?php

//hacer composer dumpautoload -o 
namespace App\Controllers;

use \Firebase\JWT\JWT;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Models\Pedido;

class PedidoController
{
    //(GET)Obtener todos los pedidos
    public function getAll(Request $request, Response $response, $args)
    {
        $rta = Pedido::get();
        $response->getBody()->write(json_encode($rta));
        return $response;
    }

    //(GET)Obtener un pedido especifico
    public static function getOne(Request $request, Response $response, $args)
    {
        //localhost/micelli_segundo_parcial/public/empleado/5
        $rta = Pedido::find($args["id"]);

        if ($rta == null) {
            $response->getBody()->write(json_encode("No existe un pedido de id: $args[id]"));
        } else {
            $response->getBody()->write(json_encode($rta));
        }

        return $response;
    }

    //(POST)Dar de alta un pedido
    public function registro(Request $request, Response $response, $args)
    {       
        $parsedBody = $request->getParsedBody();

        if(!(empty($parsedBody["comida"])) && !(empty($parsedBody["bebida"])))
        {
            $validacionComida = PedidoController::validarComida(strtolower($parsedBody["comida"]));
            $validacionBebida = PedidoController::validarBebida(strtolower($parsedBody["bebida"]));

            if($validacionComida == true && $validacionBebida == true)
            {
                $codigo = PedidoController::generarCodigo();
                $bebida = $parsedBody["bebida"];
                $comida = $parsedBody["comida"];
                $precio = PedidoController::obtenerPrecioPedido(strtolower($comida), strtolower($bebida));
                //Todos los pedidos arrancan en este estado
                $estado = "En preparacion";

                //Obtengo el id del cliente del token
                $header = getallheaders();
                $token = JWT::decode($header['token'], "tp_programacion3", array('HS256'));
                $id_cliente = $token->id;
                //El empleado encargado de este pedido se elige al azar
                $id_empleado = EmpleadoController::getEmpleadoIdRandom();
                
                $nuevoPedido = new Pedido();
                $nuevoPedido->codigo = $codigo;
                $nuevoPedido->bebida = $bebida;
                $nuevoPedido->comida = $comida;
                $nuevoPedido->precio = $precio;
                $nuevoPedido->estado = $estado;
                $nuevoPedido->id_cliente = $id_cliente;
                $nuevoPedido->id_empleado = $id_empleado;

                $nuevoPedido->save();
                $response->getBody()->write(json_encode("Informacion de su pedido:")); 
                $response->getBody()->write(json_encode($nuevoPedido)); 
                $response->getBody()->write(json_encode("El codigo para retirar su pedido es: $codigo - Aguarde mientras un empleado prepara su orden")); 
            }
            else
            {
                $response->getBody()->write(json_encode("Error: comida o bebida invalidos"));
            }
        }
        else
        {
            $response->getBody()->write(json_encode("Para encargar un pedido tiene que enviar comida y bebida por body"));
        }
    
        return $response;
    }

    //Generar codigo alfanumerico aleatorio de 5 digitos
    public static function generarCodigo()
    {
        //caracteres posibles
        $caracteres = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        //str_shuffle mezcla el contenido del string caracteres
        //substr retorna parte del string mezclado, start = 0 porque arranca desde el primer caracter y length = 5 porque esa es la longitud deseada
        $codigo = substr(str_shuffle($caracteres), 0, 5);
        return $codigo;
    }

    //Devuelve el menu de comidas y bebidas disponibles
    public static function mostrarMenu()
    {
        $menu = "
        ---------- MENU DE HOY ----------

        Bebidas:
        ----------
        Agua: $10
        Coca-Cola: $15
        Sprite: $15
        Cerveza: $20

        Comidas:
        -----------
        Milanesa con pure: $30
        Ravioles al fileto: $25
        Guiso de lentejas: $20
        Asado con fritas: $50
        Filet de merluza: $40";

        return $menu;
    }

    //Obtiene y muestra el menu
    public function ObtenerMenu(Request $request, Response $response, $args)
    {
        $rta = PedidoController::mostrarMenu();
        $response->getBody()->write(($rta));
        return $response;
    }

    //Valida que la comida enviada este dentro del menu
    public static function validarComida($comida)
    {
        //comida invalida
        $retorno = false;

        switch ($comida) {
            case 'milanesa con pure': {
                    $retorno = true;
                }
                break;

            case 'ravioles al fileto': {
                    $retorno = true;
                }
                break;

            case 'guiso de lentejas': {
                    $retorno = true;
                }
                break;

            case 'asado con fritas': {
                    $retorno = true;
                }
                break;

            case 'filet de merluza': {
                    $retorno = true;
                }
                break;
        }

        return $retorno;
    }

    //Valida que la bebida enviada este dentro del menu
    public static function validarBebida($bebida)
    {
        //bebida invalida
        $retorno = false;

        switch ($bebida) {
            case 'agua': {
                    $retorno = true;
                }
                break;

            case 'coca-cola': {
                    $retorno = true;
                }
                break;

            case 'sprite': {
                    $retorno = true;
                }
                break;

            case 'cerveza': {
                    $retorno = true;
                }
                break;
        }

        return $retorno;
    }

    //Obtiene el precio total del pedido
    public static function obtenerPrecioPedido($comida, $bebida)
    {
        $precio = 0;

        switch($comida) 
        {
            case 'milanesa con pure': 
            {
                $precio += 30;
            }
            break;

            case 'ravioles al fileto': 
            {
                $precio += 25;
            }
            break;

            case 'guiso de lentejas': 
            {
                $precio += 20;
            }
            break;

            case 'asado con fritas': 
            {
                $precio += 50;
            }
            break;

            case 'filet de merluza': 
            {
                $precio += 40;
            }
            break;
        }

        switch ($bebida) 
        {
            case 'agua': 
            {
                $precio += 10;
            }
            break;

            case 'coca-cola': 
            {
                $precio += 15;
            }
            break;

            case 'sprite': 
            {
                $precio += 15;
            }
            break;

            case 'cerveza': 
            {
                $precio += 20;
            }
            break;
        }

        return $precio;
    }

    //Muestra la informacion del pedido junto con los emails de el cliente que lo ordeno y el empleado que tiene a cargo su preparacion
    public static function funcionJoin (Request $request, Response $response, $args)
    {
        $consulta = Pedido::join('clientes', 'pedidos.id_cliente', '=', 'clientes.id')
        ->join('empleados', 'pedidos.id_empleado', '=', 'empleados.id')
        ->select('pedidos.codigo as codigo del pedido', 'pedidos.bebida', 'pedidos.comida' , 'pedidos.estado', 'pedidos.precio' , 'clientes.email as ordenado por', 
        'empleados.email as preparacion a cargo de')->get();

        $response->getBody()->write(json_encode($consulta));

        return $response;
    }

    //(POST)Cambiar el estado de un pedido de 'En preparacion' a 'Listo para entregar'
    public function prepararPedido(Request $request, Response $response, $args)
    {
        $parsedBody = $request->getParsedBody();
        
        if(!(empty($parsedBody["codigo"])))
        {
            //Obtengo codigo del pedido
            $codigoPedido = $parsedBody["codigo"];

            //Valido que el pedido exista
            $validacionPedido = PedidoController::validarPedido($codigoPedido);

            if($validacionPedido == true)
            {
                //Obtengo el id del empleado del token
                $header = getallheaders();
                $token = JWT::decode($header['token'], "tp_programacion3", array('HS256'));
                $id_empleado = $token->id;
            
                //Valido que el pedido le corresponda a ese empleado
                $validacionPedidoEmpleado = PedidoController::validarPedidoEmpleado($id_empleado, $codigoPedido);

                if($validacionPedidoEmpleado == true)
                {
                    //Obtengo el pedido, esto devuelve una coleccion ojo que no puedo despues usarla como si fuera una instancia de pedido
                    $pedido = Pedido::where('codigo', "=" , $codigoPedido)->get();
                    
                    //Obtengo id del pedido
                    foreach($pedido as $value)
                    {
                        $id_pedido = $value["id"];
                        break;
                    }
                    
                    //La tengo que agarrar con find para poder modificarle una atributo y despues guardar la version nueva en la tabla
                    $pedido = Pedido::find($id_pedido);
                                        
                    $pedido->estado = "Listo para entregar";
                    $pedido->save();
                                    
                    $response->getBody()->write(json_encode("Pedido preparado con exito"));
                }
                else
                {
                    $response->getBody()->write(json_encode("Error: la preparacion de este pedido esta a cargo de otro empleado"));
                }
            }
            else
            {
                $response->getBody()->write(json_encode("Error: No existe un pedido con ese codigo"));
            }        
        }
        else
        {
            $response->getBody()->write(json_encode("Para preparar un pedido tiene que enviar el codigo del mismo por body"));
        }
    
        return $response;
    }

    //Validar que la preparacion de un pedido especifico le corresponde a un empleado en particular
    public static function validarPedidoEmpleado($idEmpleado, $codigoPedido)
    {
        //validacion incorrecta
        $retorno = false;

        $pedidos = Pedido::get();

        foreach($pedidos as $pedido)
        {
            if($idEmpleado == $pedido->id_empleado && $codigoPedido == $pedido->codigo)
            {
                //Validacion correcta
                $retorno = true;
                break;
            }
        }

        return $retorno;
    }

    //Validar la existencia de un pedido por su codigo
    public static function validarPedido($codigo)
    {
        //No existe un pedido con ese codigo
        $retorno = false;
    
        $pedidos = Pedido::get();
    
        foreach($pedidos as $pedido)
        {
            if($codigo == $pedido->codigo)
            {
                //Pedido existe
                $retorno = true;
                break;
            }
        }
    
        return $retorno;
    }

    //(POST)Cambiar el estado de un pedido de 'En preparacion' a 'Listo para entregar'
    public static function retirarPedido(Request $request, Response $response, $args)
    {
        $parsedBody = $request->getParsedBody();
        
        if(!(empty($parsedBody["codigo"])))
        {
            //Obtengo codigo del pedido
            $codigoPedido = $parsedBody["codigo"];

            //Valido que el pedido exista
            $validacionPedido = PedidoController::validarPedido($codigoPedido);

            if($validacionPedido == true)
            {
                //Obtengo el id del cliente del token
                $header = getallheaders();
                $token = JWT::decode($header['token'], "tp_programacion3", array('HS256'));
                $id_cliente = $token->id;
            
                //Valido que el pedido le corresponda a ese cliente
                $validacionPedidoCliente = PedidoController::validarPedidoCliente($id_cliente, $codigoPedido);

                if($validacionPedidoCliente == true)
                {
                    //Obtengo el pedido, esto devuelve una coleccion ojo que no puedo despues usarla como si fuera una instancia de pedido
                    $pedido = Pedido::where('codigo', "=" , $codigoPedido)->get();
                    
                    //Obtengo id del pedido
                    foreach($pedido as $value)
                    {
                        $id_pedido = $value["id"];
                        break;
                    }
                    
                    //La tengo que agarrar con find para poder modificarle una atributo y despues guardar la version nueva en la tabla
                    $pedido = Pedido::find($id_pedido);

                    //Evaluo en que estado esta el pedido y segun eso cambia la respuesta
                    $estadoPedido = $pedido->estado;
                    $respuesta = "";

                    switch($estadoPedido) 
                    {
                        case 'En preparacion':
                            {
                                $respuesta = "El pedido aun no esta listo para retirar";
                            }
                            break;

                        case 'Pedido entregado':
                            {
                                $respuesta = "El pedido ya fue retirado con anterioridad";
                            }
                            break;

                        case 'Listo para entregar':
                            {
                                //Entrego el pedido
                                $pedido->estado = "Pedido entregado";
                                $pedido->save();

                                $respuesta = "Pedido retirado con exito! Gracias por elegirnos";
                            }
                            break;
                    }

                    $response->getBody()->write(json_encode($respuesta));

                    //Le muestro el pedido al cliente
                    $response->getBody()->write(json_encode($pedido));                                     
                }
                else
                {
                    $response->getBody()->write(json_encode("Error: el encargo de este pedido esta a nombre de otro cliente"));
                }
            }
            else
            {
                $response->getBody()->write(json_encode("Error: No existe un pedido con ese codigo"));
            }        
        }
        else
        {
            $response->getBody()->write(json_encode("Para retirar un pedido tiene que enviar el codigo del mismo por body"));
        }
    
        return $response;
    }

    //Validar que el encargo de un pedido especifico le corresponde a un cliente en particular
    public static function validarPedidoCliente($idCliente, $codigoPedido)
    {
        //validacion incorrecta
        $retorno = false;

        $pedidos = Pedido::get();

        foreach($pedidos as $pedido)
        {
            if($idCliente == $pedido->id_cliente && $codigoPedido == $pedido->codigo)
            {
                //Validacion correcta
                $retorno = true;
                break;
            }
        }

        return $retorno;
    }
}
