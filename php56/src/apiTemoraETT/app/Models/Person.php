<?php
namespace App\Models;
use Config\Database;


class Person
{
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }

    public function registerPerson(
        $nome, 
        $data_nascimento, 
        $rg, 
        $cpf, 
        $orgao_emissor, 
        $uf_emissor
    ) {
        if (!is_null($this->issetCpf($cpf))) {
            return false;
        }
        return $this->db->insert(
            "pessoa", 
            [
                "nome" => $nome, 
                "data_nascimento" => $data_nascimento, 
                "rg" => $rg, 
                "cpf" => $cpf, 
                "orgao_emissor" => $orgao_emissor, 
                "uf_emissor" => $uf_emissor
            ]
        );
    }

    protected function issetCpf($cpf) 
    {
        $result = $this->db->select(
            ["pessoa" => "p"], 
            ["p.id"], 
            "p.cpf = :cpf", 
            ["cpf" => $cpf]
        );
        return ($result) ? $result["id"] : null;
    }

    public function getIdPersonByCpf($cpf) 
    {
        $id = $this->issetCpf($cpf);
        if (!is_null($id)) {
            $rs = $this->db->select(["usuario" => "u"], "*", "u.id_pessoa = :id_person", ["id_person" => $id]);
            var_dump($rs);
            exit;
        }
    }
}
?>