<?php
require __DIR__ . '/vendor/autoload.php';


$imagine = new Imagine\Imagick\Imagine();
// make an empty image (canvas) 120x160px

$constant = 250;

$path = __DIR__."/sample/1_crop.jpg";
$collage = $imagine->open($path);



$file = "SFMoviePoster.ttf";
$font = new \Imagine\Imagick\Font($collage->getImagick(), $file, 150, $collage->palette()->color('fff'));
$collage->draw()
    ->text("The Title", $font, new \Imagine\Image\Point(40, 600),0,700);


$collage->show("jpg");

