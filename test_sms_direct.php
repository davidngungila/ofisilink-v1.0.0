<?php

/**
 * Direct SMS Test Script
 * This tests the SMS API directly without Laravel dependencies
 */

$phoneNumber = '0622239304';
$message = 'Test message from OfisiLink system. This is a sample test SMS.';

// Clean phone number
$phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
if (!str_starts_with($phoneNumber, '255')) {
    $phoneNumber = '255' . ltrim($phoneNumber, '0');
}

echo "Testing SMS API...\n";
echo "Phone: {$phoneNumber}\n";
echo "Message: {$message}\n\n";

// Configuration
$username = 'im23n';
$password = '23n23n';
$from = 'N-SMS';
$url = 'https://messaging-service.co.tz/api/sms/v1/test/text/single';

// Create auth
$auth = base64_encode($username . ':' . $password);
echo "Auth: {$auth}\n";
echo "Expected: aW0yM246MjNuMjNu\n";
echo "Match: " . ($auth === 'aW0yM246MjNuMjNu' ? 'YES' : 'NO') . "\n\n";

// Prepare body
$body = json_encode([
    'from' => $from,
    'to' => $phoneNumber,
    'text' => $message,
    'reference' => 'test_' . time()
]);

echo "Request URL: {$url}\n";
echo "Request Body: {$body}\n\n";

// Make request
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $body,
    CURLOPT_HTTPHEADER => [
        'Authorization: Basic ' . $auth,
        'Content-Type: application/json',
        'Accept: application/json'
    ],
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => 0,
    CURLOPT_VERBOSE => true,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
$curlErrno = curl_errno($ch);

echo "HTTP Code: {$httpCode}\n";
if ($curlErrno) {
    echo "cURL Error: {$curlError} (Code: {$curlErrno})\n";
}
echo "Response: {$response}\n";

curl_close($ch);

// Try to parse response
if ($response) {
    $responseData = json_decode($response, true);
    if ($responseData) {
        echo "\nParsed Response:\n";
        print_r($responseData);
    }
}







