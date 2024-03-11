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

// Login function
function login($username, $password) {
  global $pdo;
  
  $sql = "SELECT * FROM users WHERE username = :username AND password = :password";
  $stmt = $pdo->prepare($sql);
  $stmt->bindParam(':username', $username);
  $stmt->bindParam(':password', $password); // Note: You should hash the password before comparing in a real-world application
  $stmt->execute();

  $user = $stmt->fetch(PDO::FETCH_ASSOC);
  
  if ($user) {
    // Start session and set session variables
    session_start();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    return true;
  } else {
    return false;
  }
}

// Register function
function register($username, $password) {
  global $pdo;
  
  // You should hash the password before storing it in the database
  $hashed_password = password_hash($password, PASSWORD_DEFAULT);
  
  $sql = "INSERT INTO users (username, password) VALUES (:username, :password)";
  $stmt = $pdo->prepare($sql);
  $stmt->bindParam(':username', $username);
  $stmt->bindParam(':password', $hashed_password);
  $result = $stmt->execute();
  
  return $result;
}

// Handle register
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["username"]) && isset($_POST["password"])) {
  $username = $_POST["username"];
  $password = $_POST["password"];
  
  // Check if the username already exists
  $sql_check_username = "SELECT id FROM users WHERE username = :username";
  $stmt_check_username = $pdo->prepare($sql_check_username);
  $stmt_check_username->bindParam(':username', $username);
  $stmt_check_username->execute();
  $existing_user = $stmt_check_username->fetch(PDO::FETCH_ASSOC);
  
  if ($existing_user) {
    // Username already exists
    http_response_code(400);
    echo json_encode(array("error" => "Username already exists"));
    exit();
  }

  // Attempt to register the new user
  if (register($username, $password)) {
    // Registration successful
    echo json_encode(array("message" => "Registration successful"));
    exit();
  } else {
    // Registration failed
    http_response_code(500);
    echo json_encode(array("error" => "Registration failed"));
    exit();
  }
}

// If not a register request, check if it's a login request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["username"]) && isset($_POST["password"])) {
  $username = $_POST["username"];
  $password = $_POST["password"];
  
  if (login($username, $password)) {
    // Login successful
    echo json_encode(array("message" => "Login successful"));
    exit();
  } else {
    // Login failed
    http_response_code(401);
    echo json_encode(array("error" => "Invalid username or password"));
    exit();
  }
}

// If not a login or register request, assume it's a request for nearest hospitals
if ($_SERVER["REQUEST_METHOD"] == "GET") {
  // Check if latitude and longitude are provided in the URL
  if (isset($_GET["latitude"]) && isset($_GET["longitude"])) {
    $latitude = $_GET["latitude"];
    $longitude = $_GET["longitude"];
    
    // Call the function to fetch nearest hospitals
    nearest_hospitals($latitude, $longitude);
  } else {
    // Latitude and longitude not provided
    http_response_code(400);
    echo json_encode(array("error" => "Latitude and longitude parameters are required"));
    exit();
  }
} else {
  // Method not allowed
  http_response_code(405);
  echo json_encode(array("error" => "Method not allowed"));
  exit();
}
?>
