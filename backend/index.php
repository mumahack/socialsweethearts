<?php
require __DIR__ . '/vendor/autoload.php';


$imagine = new Imagine\Imagick\Imagine();
// make an empty image (canvas) 120x160px


$path = __DIR__ . "/1_crop.jpg";
$collage = $imagine->open($path);


// starting coordinates (in pixels) for inserting the first image
$x = 0;
$y = 0;
$constant = 175;

foreach (glob(__DIR__ . '/sample/*.jpg') as $path) {
    // open photo
    $photo = $imagine->open($path);

    $photo->resize(new Imagine\Image\Box($constant, $constant));


    // paste photo at current position
    $collage->paste($photo, new Imagine\Image\Point($x, $y));

    // move position by 30px to the right
    $x += $constant;


}


$file = "SFMoviePoster.ttf";
$font = new \Imagine\Imagick\Font($collage->getImagick(), $file, 150, $collage->palette()->color('fff'));

$text = "The Title";
$textlen = $collage->draw()->getTextLenght($text, $font);


$xPosition = 700 / 2 - $textlen / 2;
//print_r($textlen);
//die($textlen);
$collage->draw()
    ->text($text, $font, new \Imagine\Image\Point($xPosition, 600), 0, 700);


$collage->show("jpg");

