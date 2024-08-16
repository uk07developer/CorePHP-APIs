<?php

// PDO connection
$pdo = new PDO('mysql:host=localhost;dbname=your_database', 'username', 'password');

function generateBearerToken($length = 32) {
    return bin2hex(random_bytes($length));
}

function storeTokenInDatabase($pdo, $userId, $token) {
    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour')); // 1-hour expiration

    $stmt = $pdo->prepare("INSERT INTO user_tokens (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)");
    $stmt->execute([
        ':user_id' => $userId,
        ':token' => $token,
        ':expires_at' => $expiresAt
    ]);
}

function getBearerToken() {
    $headers = apache_request_headers();
    if (isset($headers['Authorization'])) {
        $matches = [];
        if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
            return $matches[1];
        }
    }
    return null;
}

function validateTokenAndRetrieveUser($pdo, $token) {
    $stmt = $pdo->prepare("
        SELECT users.* 
        FROM user_tokens 
        JOIN users ON users.id = user_tokens.user_id 
        WHERE user_tokens.token = :token AND user_tokens.expires_at > NOW()
    ");
    $stmt->execute([':token' => $token]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // If the token is invalid or expired, return a 401 error and exit
        header('HTTP/1.0 401 Unauthorized');
        echo json_encode([
            "error" => "Invalid or expired token."
        ]);
        exit();
    }

    return $user;
}

// Example usage:

// Simulate user login
$userId = 1; // Example user ID from login
$token = generateBearerToken();
storeTokenInDatabase($pdo, $userId, $token);
echo "Bearer Token: " . $token . "\n";

// Simulate API request with token validation
$incomingToken = getBearerToken();
if ($incomingToken) {
    $userDetails = validateTokenAndRetrieveUser($pdo, $incomingToken);
    echo json_encode($userDetails);
} else {
    header('HTTP/1.0 401 Unauthorized');
    echo json_encode([
        "error" => "No token provided."
    ]);
    exit();
}