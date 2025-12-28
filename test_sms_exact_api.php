<?php

/**
 * Test SMS using exact API format provided
 */

$phoneNumber = '0622239304';
$message = 'Test message from OfisiLink system. This is a sample test SMS.';

// Clean phone number
$phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
if (!str_starts_with($phoneNumber, '255')) {
    $phoneNumber = '255' . ltrim($phoneNumber, '0');
}

echo "Testing SMS API with exact format...\n";
echo "Phone: {$phoneNumber}\n";
echo "Message: {$message}\n\n";

// Prepare body exactly as in the example
$body = json_encode([
    'from' => 'N-SMS',
    'to' => $phoneNumber,
    'text' => $message,
    'reference' => 'test_' . time()
]);

echo "Request Body: {$body}\n\n";

$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://messaging-service.co.tz/api/sms/v1/test/text/single',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => $body,
    CURLOPT_HTTPHEADER => array(
        'Authorization: Basic aW0yM246MjNuMjNu',
        'Content-Type: application/json',
        'Accept: application/json'
    ),
));

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
$curlError = curl_error($curl);
$curlErrno = curl_errno($curl);

curl_close($curl);

echo "HTTP Code: {$httpCode}\n";
if ($curlErrno) {
    echo "cURL Error: {$curlError} (Code: {$curlErrno})\n";
}
echo "Response: {$response}\n";

// Try to parse response
if ($response) {
    $responseData = json_decode($response, true);
    if ($responseData) {
        echo "\nParsed Response:\n";
        print_r($responseData);
    }
}







