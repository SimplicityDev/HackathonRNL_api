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
 public $USER_ID = null;
     function __construct()
     {
        $this->DBM = new DatabaseManager();
         $this->RESPONSE = new stdClass();
           $this->USER = new stdClass();
         $this->DATA = json_decode(file_get_contents('php://input'));
        $this->DATA = empty($this->DATA)  ? new stdClass() : $this->DATA;

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
            case Index::$base_folder.'/request/view':
                $this->isAuthenticated();
                $this->RESPONSE = $this->requestData();
                break;
            case Index::$base_folder.'/request/new-comment':
                $this->isAuthenticated();
                $this->RESPONSE = $this->newComment();
                break;
        }
    }
    // werkt
    private function auth(){

        $this->DBM->query("SELECT * FROM Person WHERE Name = :u_name AND Password = :password");

        $this->DBM->bind(':u_name', $this->DATA->username);
        $this->DBM->bind(':password', $this->DATA->password);

        $result = $this->DBM->resultset();

        $response = new stdClass();
        
        if ($result != null && $result > 0) {
            

            $user = new stdClass();
            $user->id = $result[0]->Id;

            $response->success = true;
            $response->token = JWT::encode($user, Index::$key);
            $response->user_id = $result[0]->Id;

            return $response;
        }else{
            $response->success = false;
            $response->message = "Invalid username or password";
            echo json_encode($response);
            die;
        }
    }
    // werkt
    private function allRequests() {

        $this->DBM->query('SELECT * FROM request LEFT JOIN Person ON Person.Id = Request.Person_Id WHERE request.Person_Id = :p_id');

        $this->DBM->bind(':p_id', $this->USER_ID);
        $result = $this->DBM->resultset();
        $response = new stdClass();

        if ($result != null && $result > 0) {

            $response->success = true;
            $response->token = JWT::encode($result, Index::$key);
            $response->result = $result;

            return $response;
        }else{
            $response->success = false;
            $response->message = "Unable to find requests";

            echo json_encode($response);
            die;
        }
    }
    // test
    private function newComment() {
        $this->DBM->query('
            INSERT INTO Comments(Type, Desc, Person_id, Steps_id)  VALUES(:c_type, :c_desc, :p_id, s_id)');

        var_dump($this->DATA);
        var_dump($this->USER_ID);

        $this->DBM->bind(':p_id', $this->USER_ID);
        $this->DBM->bind(':c_type', $this->DATA->c_type);
        $this->DBM->bind(':c_desc', $this->DATA->c_desc);
        $this->DBM->bind(':s_id', $this->DATA->s_id);

        $this->DBM->execute();
    }

    private function commentDataFromStep() {
        $this->DBM->query('
            SELECT * FROM comments 
            LEFT JOIN Steps ON Steps_Id = Steps.Id
            LEFT JOIN Persons ON Comments.Person_Id = Person.Id
            WHERE Steps_Id = Steps.Id');

        $this->DBM->resultset();

        if ($result < 0) {
            $response->c_title = $result['comments.Title'];
            $response->c_desc = $result['comments.Desc'];
            $response->c_commenter = $result['Person.Name'];

        }
    }

    private function requestData() {

        $this->DBM->query('
            SELECT * FROM request_has_steps
            LEFT JOIN steps ON request_has_steps.StepsId = steps.Id
            LEFT JOIN steps_has_organization ON steps.Id = steps_has_organization.stepId
            LEFT JOIN organization ON steps_has_organization.organizationId = organization.Id
            LEFT JOIN comments ON steps.Id = comments.stepId
            WHERE request.Id = :r_id
            ');
        var_dump($this->DATA);

        $this->DBM->bind(':r_id',$this->DATA->r_id);

        $result =  $this->DBM->resultset();
        $response = new stdClass();

        if ($result != null && $result > 0) {
            $response = $result;
            $response->success = true;
            return $response;
        } else {
             $response->success = false;
             $response->message = "error fetching requestdata";
             echo json_encode($response);
             die;
        }
    }

    private function solutionFromStep() {
        $this->DBM->query('
            SELECT * FROM Solution 
            LEFT JOIN Solutions_has_Steps ON Steps_Id = Steps.Id
            lEFT JOIN Solutions_has_Steps ON Solutions_Id = Solutions.Id 
            WHERE Steps.Id = :s_id');

        $result = $DBM->resultset()[0];

        if ($result > 0) {
            $response = $result;
            $response->success = true;
            return $response;
        }else{
            $response->success = false;
            $response->message = "Error fetching solutions for this particular step";
        }
    }


    private function organizationOverview() {

    }

    private function addPersonToOrganization() {

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

        $this->USER_ID = $decoded->id;
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


?>
