<?php
header('Content-Type: application/json');
$request_uri = $_SERVER['REQUEST_URI'];
$uri_parts = explode('/', $request_uri);
$endpoint = implode('/', array_slice($uri_parts, 2));

include 'api.php';


// GET 
if (strpos($endpoint, 'api/nearest_hospitals') !== false) {
  if ($_SERVER['REQUEST_METHOD'] === 'GET') {
      $parts = explode('/', $endpoint);
      echo $parts;
      if (count($parts) == 5) {
        $latitude = $parts[3];
        $longitude = $parts[4];
        nearest_hospitals($latitude, $longitude); 
      } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid endpoint format']);
      }
  } else {
      http_response_code(405);
      echo json_encode(['error' => 'Invalid request method for this endpoint']);
  }
}

// Login
if (strpos($endpoint, 'api/login') !== false) {
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $data = json_decode(file_get_contents('php://input'), false);
      $email = filter_var($data['email'], FILTER_VALIDATE_EMAIL);
      $password = $data['password'];

      if (!$email || empty($password)) {
          // Invalid input
          http_response_code(400);
          echo json_encode(['error' => 'Invalid email or password']);
      } else {
          // Your authentication logic goes here
          // This is a basic example, and you should replace it with your actual authentication mechanism
          $sql = "SELECT * FROM user_credential WHERE email = :email";
          $stmt = $pdo->prepare($sql);
          $stmt->bindParam(':email', $email);
          $stmt->execute();

          // Fetch the user data
          $user = $stmt->fetch(PDO::FETCH_ASSOC);

          // Check if the user exists and the password is correct
          if ($user && password_verify($password, $user['password'])) {
              // Successful login
              http_response_code(200);
              echo json_encode(['message' => 'Login successful']);
          } else {
              // Failed login
              http_response_code(401);
              echo json_encode(['error' => 'Invalid credentials']);
          }
      }
  } else {
      // Invalid request method
      http_response_code(405);
      echo json_encode(['error' => 'Invalid request method for this endpoint']);
  }
}




?>
