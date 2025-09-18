<?php

require __DIR__ . '/vendor/autoload.php';

$image = new Imagick("IMG_9987.HEIC");
//$image->pingImage("IMG_9987.HEIC");
//$info = $image->getImageProperties("*");


$image->setImageFormat("jpg");
$image->setImageCompressionQuality(95);
$image->writeImage(__DIR__ . '/test.jpg' );

$image->destroy();
$image = null;

$info = get_image_gps("test.jpg");

var_dump($info);