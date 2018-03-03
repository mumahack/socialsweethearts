<?php
require __DIR__ . '/vendor/autoload.php';

class Image
{
    /**
     * @var \Imagine\Imagick\Imagine
     */
    protected $imagine;
    /**
     * @var \Imagine\Image\ImageInterface|\Imagine\Imagick\Image
     */
    protected $collage;

    public function __construct()
    {
        $this->imagine = new Imagine\Imagick\Imagine();
        $path = __DIR__ . "/1_crop.jpg";
        $this->collage = $this->imagine->open($path);
    }

    public function drawImages()
    {
        $constant = 175;
        // starting coordinates (in pixels) for inserting the first image
        $x = 0;
        $y = 0;
        foreach (glob(__DIR__ . '/sample/*.jpg') as $path) {
            // open photo
            $photo = $this->imagine->open($path);

            $photo->resize(new Imagine\Image\Box($constant, $constant));


            // paste photo at current position
            $this->collage->paste($photo, new Imagine\Image\Point($x, $y));

            // move position by 30px to the right
            $x += $constant;

        }
    }

    public function drawNames()
    {

    }

    public function drawTitle()
    {
        $file = "SFMoviePoster.ttf";
        $font = new \Imagine\Imagick\Font($this->collage->getImagick(), $file, 150, $this->collage->palette()->color('fff'));
        $text = "Günthers Choice";
        $draw = $this->collage->draw();
        $textlen = $draw->getTextLenght($text, $font);
        $xPosition = 700 / 2 - $textlen / 2;
        $draw->text($text, $font, new \Imagine\Image\Point($xPosition, 750), 0, 700);

    }

    public function output()
    {
        $this->collage->show("jpg");
    }

}

$obj = new Image();
$obj->drawImages();
$obj->drawTitle();
$obj->drawNames();
$obj->output();



