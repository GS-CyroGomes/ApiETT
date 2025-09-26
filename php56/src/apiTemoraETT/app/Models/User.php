<?
namespace App\Models;
use Config\Database;
use App\Models\Person;
use App\Helpers\Helper;

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
        Helper::emitirErro("Usuário já cadastrado", "409 Conflict");
    }

    public function checkUserPassword($id, $password)
    {
        $result = $this->db->selectWithDecrypted(["usuario"], "password", "id_pessoa = :id_person", ["id_person" => $id]);
        return isset($result["password"]) ? true : false;
    }

    public function getUserGroup($id)
    {
        $result = $this->db->select(["usuario" => "u"], "u.user_group", "u.id = :id_user", ["id_user" => $id]);
        return isset($result["user_group"]) ? $result["user_group"] : null;
    }
}
?>