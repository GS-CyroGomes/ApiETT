<?
namespace App\Models;
use Config\Database;

class User
{
    private $db;
    public function __construct() {
        // $this->db = Database::connect();
    }

    public function cadastrar($arguments){
        var_dump($arguments);
    }
}
?>