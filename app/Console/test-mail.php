<?php
echo "OpenSSL CA File: " . ini_get('openssl.cafile') . "<br>";
echo "cURL CA Info: " . ini_get('curl.cainfo') . "<br>";
echo "OpenSSL Version: " . OPENSSL_VERSION_TEXT . "<br>";

$context = stream_context_create([
    'ssl' => [
        'verify_peer' => true,
        'cafile' => ini_get('openssl.cafile')
    ]
]);

$result = file_get_contents('https://www.google.com', false, $context);
echo $result ? "SSL test PASSED" : "SSL test FAILED";
?>