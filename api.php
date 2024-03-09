<?php
// Security Headers
function cors() {
  if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');
  }

  if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
      header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT");
    }
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
      header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    }
    exit(0);
  }
}

cors();

include 'config.php';

// GET Nearest Hospitals
function nearest_hospitals($latitude, $longitude){
  global $pdo;
  
  $sql = "SELECT 
            latitude,
            longitude,
            hospital_name,
            address,
            contact_number,
            availability,
            (6371 * acos(cos(radians(:lat)) * cos(radians(latitude)) * cos(radians(longitude) - radians(:lng)) + sin(radians(:lat)) * sin(radians(latitude)))) AS distance
          FROM hospitals
          ORDER BY distance ASC"; // Sort by distance ascending
  $stmt = $pdo->prepare($sql);
  $stmt->bindParam(':lat', $latitude);
  $stmt->bindParam(':lng', $longitude);
  $stmt->execute();

  $data = array();
  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $data[] = $row;
  }

  echo json_encode($data);
}

function login($email, $password) {
  // Ensure that $pdo is properly initialized with your database connection
  global $pdo;

  // Validate input
  if (!$email || empty($password)) {
      // Invalid input
      return false;
  }

  // Your authentication logic goes here
  // This is a basic example, replace it with your actual authentication mechanism
  $sql = "SELECT * FROM user_credential WHERE email = :email";
  $stmt = $pdo->prepare($sql);
  $stmt->bindParam(':email', $email);
  $stmt->execute();

  // Fetch the user data
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  // Check if the user exists and the password is correct
  if ($user && password_verify($password, $user['password'])) {
    $result = login($providedEmail, $providedPassword);
    

    if ($result['success']) {
        echo json_encode(['message' => $result['message']]);
    }    return true;
  } else {
      // Failed login
      return false;
  }
}







?>
