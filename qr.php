<?php
require_once __DIR__ . '/vendor/autoload.php';

use chillerlan\QRCode\QRCode;

$data = "https://google.com";
?>

<img src="<?=(new QRCode)->render($data)?>" alt="QR Code" />
