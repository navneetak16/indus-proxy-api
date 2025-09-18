<?php
// Target URL
$targetUrl = "https://prod.api.indusgame.com/guest-signups";

// Capture request method (GET, POST, PUT, DELETE, etc.)
$method = $_SERVER['REQUEST_METHOD'];

// Capture request headers
$requestHeaders = getallheaders();
$headers = [];
foreach ($requestHeaders as $key => $value) {
    // Exclude Host header (we’ll let cURL set it to target)
    if (strtolower($key) !== 'host') {
        $headers[] = "$key: $value";
    }
}

// Capture request body (for POST, PUT, PATCH, etc.)
$body = file_get_contents("php://input");

// Initialize cURL
$ch = curl_init($targetUrl);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_HEADER, true); // include headers in output

// Send body if request has one
if (!empty($body)) {
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
}

// Execute request
$response = curl_exec($ch);

// Separate headers and body
$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$raw_headers = substr($response, 0, $header_size);
$response_body = substr($response, $header_size);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

// Send response headers back to client
$header_lines = explode("\r\n", trim($raw_headers));
foreach ($header_lines as $header_line) {
    if (!empty($header_line) && stripos($header_line, "Transfer-Encoding") === false) {
        header($header_line, false);
    }
}

// Send HTTP status code
http_response_code($http_code);

// Output response body
echo $response_body;
