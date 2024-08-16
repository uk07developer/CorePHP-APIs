<?php

function generateSecurityKey($length = 32) {
    return bin2hex(random_bytes($length / 2)); // Generates a random hexadecimal string
}

function generateSecurityCode($length = 6) {
    return strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, $length)); // Generates a random alphanumeric string
}

function validateSecurityAccess() {
    // Your predefined security key and security code (store these securely!)
    $predefinedSecurityKey = 'f20ef16a5ba8276e2dcdf2ef0d2a0470';
    $predefinedSecurityCode = '9A6280';
    
    // Get the security key and security code from the request headers
    $receivedSecurityKey = isset($_SERVER['HTTP_SECURITY_KEY']) ? $_SERVER['HTTP_SECURITY_KEY'] : '';
    $receivedSecurityCode = isset($_SERVER['HTTP_SECURITY_CODE']) ? $_SERVER['HTTP_SECURITY_CODE'] : '';
    
    // Validate the security key and security code
    if ($receivedSecurityKey === $predefinedSecurityKey && $receivedSecurityCode === $predefinedSecurityCode) {
        // Process the API request
        $response = array('status' => 'success', 'message' => 'Valid security key and code.');
    } else {
        // Unauthorized access
        http_response_code(401);
        $response = array('status' => 'error', 'message' => 'Invalid security key or code.');
    }
    
    // Return the response as JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}
