<?
namespace App\Models;
use Config\Database;
use App\Models\Person;

class User extends Person
{
    private $db;

    public function __construct() 
    {
        parent::__construct();
        $this->db = new Database();
    }

    public function registerUser(
        $nome, 
        $dataNascimento, 
        $rg, 
        $cpf, 
        $orgaoEmissor, 
        $ufEmissor, 
        $password
    ){
        $idPessoa = parent::registerPerson($nome, $dataNascimento, $rg, $cpf, $orgaoEmissor, $ufEmissor);
        if ($idPessoa) {
            return $this->db->insertWithEncrypted(
                "usuario", 
                [
                    "id_pessoa" => $idPessoa, 
                    "password" => $password,
                ]
            );
        }
        throw new \Exception("Erro ao cadastrar usuário");
    }

    public function checkUserPassword($id, $password)
    {
        $result = $this->db->selectWithDecrypted(["usuario"], "password", "id_pessoa = :id_person", ["id_person" => $id]);
        return isset($result["password"]) ? true : false;
    }
}
?>