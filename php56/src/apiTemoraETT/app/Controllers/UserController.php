<?php
namespace App\Controllers;

require_once __DIR__ . '/../Helpers/Functions.php';

use Config\Database;
use App\Helpers\Helper;

class UserController
{
    public function register($arguments)
    {
        $requiredArguments = [
            'nome', 'data_nascimento', 'rg', 'cpf', 'orgao_emissor', 'uf_emissor', 'senha'
        ];

        foreach ($requiredArguments as $value) {
            var_dump(array_key_exists($key, $arguments));
            if (!array_key_exists($key, $arguments)) {
                $this->returnError("Argumento obrigatório: {$key}", 422);
            }
            // if (!in_array($key, array_keys($requiredArguments))) {
            // }
        }
        
        // if (empty($cpf) || empty($senha)) {
        //     $this->returnError('Cpf e senha obrigatórios', 422);
        // }
    }

    private function returnError($message, $statusCode){
        Helper::emitirErro($message, $statusCode);
    }
}
