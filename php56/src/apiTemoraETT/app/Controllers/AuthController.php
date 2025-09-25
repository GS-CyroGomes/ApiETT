<?php
namespace App\Controllers;

use App\Helpers\Helper;
use App\Models\Person;
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
        $this->User->getIdPersonByCpf($arguments['cpf']); 

        var_dump($arguments);
        exit;
    }
}