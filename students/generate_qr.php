<?php
require_once __DIR__ . '/vendor/autoload.php';
use chillerlan\QRCode\QRCode;

// Data for QR code
$data = "https://google.com"; // Replace with dynamic data if needed

// Output the QR code image directly
header('Content-Type: image/png');
echo (new QRCode)->render($data);
