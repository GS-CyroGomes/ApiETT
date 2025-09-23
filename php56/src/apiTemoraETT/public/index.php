<?php
    require __DIR__ . '/../vendor/autoload.php';
    require_once __DIR__ . '/../app/Helpers/Functions.php';
    
    use Config\Database;
    use App\Helpers\Helper;

    class TemoraApi {
        private $conn;
        public $bodyRequest;

        public function __construct() {
            $this->connection();
            
            // list($rota, $parametros) = $this->bindFunctionArgs($_GET['url']);
            // $json = json_decode(file_get_contents("php://input"), true);
            // $json = array_map('gCleanField', $json);
            // if (!verificarErroJson($json)) { $this->bodyRequest = $json; }
            // $this->callRoute($rota, $parametros);
        }

        public function connection(){
            try{
                $db = new Database();
                $this->conn = $db->getConnection();
            }catch(Exception $e){
                Helper::emitirErro($e->getMessage());
            }
        }
    }

$api = new TemoraApi();

?>