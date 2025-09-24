<?php
namespace App\Controllers;

require_once __DIR__ . '/../Helpers/Functions.php';

class PersonController
{
    public $Person;
    public function __construct() {
        $this->Person = new Person();
    }

    public function index()
    {
        echo "PersonController";
    }
    
    public function register($arguments)
    {
        Helper::validateRequiredFields($arguments, ['nome', 'data_nascimento', 'rg', 'cpf', 'orgao_emissor', 'uf_emissor', 'senha']);        
        $this->Person->cadastrar($arguments);
        // Helper::finalizarRequisicao($arguments);
    }
}

?>