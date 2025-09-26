<?php
namespace App\Core;

require_once __DIR__ . '/../Helpers/Functions.php';
use App\Helpers\Helper;
use App\Middlewares\AuthMiddleware;

class Router
{
    protected $routes = [];
    protected $currentGroup = '';

    /**
     * Adiciona rota ao router
     */
    public function add($method, $path, $action, $options = [])
    {
        $fullPath = rtrim($this->currentGroup . '/' . ltrim($path, '/'), '/');
        if ($fullPath === '') $fullPath = '/';

        $this->routes[strtoupper($method)][$fullPath] = [
            'action' => $action,
            'options' => $options
        ];
    }

    public function get($path, $action, $options = []) { $this->add('GET', $path, $action, $options); }
    public function post($path, $action, $options = []) { $this->add('POST', $path, $action, $options); }
    public function put($path, $action, $options = []) { $this->add('PUT', $path, $action, $options); }
    public function delete($path, $action, $options = []) { $this->add('DELETE', $path, $action, $options); }

    /**
     * Cria grupo de rotas com prefixo
     */
    public function group($prefix, \Closure $callback)
    {
        $parent = $this->currentGroup;
        $this->currentGroup = rtrim($parent . '/' . trim($prefix, '/'), '/');
        $callback($this);
        $this->currentGroup = $parent;
    }

    /**
     * Executa a rota solicitada
     */
    public function dispatch($uri, $method)
    {
        $base = '/apiTemoraETT';
        $path = parse_url($uri, PHP_URL_PATH);
        if (strpos($path, $base) === 0) $path = substr($path, strlen($base));
        $path = '/' . ltrim($path, '/');
        $method = strtoupper($method);

        if (!isset($this->routes[$method][$path])) {
            Helper::emitirErro([
                'debug' => [
                    'method' => $method,
                    'path' => $path,
                    'routes' => array_keys($this->routes[$method]) ?: []
                ]
            ], '404 Not Found');
        }

        $route = $this->routes[$method][$path];
        $options = isset($route['options']) ? $route['options'] : [];

        // --- Middleware de autenticação ---
        if (!empty($options['auth'])) {
            $user = AuthMiddleware::handle();

            // Verifica grupo
            if (!empty($options['groups'])) {
                $userGroup = isset($user['group']) ? $user['group'] : null;
                if (!in_array($userGroup, $options['groups'])) {
                    Helper::finalizarRequisicao(['message' => 'Acesso negado'], '403 Forbidden');
                }
            }

            $_REQUEST['auth_user'] = $user;
        }

        // Processa corpo da requisição
        $json = Helper::gCleanField(json_decode(file_get_contents("php://input"), true));
        $requestBody = [];
        $jsonValido  = Helper::verificarErroJson($json);
        if (array_key_exists('data', $jsonValido)) $requestBody = $jsonValido['data'];

        list($class, $func) = explode('@', $route['action']);
        $controller = new $class();

        return $controller->$func($requestBody);
    }
}
