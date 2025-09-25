<?php
    date_default_timezone_set('America/Sao_Paulo');
    require __DIR__ . '/../vendor/autoload.php';

    use App\Core\Core;

    class TemoraApi {
        public $bodyRequest;

        public function __construct() {
            $this->core = new Core();
            
        }
    }

    $api = new TemoraApi();
?>