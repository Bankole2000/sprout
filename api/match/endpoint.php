<?php 
// Required Headers
header("Access-Control-Allow-Origin: http://localhost/rest-api-authentication-example/");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// database connection will be here
// files needed to connect to database
include_once '../config/database.php';
include_once '../models/user.php';
 
// get database connection
$database = new Database();
$db = $database->getConnection();

function create_user (){
  $user = new User($db);

  // Get Form Data
  $data = json_decode(file_get_contents("php://input"));
  
  // Set User Object Data
  $user->firstname = $data->firstname;
  $user->lastname = $data->lastname;
  $user->email = $data->email;
  $user->password = $data->password;
  $user->gender = $data->gender;

  // Check if Email Exists
  $email_exists = $user->emailExists();

}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
  case 'GET': {
    http_response_code(200);
    echo json_encode(array(
      "message" => "You Sent $method Request",
      "result" => "success"
    ));
    }
    break;
  case 'POST':  {
    http_response_code(200);
    echo json_encode(array(
      "message" => "You Sent $method Request",
      "result" => "success"
    )); 
    }
    break;
  case 'DELETE': {
    http_response_code(200);
    echo json_encode(array(
      "message" => "You Sent $method Request",
      "result" => "success"
    ));
    }
    break;
  case 'PUT': {
    http_response_code(200);
    echo json_encode(array(
      "message" => "You Sent $method Request",
      "result" => "success"
    )); 
    }    
    break;
}
?>