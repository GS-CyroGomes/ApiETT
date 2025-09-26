<?php
namespace App\Middlewares;

require_once __DIR__ . '/../Helpers/Functions.php';
use App\Helpers\Helper;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware {

    /**
     * Valida JWT e retorna payload do usuário
     *
     * @return array
     */
    public static function handle() {
        $headers = getallheaders();

        if (!isset($headers['Authorization'])) {
            Helper::emitirErro('Token de autenticação não fornecido.', "401 Unauthorized");
        }

        $authHeader = $headers['Authorization'];

        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        } else {
            Helper::emitirErro('Formato do token inválido. Use o formato Bearer.', "401 Unauthorized");
        }

        $secretKey = 'SUA_CHAVE_SECRETA_AQUI'; // coloque sua chave secreta aqui

        try {
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
            return (array) $decoded;
        } catch (\Exception $e) {
            Helper::emitirErro('Acesso negado: ' . $e->getMessage(), "403 Forbidden");
        }
    }
}
