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

// Login endpoint
if ($endpoint === 'api/login') {
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve username and password from request body
    $data = json_decode(file_get_contents('php://input'), true);
    $username = $data['username'];
    $password = $data['password'];

    // Validate username and password (you should hash the password before comparing)
    if ($username && $password) {
      // Assuming your login function returns true or false
      if (login($username, $password)) {
        echo json_encode(['message' => 'Login successful']);
      } else {
        http_response_code(401); // Unauthorized
        echo json_encode(['error' => 'Invalid username or password']);
      }
    } else {
      http_response_code(400); // Bad request
      echo json_encode(['error' => 'Missing username or password']);
    }
  } else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Invalid request method for this endpoint']);
  }
}

// Register endpoint
if ($endpoint === 'api/register') {
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve username and password from request body
    $data = json_decode(file_get_contents('php://input'), true);
    $username = $data['username'];
    $password = $data['password'];

    // Validate username and password
    if ($username && $password) {
      // Attempt registration
      $registration_result = register($username, $password);
      if ($registration_result) {
        echo json_encode(['message' => 'Registration successful']);
      } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(['error' => 'Registration failed']);
      }
    } else {
      http_response_code(400); // Bad request
      echo json_encode(['error' => 'Missing username or password']);
    }
  } else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Invalid request method for this endpoint']);
  }
}

?>
