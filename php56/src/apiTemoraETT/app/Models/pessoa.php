<?php
    class Pessoa {
        private $db;

        public function __construct($db) {
            $this->db = $db;
        }

        public function cadastrar(
            string $nome, 
            string $data_nascimento, 
            string $rg, 
            string $cpf, 
            string $orgao_emissor, 
            string $uf_emissor
        ) {
            if (is_null($this->issetCpf($cpf))) {
                return $this->db->insert("pessoa", get_defined_vars());
            } else {
                emitirErro("Não podem existir mais de uma pessoa com o mesmo cpf: {$cpf}");
            }
        }

        protected function issetCpf($cpf) {
            $rs = $this->db->select("id", "pessoa", "cpf = '{$cpf}'");
            return (is_null($rs)) ? null : $rs[0]->id;
        }

        protected function getIdPersonByCpf($cpf) {
            $id = $this->issetCpf($cpf);
            if (!is_null($id)) {
                return $this->db->select("id, cpf", "pessoa", "id = '{$id}'")[0]->id;
            }
        }
    }

    class Usuario extends Pessoa {
        private $db;
    
        public function __construct($db) {
            parent::__construct($db);
            $this->db = $db;
        }

        public function cadastrar(
            string $nome, 
            string $dataNascimento, 
            string $rg, 
            string $cpf, 
            string $orgaoEmissor, 
            string $ufEmissor, 
            string $password
        ) {
            $idPessoa = parent::cadastrar($nome, $dataNascimento, $rg, $cpf, $orgaoEmissor, $ufEmissor);
            if (!is_null($idPessoa)) {
                $dataUser = [
                    "id_pessoa" => $idPessoa,
                    "password" => $password
                ];
                $idUser = $this->db->insert("usuario", $dataUser);
                $dataUser["idUser"] = $idUser;
                var_dump($dataUser);
                exit;
            }
        }

        public function login($cpf, $senha) {
            require_once "./functions.php";
            $id = parent::getIdPersonByCpf($cpf);
            $select = desencriptar("password")."as password";
            $password = $this->db->select($select, "usuario", "id_pessoa = '{$id}'")[0]->password;
            if ($password == $senha) {
                return "Logou!";
            }
        }
    }
?>