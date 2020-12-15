<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Config\Database;

//Controladores de clases
use App\Controllers\ClienteController;
use App\Controllers\EmpleadoController;
use App\Controllers\PedidoController;

//Middlewares
use App\Middlewares\AuthMiddleware;
use App\Middlewares\JsonMiddleware;
use App\Middlewares\ValidarClienteMiddleware;
use App\Middlewares\ValidarEmpleadoMiddleware;

require __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();

//Instancio un objeto Config/database llamando a su constructor.
//En este caso nos conectamos a la base de datos : baseparcial
new Database;

//Pagina a mostrar en el inicio
$app->get('[/]', ClienteController::class . ':mostrarInicio')->add(new JsonMiddleware);

/* 
 -------------
 RUTAS CLIENTE
 -------------
*/

//Ver todos los clientes
$app->get('/clientes[/]', ClienteController::class . ':getAll')->add(new JsonMiddleware);

//Ver un cliente especifico
$app->get('/cliente/{id}', ClienteController::class . ':getOne')->add(new JsonMiddleware);

//Dar de alta un cliente
$app->post('/cliente[/]', ClienteController::class . ':registro')->add(new JsonMiddleware);

//Logearse como cliente
$app->post('/clienteLogin[/]', ClienteController::class . ':login')->add(new JsonMiddleware);

/* 
 --------------
 RUTAS EMPLEADO
 --------------
*/

//Ver todos los empleados
$app->get('/empleados[/]', EmpleadoController::class . ':getAll')->add(new JsonMiddleware);

//Ver un empleado especifico
$app->get('/empleado/{id}', EmpleadoController::class . ':getOne')->add(new JsonMiddleware);

//Dar de alta un empleado
$app->post('/empleado[/]', EmpleadoController::class . ':registro')->add(new JsonMiddleware);

//Logearse como empleado
$app->post('/empleadoLogin[/]', EmpleadoController::class . ':login')->add(new JsonMiddleware);

/* 
 ------------
 RUTAS PEDIDO
 ------------
*/

//Ver el menu
$app->get('/menu[/]', PedidoController::class . ':ObtenerMenu')->add(new JsonMiddleware);

//Ver todos los pedidos (solo empleado)
$app->get('/pedidos[/]', PedidoController::class . ':funcionJoin')->add(new ValidarEmpleadoMiddleware)->add(new JsonMiddleware);

//Encargar un pedido (solo cliente)
$app->post('/pedido[/]', PedidoController::class . ':registro')->add(new ValidarClienteMiddleware)->add(new JsonMiddleware);

//Prepara un pedido (solo empleado)
$app->post('/prepararPedido[/]', PedidoController::class . ':prepararPedido')->add(new ValidarEmpleadoMiddleware)->add(new JsonMiddleware);

//Retirar un pedido (solo cliente)
$app->post('/retirarPedido[/]', PedidoController::class . ':retirarPedido')->add(new ValidarClienteMiddleware)->add(new JsonMiddleware);

$app->run();