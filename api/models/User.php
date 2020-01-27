<?php
// 'user' object
class User{
 
    // database connection and table name
    private $conn;
    private $table_name = "users";
 
    // object properties
    public $user_id;
    public $firstname;
    public $lastname;
    public $email;
    public $password;
    public $token;
    public $is_email_verified;
    public $phone;
    public $gender;
    public $state_id;
    public $state_details = array();
    public $bank_id;
    public $bank_details = array();
    public $acc_number;
    public $don_balance;
    public $rec_balance;
    public $total_don;
    public $total_rec;
    public $eligibility;
    public $strikes;
    public $strike_details = array();
    public $don_url;
    public $rec_url;
    public $match_url;
    public $signup_date;


 
    // constructor
    public function __construct($db){
        $this->conn = $db;
    }

// create new user record
function create(){     
  // insert query    
  $query = "INSERT INTO " . $this->table_name . " SET firstname = :firstname, lastname = :lastname, email = :email, password = :password, gender = :gender, token = :token";     
  
  // prepare the query    
  $stmt = $this->conn->prepare($query);     
  
  // sanitize    
  $this->firstname=htmlspecialchars(strip_tags($this->firstname));    
  $this->lastname=htmlspecialchars(strip_tags($this->lastname));    
  $this->email=htmlspecialchars(strip_tags($this->email));    
  $this->password=htmlspecialchars(strip_tags($this->password));     
  $this->gender=htmlspecialchars(strip_tags($this->gender));   
  $this->token=htmlspecialchars($this->token);
  
  // bind the values    
  $stmt->bindParam(':firstname', $this->firstname);    
  $stmt->bindParam(':lastname', $this->lastname);    
  $stmt->bindParam(':email', $this->email);     
  $stmt->bindParam(':gender', $this->gender);     
  $stmt->bindParam(':token', $this->token);     
  
  // hash the password before saving to database    
  $password_hash = password_hash($this->password, PASSWORD_BCRYPT);    
  $stmt->bindParam(':password', $password_hash);     
  
  // execute the query, also check if query was successful    
  if($stmt->execute()){        
    return true;    
  }     
  return false;
} 

// check if given email exist in the database
public function emailExists(){

  // query to check if email exists
  $query = "SELECT *
          FROM " . $this->table_name . "
          WHERE email = :email
          LIMIT 0,1";

  // prepare the query
  $stmt = $this->conn->prepare( $query );

  // sanitize
  $this->email=htmlspecialchars(strip_tags($this->email));

  // bind given email value
  $stmt->bindParam(':email', $this->email);

  // execute the query
  $stmt->execute();

  // get number of rows
  $num = $stmt->rowCount();

  // if email exists, assign values to object properties for easy access and use for php sessions
  if($num>0){

      // get record details / values
      $row = $stmt->fetch(PDO::FETCH_ASSOC);

      // assign values to object properties
      $this->user_id = $row['user_id'];
      $this->firstname = $row['firstname'];
      $this->lastname = $row['lastname'];
      $this->password = $row['password'];
      $this->gender = $row['gender'];
      $this->is_email_verified = $row['is_email_verified'];

      // return true because email exists in the database
      return true;
  }

  // return false if email does not exist in the database
  return false;
}

// Get All Users 
public function get_all(){
  // Create Query
  $query = '
  SELECT 
    *
  FROM 
    '. $this->table_name .'
  ORDER BY 
    user_id
  ';

  // Prepared Statement 
  $stmt = $this->conn->prepare($query);

  // Execute Query
  $stmt->execute();

  return $stmt;
}

// Get Single User Data
function get_single(){
  // Create query
  $query = '
  SELECT
    *
  FROM 
    '. $this->table_name .'
  WHERE 
    user_id = :user_id
  LIMIT 0,1
  ';

  // Prepare statment
  $stmt = $this->conn->prepare($query);

  // Bind ID
  $stmt->bindParam(':user_id', $this->user_id);

  // Execute Query
  $stmt->execute();

  // get number of rows
  $num = $stmt->rowCount();

  if($num>0){

  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  // Set properties
  $this->user_id = $row['user_id'];
  $this->firstname = $row['firstname'];
  $this->lastname = $row['lastname'];
  $this->email = $row['email'];
  // $this->phone = $row['phone'];
  $this->gender = $row['gender'];
  // $this->strikes = $row['strikes'];
  // $this->signup_date = $row['signup_date'];
  
  return true;
  } else {
    return false;
  }

}

// // update a user record
// public function update(){
 
//   // if password needs to be updated
//   $password_set=!empty($this->password) ? ", password = :password" : "";

//   // if no posted password, do not update the password
//   $query = "UPDATE " . $this->table_name . "
//           SET
//               firstname = :firstname,
//               lastname = :lastname,
//               email = :email
//               {$password_set}
//           WHERE id = :id";

//   // prepare the query
//   $stmt = $this->conn->prepare($query);

//   // sanitize
//   $this->firstname=htmlspecialchars(strip_tags($this->firstname));
//   $this->lastname=htmlspecialchars(strip_tags($this->lastname));
//   $this->email=htmlspecialchars(strip_tags($this->email));

//   // bind the values from the form
//   $stmt->bindParam(':firstname', $this->firstname);
//   $stmt->bindParam(':lastname', $this->lastname);
//   $stmt->bindParam(':email', $this->email);

//   // hash the password before saving to database
//   if(!empty($this->password)){
//       $this->password=htmlspecialchars(strip_tags($this->password));
//       $password_hash = password_hash($this->password, PASSWORD_BCRYPT);
//       $stmt->bindParam(':password', $password_hash);
//   }

//   // unique ID of record to be edited
//   $stmt->bindParam(':id', $this->id);

//   // execute the query
//   if($stmt->execute()){
//       return true;
//   }

//   return false;
// }

// Delete Single User
public function delete(){
  // Create query
  $query = '
  DELETE FROM
    '. $this->table_name .'
  WHERE 
    user_id = :user_id
  ';

  // Prepare Statement 
  $stmt = $this->conn->prepare($query);

  $this->user_id = htmlspecialchars(strip_tags($this->user_id));

  // Bind Data
  $stmt->bindParam(':user_id', $this->user_id);

  // Execute Query 
  if($stmt->execute()){
    return true;
  }

  // Print error is something goes wrong
  printf("Error: $s.\n", $stmt->error);

  return false;
}
}