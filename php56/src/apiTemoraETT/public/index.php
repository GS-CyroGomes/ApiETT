<?php
    date_default_timezone_set('America/Sao_Paulo');
    require __DIR__ . '/../vendor/autoload.php';

    use App\Core\Router;

    class TemoraApi {
        public $bodyRequest;

        public function __construct() {
            $router = new Router();
            
            // Grupo de rotas para usuários
            $router->group('/usuarios', function($route) {
                $route->post('/cadastro', 'App\Controllers\UserController@register');
                $route->get ('/listar',   'App\Controllers\UserController@index');
                $route->put ('/editar',   'App\Controllers\UserController@update');
                $route->delete('/excluir','App\Controllers\UserController@delete');
            });

            // Você pode ter outros grupos independentes
            $router->group('/auth', function($route) {
                $route->post('/login', 'App\Controllers\AuthController@login');
                $route->post('/logout','App\Controllers\AuthController@logout');
            });

            $router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
        }
    }

$api = new TemoraApi();

?>