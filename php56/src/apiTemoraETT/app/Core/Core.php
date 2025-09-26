<?php
namespace App\Core;

use App\Core\Router;

class Core {
    public function __construct() {
        $this->router = new Router();
        $this->init();
    }

    public function init() {
        $this->router->group('/usuarios', function($route) {
            $route->post('/cadastro', 'App\Controllers\UserController@register', [
                'auth' => true,
                'groups' => ['admin']
            ]);
        });

        $this->router->group('/auth', function($route) {
            $route->post('/login', 'App\Controllers\AuthController@login', []);
        });

        $this->router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
    }
}
?>