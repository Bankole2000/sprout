<?php 
// NOTE: Need to Add JWT Authentication in PRIVATE: OWNER: and ADMIN: routes
// Required Headers
header("Access-Control-Allow-Origin: http://localhost/rest-api-authentication-example/");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// files needed to connect to database
include_once '../config/Database.php';
include_once '../models/User.php';

// files for jwt
include_once '../config/core.php';
include_once '../../vendor/firebase/php-jwt/src/BeforeValidException.php';
include_once '../../vendor/firebase/php-jwt/src/ExpiredException.php';
include_once '../../vendor/firebase/php-jwt/src/SignatureInvalidException.php';
include_once '../../vendor/firebase/php-jwt/src/JWT.php';
use \Firebase\JWT\JWT;
 
// get database connection



function create_user ($user){

  $data = json_decode(file_get_contents("php://input"));
  $user->firstname = $data->firstname;
  $user->lastname = $data->lastname;
  $user->email = $data->email;
  $user->password = $data->password;
  $user->gender = $data->gender;
  // Create token and append
    $str="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
    $token = substr(str_shuffle($str), 0, 20);
    $user->token=$token;
    
    // Register User
    if($user->create()){
      http_response_code(200);
      echo json_encode(array(
        "message"=>"User Registered",
        "result"=>"success",
        "email"=> $user->email,
        "token"=> $user->token,
        "emailExists" => $user->emailExists(),
        "nextAction"=> "verifyEmail"
      ));
    }
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
  case 'GET': 
    if(isset($_GET['action']))
    {
      if($_GET['action'] == "getOne") // PRIVATE: Logged in User get Other User CONSTRAINT: user_id
      {        
        $database = new Database();
        $db = $database->getConnection();
        $user = new User($db);
        $user->user_id=$_GET['user_id'];
        if($user->get_single()){
          http_response_code(200);
          echo json_encode(array(
            "message" => "Fetched User details",
            "result" => "success",
            "user_data" => $user
          ));
        } else {
          http_response_code(404);
          echo json_encode(array(
          "message" => "No User with that ID",
          "result" => "fail",
          "user_id" => $user->user_id
          ));
        }
        
      }
      if($_GET['action'] == "getAll") // ADMIN: Get all Users 
      {
        $database = new Database();
        $db = $database->getConnection();
        $user = new User($db);
        $result = $user->get_all();
        // Get row count
        $num = $result->rowCount();
        if($num > 0){
          // users array 
          $users_arr = array();
          $users_arr['data'] = array();
          $users_arr['message'] = "Retrieved All users";
          $users_arr['result'] = "success";
          while($row = $result->fetch(PDO::FETCH_ASSOC)){
            extract($row);
            $single_user = array(
              "user_id" => $user_id,
              "firstname" => $firstname,
              "lastname" => $lastname,
              "gender" => $gender,
              "email" => $email,
            );
            // Push to "users" array
            array_push($users_arr['data'], $single_user);
          };
          http_response_code(200);
          echo json_encode($users_arr);
        } else {
          http_response_code(404);
          echo json_encode(array(
          "message" => "No Users In the Database",
          "result" => "fail",
          "sql"=> var_dump($user->get_all())
          ));
        }
        
      }
      if($_GET['action'] == "getDetails") // ADMIN: Logged in User CONSTRAINT: user_id
      {
        http_response_code(200);
        echo json_encode(array(
          "message" => "Here's All Details of Single user with Id",
          "result" => "success"
        ));
      }  
    }
    break;
  case 'POST': 
    // Get POST request Info
    $data = json_decode(file_get_contents("php://input"));
    // Signup Request Handler
    if($data->action == "register") // PUBLIC: Register New User
    {
      require_once('../config/connect.php');
      $email = $data->email;
      $sql= "SELECT * FROM users WHERE email='$email' LIMIT 1";
      $result = $db2->query($sql);
      if($result->num_rows === 1 ){
        // create_user($user);
        http_response_code(400);
        echo json_encode(array(
        "message" => "Email Already Registered",
        "result" => "fail",
        "email" => $email,
        "sqlresult"=> var_dump($result)
        ));
      }else{
        $database = new Database();
        $db = $database->getConnection();
        $user = new User($db);
        create_user($user);
      }
    }

    // Login Request Handler
    if($data->action == "login") // PUBLIC: User Login
    {
      $database = new Database();
      $db = $database->getConnection();
      $user = new User($db);
      $user->email = $data->email;
      $email_exists = $user->emailExists();
      if($email_exists && password_verify($data->password, $user->password)){
        // Check if Email is Verified
        if($user->is_email_verified == 0){
          http_response_code(401);
          echo json_encode(array(
            "message"=>"Please Verify Email",
            "result"=>"fail",
            "email_verified"=>$user->is_email_verified
          ));
        }else if($user->is_email_verified == 1){
          $token = array(
            "iss" => $iss,
            "aud" => $aud,
            "iat" => $iat,
            "nbf" => $nbf,
            "data" => array(
                "userId" => $user->user_id,
                "firstname" => $user->firstname,
                "lastname" => $user->lastname,
                "email" => $user->email
            )
         );       
         // set response code
         http_response_code(200);  
         // generate jwt
         $jwt = JWT::encode($token, $key);
         echo json_encode(
                 array(
                     "message" => "Successful login.",
                     "jwt" => $jwt
                 )
             );
        }
      }else{
        // set response code
        http_response_code(401);
        // tell the user login failed
        echo json_encode(array(
          "message" => "Login failed.",
          "emailExists" => $email_exists ));
      }
    }
    break;
  case 'DELETE': 
    // Get POST request info
    $data = json_decode(file_get_contents("php://input"));
    // Delete single User
    if($data->action == "deleteUser") // PRIVATE: OWNER: Logged in User Delete Self CONSTRAINT: user_id
    {
      http_response_code(200);
      echo json_encode(array(
      "message" => "Deleted user with id $data->user_id ",
      "result" => "success"
      ));
    }
    if($data->action == "admindeleteUser") // ADMIN: Delete User From Admin CONSTRAINT: user_id
    {
      http_response_code(200);
      echo json_encode(array(
      "message" => "Admin Deleted user with id $data->user_id",
      "result" => "success"
      ));
    }
    break;
  case 'PUT': 
    // Get POSTED DATA
    $data = json_decode(file_get_contents("php://input"));
    // Edit Basic User Data
    if($data->action == "editData") // PRIVATE: OWNER: Logged in user edit data CONSTRAINT: user_id
    {
      http_response_code(200);
      echo json_encode(array(
      "message" => "Updated data of user with id $data->user_id ",
      "result" => "success"
      )); 
    }

    if($data->action == "updatePassword") // PRIVATE: OWNER: Logged in user change Password CONSTRAINT: user_id
    {
      http_response_code(200);
      echo json_encode(array(
      "message" => "Password changed for user with id $data->user_id",
      "result" => "success"
      )); 
    }
    if($data->action == "resetPassword")
    {
      http_response_code(200);
      echo json_encode(array(
      "message" => "Password reset for user with id $data->user_id",
      "result" => "success"
      )); 
    }
    break;
    default: {
      http_response_code(400);
      echo json_encode(array(
      "message" => "Bad Request - Please check request Method and input parameters",
      "result" => "fail"
      )); 
    };
}
?>