<?php
namespace App\Core;

require_once __DIR__ . '/../Helpers/Functions.php';

use App\Helpers\Helper;

class Router
{
    protected $routes = [];
    protected $currentGroup = '';

    public function add($method, $path, $action)
    {
        // junta prefixo de grupo com o caminho passado
        $fullPath = rtrim($this->currentGroup . '/' . ltrim($path, '/'), '/');
        if ($fullPath === '') {
            $fullPath = '/';
        }

        $this->routes[strtoupper($method)][$fullPath] = $action;
    }

    public function get($p, $a)  { $this->add('GET',  $p, $a); }
    public function post($p, $a) { $this->add('POST', $p, $a); }
    public function put($p, $a)  { $this->add('PUT',  $p, $a); }
    public function delete($p,$a){ $this->add('DELETE',$p,$a); }

    /** Cria grupo com prefixo: $router->group('/usuarios', function($r){ ... }); */
    public function group($prefix, \Closure $callback)
    {
        $parent = $this->currentGroup;
        $this->currentGroup = rtrim($parent . '/' . trim($prefix, '/'), '/');
        $callback($this);
        $this->currentGroup = $parent; // volta ao prefixo anterior
    }

    public function dispatch($uri, $method)
    {
        $base = '/apiTemoraETT'; // ajuste conforme sua pasta
        $path = parse_url($uri, PHP_URL_PATH);

        if (strpos($path, $base) === 0) {
            $path = substr($path, strlen($base));
        }

        $path = '/' . ltrim($path, '/');
        $method = strtoupper($method);

        if (!isset($this->routes[$method][$path])) {
            Helper::emitirErro([
                'debug' => [
                    'method' => $method,
                    'path'   => $path,
                    'routes' => array_keys($this->routes[$method]) ?: []
                ]
            ], '404 Not Found');
        }
        $json = Helper::gCleanField(json_decode(file_get_contents("php://input"), true));
        $jsonValido = Helper::verificarErroJson($json);
        
        if (array_key_exists('data', $jsonValido)) { 
            $requestBody = $jsonValido['data'];
        }

        list($class, $func) = explode('@', $this->routes[$method][$path]);
        $controller = new $class();

        $qtdParametros = (int)count(Helper::listParametersClassFunction($class, $func));

        switch ($qtdParametros) {
            case 0:
                $class->{$func}();
                break;
            case 1:
                call_user_func([$controller, $func], $requestBody);
                break;
            default:
                call_user_func_array([$controller, $func], array_values($requestBody));
                break;
        }
    }
}

?>