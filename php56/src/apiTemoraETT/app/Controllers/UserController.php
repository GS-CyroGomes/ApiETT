<?php
namespace App\Controllers;

require_once __DIR__ . '/../Helpers/Functions.php';

use App\Helpers\Helper;
use App\Models\User;
use App\Models\Person;

class UserController
{
    private $User;

    public function __construct() 
    {
        $this->User = new User();
    }
    
    public function register($arguments)
    {
        Helper::validateRequiredFields($arguments, ['nome', 'dataNascimento', 'rg', 'cpf', 'orgaoEmissor', 'ufEmissor', 'senha']);
        $arguments['password'] = $arguments['senha'];
        unset($arguments['senha']);
        call_user_func_array([$this->User, 'registerUser'], $arguments);
        // $jwe = Helper::jweEncripty($arguments);
        // $arguments = Helper::jweDecripty($jwe);
        // Helper::finalizarRequisicao($jwe);

        // Helper::finalizarRequisicao($arguments);
    }
}
