<?php

// Set CORS headers if needed
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$connection = new mysqli('localhost', 'root', '', 'stay-master');

// Check the connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Get the request method and URL path
$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = explode('/', trim($_SERVER['REQUEST_URI'], '/')); // Split the URL path into segments
// Assuming the base path is "/api", so the second segment should be either "users" or "hotels"
$entity = $requestUri[3] ?? null; // This will either be 'users' or 'hotels'

// Handle the request based on the entity and method
if ($entity === 'hotels') {
    if ($requestMethod === 'GET') {
        // Fetch hotels (GET /api/hotels)
        $sql = "SELECT * FROM hotels";
        $result = $connection->query($sql);

        if ($result->num_rows > 0) {
            $hotels = [];
            while ($row = $result->fetch_assoc()) {
                $hotels[] = [
                    "id" => $row['id'],
                    "img" => $row['img'],
                    "hotelName" => $row['hotelName'],
                    "rating" => $row['rating'],
                    "price" => $row['price'],
                    "imgsOfRooms" => json_decode($row['imgsOfRooms']),
                    "details" => $row['details'],
                    "facilities" => json_decode($row['facilities'])
                ];
            }
            echo json_encode($hotels);
        } else {
            echo json_encode([]);
        }
    } elseif ($requestMethod === 'POST') {
        // Add a new hotel (POST /api/hotels)
        $data = json_decode(file_get_contents('php://input'), true); // Get JSON data from the client

        if (isset($data['hotelName'], $data['rating'], $data['price'], $data['ImgsOfRooms'], $data['details'], $data['facilities'])) {
            // Sanitize inputs
            $hotelName = $connection->real_escape_string($data['hotelName']);
            $rating = $connection->real_escape_string($data['rating']);
            $price = $connection->real_escape_string($data['price']);
            $ImgsOfRooms = $connection->real_escape_string(json_encode($data['ImgsOfRooms']));
            $details = $connection->real_escape_string($data['details']);
            $facilities = $connection->real_escape_string(json_encode($data['facilities']));

            // Insert into the database
            $sql = "INSERT INTO hotels (hotelName, rating, price, ImgsOfRooms, details, facilities)
                    VALUES ('$hotelName', '$rating', '$price', '$ImgsOfRooms', '$details', '$facilities')";

            if ($connection->query($sql) === TRUE) {
                echo json_encode(["message" => "Hotel information added successfully!"]);
            } else {
                echo json_encode(["error" => "Error: " . $sql . "<br>" . $connection->error]);
            }
        } else {
            echo json_encode(["error" => "Invalid input: Please provide all required fields."]);
        }
    }
} elseif ($entity === 'users') {
    if ($requestMethod === 'GET') {
        // Fetch users (GET /api/users)
        $sql = "SELECT * FROM users";
        $result = $connection->query($sql);

        if ($result->num_rows > 0) {
            $users = [];
            while ($row = $result->fetch_assoc()) {
                $users[] = [
                    "id" => $row['id'],
                    "username" => $row['username'],
                    "password" => $row['password'],  
                    "role" => $row['role'],
                    "email" => $row['email'],
                    "hotelName" => $row['hotelName'], 
                ];
            }
            echo json_encode($users);
        } else {
            echo json_encode([]);
        }
    } elseif ($requestMethod === 'POST') {
        // Add a new user (POST /api/users)
        $data = json_decode(file_get_contents('php://input'), true); // Get JSON data from the client

        if (isset($data['username'], $data['email'], $data['password'], $data['role'])) {

            // Sanitize inputs
            $username = $connection->real_escape_string($data['username']);
            $email = $connection->real_escape_string($data['email']);
            $password = $connection->real_escape_string($data['password']);
            $role = $connection->real_escape_string($data['role']);
            // Check if hotelName is provided, otherwise set it to NULL
            $hotelName = isset($data['hotelName']) && !empty($data['hotelName']) 
            ? $connection->real_escape_string($data['hotelName']) 
            : 'NULL'; 
            // Insert into the database
            $sql = "INSERT INTO users (username, email, password, role, hotelName) 
                VALUES ('$username', '$email', '$password', '$role', '$hotelName')";

            if ($connection->query($sql) === TRUE) {
                echo json_encode(["message" => "User added successfully!"]);
            } else {
                echo json_encode(["error" => "Error: " . $sql . "<br>" . $connection->error]);
            }
        } else {
            echo json_encode(["error" => "Invalid input: Please provide all required fields."]);
        }
    }
}
elseif ($entity === 'payments') {
    if ($requestMethod === 'POST') {
        // Add a new payment (POST /api/payments)
        $data = json_decode(file_get_contents('php://input'), true); // Get JSON data from the client

        if (isset($data['cardNumber'], $data['cvc'], $data['cardName'], $data['payByName'], $data['payByEmail'], $data['payById'], $data['hotelId'], $data['hotelsName'], $data['hotelRoomPrice'])) {
            // Sanitize inputs
            $cardNumber = $connection->real_escape_string($data['cardNumber']);
            $cvc = $connection->real_escape_string($data['cvc']);
            $cardName = $connection->real_escape_string($data['cardName']);
            $payByName = $connection->real_escape_string($data['payByName']);
            $payByEmail = $connection->real_escape_string($data['payByEmail']);
            $payById = $connection->real_escape_string($data['payById']);
            $hotelId = $connection->real_escape_string($data['hotelId']);
            $hotelsName = $connection->real_escape_string($data['hotelsName']);
            $hotelRoomPrice = $connection->real_escape_string($data['hotelRoomPrice']);

            // Insert into the payments database table
            $sql = "INSERT INTO payments (cardNumber, cvc, cardName, payByName, payByEmail, payById, hotelId, hotelsName, hotelRoomPrice)
                    VALUES ('$cardNumber', '$cvc', '$cardName', '$payByName', '$payByEmail', '$payById', '$hotelId', '$hotelsName', '$hotelRoomPrice')";

            if ($connection->query($sql) === TRUE) {
                echo json_encode(["message" => "Payment processed successfully!"]);
            } else {
                echo json_encode(["error" => "Error: " . $sql . "<br>" . $connection->error]);
            }
        } else {
            echo json_encode(["error" => "Invalid input: Please provide all required fields."]);
        }
    } 
    elseif ($requestMethod === 'GET') {
        // Fetch payments (GET /api/payments)
        $sql = "SELECT * FROM payments";  // Default query to fetch all payments

        $result = $connection->query($sql);

        if ($result->num_rows > 0) {
            $payments = [];
            while ($row = $result->fetch_assoc()) {
                $payments[] = [
                    "id" => $row['id'],
                    "cardNumber" => $row['cardNumber'],  
                    "cvc" => $row['cvc'],
                    "cardName" => $row['cardName'],
                    "payByName" => $row['payByName'],
                    "payByEmail" => $row['payByEmail'],
                    "payById" => $row['payById'],
                    "hotelId" => $row['hotelId'],
                    "hotelsName" => $row['hotelsName'],
                    "hotelRoomPrice" => $row['hotelRoomPrice']
                ];
            }
            echo json_encode($payments);
        } else {
            echo json_encode([]);
        }
    }
}
elseif ($entity === 'decors') {
    if ($requestMethod === 'POST') {
        // Add a new decor (POST /api/decor)
        $data = json_decode(file_get_contents('php://input'), true); // Get JSON data from the client

        if (isset($data['img'], $data['companyName'], $data['details'], $data['imgsOfDesign'], $data['servicesAndPrice'])) {
            // Sanitize inputs
            $img = $connection->real_escape_string($data['img']);
            $companyName = $connection->real_escape_string($data['companyName']);
            $details = $connection->real_escape_string($data['details']);
            $imgsOfDesign = $connection->real_escape_string(json_encode($data['imgsOfDesign'])); // JSON encode array of image URLs
            $services = $connection->real_escape_string(json_encode($data['services'])); // JSON encode services

            // Insert into the decor database table
            $sql = "INSERT INTO decor (img, companyName, details, imgsOfDesign, servicesAndPrice)
                    VALUES ('$img', '$companyName', '$details', '$imgsOfDesign', '$servicesAndPrice')";

            if ($connection->query($sql) === TRUE) {
                echo json_encode(["message" => "Decor entry added successfully!"]);
            } else {
                echo json_encode(["error" => "Error: " . $sql . "<br>" . $connection->error]);
            }
        } else {
            echo json_encode(["error" => "Invalid input: Please provide all required fields."]);
        }
    }elseif ($requestMethod === 'GET') {
        // Fetch decor entries (GET /api/decor)
        $sql = "SELECT * FROM decors";  // Default query to fetch all decor entries

        $result = $connection->query($sql);

        if ($result->num_rows > 0) {
            $decorEntries = [];
            while ($row = $result->fetch_assoc()) {
                $decorEntries[] = [
                    "id" => $row['id'],
                    "img" => $row['img'],  
                    "companyName" => $row['companyName'],
                    "details" => $row['details'],
                    "imgsOfDesign" => json_decode($row['imgsOfDesign']),  // Decode the JSON array of images
                    "services" => json_decode($row['servicesAndPrice'])  // Decode the JSON array for services
                ];
            }
            echo json_encode($decorEntries);
        } else {
            echo json_encode([]);
        }
    }
}


else {
    // If the entity is not recognized (e.g., /api/somethingElse)
    header("HTTP/1.1 404 Not Found");
    echo json_encode(["error" => "Invalid endpoint"]);
}

?>


