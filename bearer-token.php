<?php

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
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Example usage:
$incomingToken = getBearerToken();
if ($incomingToken) {
    $userDetails = validateTokenAndRetrieveUser($pdo, $incomingToken);
    if ($userDetails) {
        echo json_encode($userDetails);
    } else {
        header('HTTP/1.0 401 Unauthorized');
        echo "Invalid or expired token.";
    }
} else {
    header('HTTP/1.0 401 Unauthorized');
    echo "No token provided.";
}

?>