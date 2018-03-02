<?php
require __DIR__ . '/vendor/autoload.php';




$imagine = new Imagine\Imagick\Imagine();
// make an empty image (canvas) 120x160px

$constant = 250;


$collage = $imagine->create(new Imagine\Image\Box($constant*2, $constant*2));

// starting coordinates (in pixels) for inserting the first image
$x = 0;
$y = 0;



foreach (glob(__DIR__.'/sample/*.jpg') as $path) {
    // open photo
    $photo = $imagine->open($path);

    $photo->resize(new Imagine\Image\Box($constant, $constant));


    // paste photo at current position
    $collage->paste($photo, new Imagine\Image\Point($x, $y));

    // move position by 30px to the right
    $x += $constant;

    if ($x >= $constant * 2) {
        // we reached the right border of our collage, so advance to the
        // next row and reset our column to the left.
        $y += $constant;
        $x = 0;
    }

    if ($y >= $constant*2) {
        break; // done
    }
}

$collage->show("jpg");

