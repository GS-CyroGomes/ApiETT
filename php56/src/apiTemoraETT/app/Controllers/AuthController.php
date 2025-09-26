<?php
namespace App\Controllers;

use App\Helpers\Helper;
use App\Models\User;

class AuthController
{
    public $User;
    public function __construct() 
    {
        $this->User = new User();
    }
    
    public function login($arguments)
    {
        Helper::validateRequiredFields($arguments, ['cpf', 'senha']);
        $id = $this->User->getIdPersonByCpf($arguments['cpf']);
        $userValid = $this->User->checkUserPassword($id, $arguments['senha']);

        if ($userValid) {
            $user = $this->User->getPersonById($id);
            $user['group'] = $this->User->getUserGroup($id);
            $jwe = Helper::jweEncripty($user);
            Helper::finalizarRequisicao(["token" => $jwe]);
        }
        Helper::finalizarRequisicao(["message" => "Usuário ou senha inválidos"], "401 Unauthorized");
    }

    public function validateToken($arguments)
    {
        Helper::finalizarRequisicao("Token válido");
    }
}