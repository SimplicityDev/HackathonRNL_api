<?php
include_once('vendor/autoload.php');
include_once('vendor/DatabaseManager.php');
require_once 'Modules/config.php';
use Firebase\JWT\JWT;
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');
if($_SERVER['REQUEST_METHOD'] == "OPTIONS") die;
class Index {
 public $RESPONSE;
 public $DATA;
 public $USER;
 public $DBM;
 static $key = "T$#%YH$^^J$*K%";
 static $base_folder = "/redesign-Netherlands";
     function __construct()
     {
        $this->DBM = new DatabaseManager();
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
            case Index::$base_folder.'/request/all':
                $this->isAuthenticated();
                $this->RESPONSE = $this->allRequests();
                break;
        }
    }

    private function auth(){
        $data = array(
            "username" => $this->DATA->username,
            "password" => $this->DATA->password
        );
        $this->DBM->query("SELECT * FROM person WHERE Username = :u_name AND Password == :password");

        $this->DBM->bind(':u_name', $data['username']);
        $this->DBM->bind(':password', $data['password']);

        $result = $this->DBM->resultset()[0];
        if ($result > 0) {
            $response = new stdClass();

            $response->success = true;
            $response->token = JWT::encode($result, Index::$key);
            $response->u_name = $result["Id"];
            $response->u_pass = $result['Password'];
            $response->u_name = $result['Name'];
            $response->u_sofi = $result['Sofi'];
            $response->u_email = $result['Email'];
            $response->u_adress = $result['Adress'];
            $response->u_organisation = $result['Organisation'];
            $response->u_postalcode = $result['Postalcode'];
            $response->u_logintoken = $result['Logintoken'];

            return $response;
        }else{
            $response->success = false;
            $response->message = "Invalid username or password";
            echo json_encode($response);
            die;
        }
    }

    private function allRequests() {

        $this->DBM->query('SELECT * FROM request');
        $result = $this->DBM->resultset()[0];

        if ($result > 0) {
            $response = new stdClass();

            $response->success = true;
            $response->token = JWT::encode($result, Index::$key);

            $response->r_id = $result['Id'];
            $response->r_title = $result['Title'];
            $response->r_desc = $result["Desc"];

            return $response;
        }else{
            $response->success = false;
            $response->message = "Unable to find requests";

            echo json_encode($response);
            die;
        }
    }

    private function createRequest() {
        $data = array(
            'r_title' => $this->DATA->request_title,
            'r_Desc' => $this->DATA->request_desc,
            's_title' => $this->DATA->step_title,
            's_desc' => $this->DATA->step_desc,
            's_time' => $this->DATA->step_time);

        $this->DBM->

    }

    private function insertComment() {
        $this->DBM->query('INSERT INTO Comments VALUES(Type, Desc, Person_id');
    }

    private function requestData() {
        $data = array('r_id' = $this->DATA->requestId);

        $this->DBM->query('
            SELECT 
            s.*, 
            r.*,
            o.*,
            p.Name
            FROM 
            steps s, 
            request r, 
            request_has_steps rhs,  
            steps_has_comments shc, 
            comments c, 
            steps_has_organisation sho,
            organisation o,
            person p
            WHERE
            rhs.request_Id = r_id,
            shc.steps_Id = s.Id,
            sho.Organisation_Id = o.Id
            ');

        $this->DBM->bind(':r_id',$data['r_id']);

        $result =  $this->DBM->resultset();

        if ($result > 0) {
            $response = new stdClass();
            // request data
            $response->request_id = $result['r.Id'];
            $response->request_title = $result['r.title'];
            $response->request_desc = $result['r.desc'];
            // step data
            $response->step_id = $result['s.Id'];
            $response->step_title = $result['s.Title'];
            $response->step_desc = $result['s.Desc'];
            $response->step_processtime = $result['s.Time'];
            // comment data
            $response->comment_Id = $result['c.Id'];
            $response->comment_Type = $result['c.Type'];
            $response->comment_Desc = $result['c.Desc'];
            $response->comment_commenter =  $result['p.Name'];


        }
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


