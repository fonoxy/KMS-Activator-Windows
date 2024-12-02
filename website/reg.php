<?php
// reg.php

// Ensure a code is passed in the URL (via ?code=)
if (!isset($_GET['code']) || empty($_GET['code'])) {
    echo "001"; // Error code if no code is provided
    exit;
}

$code = $_GET['code']; // Get the code from the URL
$codesFile = 'codes.json';
$usedCodesFile = 'used_codes.json';

// Read data from the files
$codes = json_decode(file_get_contents($codesFile), true);
$usedCodes = json_decode(file_get_contents($usedCodesFile), true);

// Check if the code exists in the codes file
if (isset($codes[$code])) {
    // Remove "Windows " from the OS field, if present
    if (isset($codes[$code]['os'])) {
        $codes[$code]['os'] = str_replace('Windows ', '', $codes[$code]['os']);
    }

    // Check if the code has already been redeemed
    if (isset($usedCodes[$code]) && $usedCodes[$code] === true) {
        // If the code has been redeemed and &json=true is provided, return all details except the key
        if (isset($_GET['json']) && $_GET['json'] === 'true') {
            // Prepare JSON output without the key
            $response = $codes[$code];
            unset($response['key']); // Remove the key from the JSON response
            
            // Set JSON header
            header('Content-Type: application/json');
            echo json_encode($response, JSON_PRETTY_PRINT);
        } else {
            // Otherwise, show "002" indicating the code has already been redeemed
            echo "002";
        }
    } else {
        // If the code hasn't been redeemed, mark it as redeemed
        $usedCodes[$code] = true;
        file_put_contents($usedCodesFile, json_encode($usedCodes, JSON_PRETTY_PRINT));

        // Display only the key as plain text
        $key = $codes[$code]['key'];
        echo $key;
    }
} else {
    echo "003"; // Error code if the code has never existed
}

/*
Error Codes:
001 - No code provided in the URL (e.g., ?code=).
002 - Code has been used (redeemed) already.
003 - Code does not exist in the codes file.
*/
?>
