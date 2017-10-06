<?php
include_once('vendor/autoload.php');
use Firebase\JWT\JWT;
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');
if($_SERVER['REQUEST_METHOD'] == "OPTIONS") die;
class Index {
 public $RESPONSE;
 public $DATA;
 public $USER;
 static $key = "T$#%YH$^^J$*K%";
 static $base_folder = "/hackathon_api";
     function __construct()
     {
         $this->RESPONSE = new stdClass();
         $this->DATA = json_decode(file_get_contents('php://input')); #username && password
     }

    public function start(){
        switch ($_SERVER["REQUEST_URI"]) {
            case Index::$base_folder.'/auth':
                $this->RESPONSE = $this->auth();
                break;
            case Index::$base_folder.'/user/me':
                $this->isAuthenticated();
                $this->RESPONSE = $this->me();
                break;
        }
    }

    private function auth(){
        $data = array(
            "username" => $this->DATA->username,
            "password" => $this->DATA->password
        );
        $DBM->query = "SELECT * FROM users WHERE u_name = :u_name AND pw = :pw";

        $DBM->bind(':u_name', $this->DATA->username);
        $DBM->bind(':pw', $this->DATA->password);

        $response = new stdClass();

        $response->success = true;
        $response->token = JWT::encode($data, Index::$key);
        $response->u_name = $data["user_id"];
        return $response;
    }

    private function me(){
        return "blaat";
    }

    private function isAuthenticated(){
     $response = new stdClass();
     if(isset($_SERVER['HTTP_AUTH'])){
         try{
             $decoded = JWT::decode($_SERVER['HTTP_AUTH'], Index::$key, array('HS256'));
         }catch(Exception $exception){
             $response->success = false;
             $response->message = "invalid token";
         }



         if (empty($decoded)) {
             $response->success = false;
             $response->message = "invalid token";
             echo json_encode($response);
             die;
         }
     }else{
         $response->success = false;
         $response->message = "token not found";
         echo json_encode($response);
         die();
     }

    }
}

$index = new Index();
$index->start();
echo json_encode($index->RESPONSE);


