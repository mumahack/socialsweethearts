<?php
require __DIR__ . '/vendor/autoload.php';

class FontStruct
{
    private $text;

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return mixed
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return mixed
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @return \Imagine\Image\Point
     */
    public function getPoint()
    {
        return $this->point;
    }

    /**
     * @return mixed
     */
    public function getWidth()
    {
        return $this->width;
    }

    private $size;
    private $color;
    /**
     * @var \Imagine\Image\Point
     */
    private $point;
    private $width;

    public function __construct(
        $text,
        $size,
        $color,
        \Imagine\Image\Point $point,
        $width
    )
    {
        $this->text = $text;
        $this->size = $size;
        $this->color = $color;
        $this->point = $point;
        $this->width = $width;
    }
}

class Image
{

    public $funnyNameArray = [
        /*
        "__NAME__ und der letzte Gentleman",
        "The Curious case of __NAME__ Button",
        "__NAME__ Wonka and the Chocolate Factory",
        */
        "__NAME__ Croft: Tomb Raider",
        "3 Engel für __NAME__",
        "__NAME__ die Meerjungfrau",
        "Findet __NAME__",

        "__NAME__ Brokovich",
        "__NAME__ - Total verhext",
        "__NAME__ in Wonderland",
        "__NAME__'s Choice",
        "__NAME__ Jones' diary",
        "A Fish called __NAME__",
        "The exorcism of __NAME__ rose",
        "Along came __NAME__",
        "__NAME__ Poppins",
        "__NAME__ Croft: Tomb Raider",
        "__NAME__ The Vampire Slayer",

        "__NAME__ Gump",
        "__NAME__ Potter",

        "__NAME__ Jones",
        "How __NAME__ Stole Christmas",
        "Verrückt nach __NAME__",
        "__NAME__ und der wilde Kaiser",
        "__NAME__ Potter",

    ];
    /**
     * @var \Imagine\Imagick\Imagine
     */
    protected $imagine;
    /**
     * @var \Imagine\Image\ImageInterface|\Imagine\Imagick\Image
     */
    protected $collage;
    /**
     * @var \stdClass
     */
    protected $data;
    /**
     * @var array
     */
    protected $tmpFiles;

    /**
     * @param array $data
     */
    public function __construct($data)
    {
        $this->imagine = new Imagine\Imagick\Imagine();
        $path = __DIR__ . "/1_crop.jpg";
        $this->collage = $this->imagine->open($path);
        $this->data = $data->form;
    }

    public function drawImages()
    {
        $constant = 175;
        // starting coordinates (in pixels) for inserting the first image
        $x = 0;
        $y = 0;
        foreach ($this->data as $user) {
            // open photo
            $tmpFile = tempnam("/tmp", "TEST");
            $url = $user->image;
            $content = file_get_contents($url);
            file_put_contents($tmpFile, $content);
            //file_put_contents($tmpFile, base64_decode($user->imageData));
            $photo = $this->imagine->open($tmpFile);

            $photo->resize(new Imagine\Image\Box($constant, $constant));


            // paste photo at current position
            $this->collage->paste($photo, new Imagine\Image\Point($x, $y));

            // move position by 30px to the right
            $x += $constant;

        }
    }

    public function drawNames()
    {
        $vornamen = array(
            $this->data[0]->name,
            $this->data[1]->name,
            $this->data[2]->name,
            $this->data[3]->name
        );
        $nachnamen = array(
            "Cruise",
            "Carrey",
            "Murphy",
            "Stiller"
        );
        for ($i = 0; $i < 4; $i++) {
            $fontStructure = new FontStruct(
                $vornamen[$i],
                40,
                'fff',
                new \Imagine\Image\Point($i * 175, 175),
                175
            );
            $this->createCenterText($fontStructure);

            $fontStructure = new FontStruct(
                $nachnamen[$i],
                60,
                'fff',
                new \Imagine\Image\Point($i * 175, 200),
                175
            );
            $this->createCenterText($fontStructure);


        }


    }

    public function drawTitle()
    {
        $title = "Günthers Choice";

        $nachName = $this->data[0]->name;
        $randNumber = rand(0, count($this->funnyNameArray));
        $randNumber = 0;
        $title = $this->funnyNameArray[$randNumber];
        $title = str_replace("__NAME__", $nachName, $title);


        $fontStructure = new FontStruct(
            $title,
            150,
            'fff',
            new \Imagine\Image\Point(0, 750),
            700
        );
        $this->createCenterText($fontStructure);


    }

    public function createCenterText(FontStruct $fontStruct)
    {
        $file = "SFMoviePoster.ttf";
        $size = $fontStruct->getSize();
        $font = new \Imagine\Imagick\Font($this->collage->getImagick(), $file, $size, $this->collage->palette()->color($fontStruct->getColor()));
        $text = $fontStruct->getText();
        $draw = $this->collage->draw();

        $textlen = $draw->getTextLenght($text, $font);
        while ($textlen > $fontStruct->getWidth() - 20 * 2) {
            $size--;
            $font->setSize($size);
            $textlen = $draw->getTextLenght($text, $font);
        }
        $xPosition = $fontStruct->getWidth() / 2 - $textlen / 2;
        $point = new \Imagine\Image\Point($fontStruct->getPoint()->getX() + $xPosition, $fontStruct->getPoint()->getY());
        $draw->text($text, $font, $point, 0);
    }

    public function output()
    {
        $md5 = md5($this->collage->get('jpg'));

        $fileName = $md5 . ".jpg";
        $this->collage->save(__DIR__ . "/pictures/" . $fileName);
        $url = "https://post-a-poster.twinsen.de/backend/pictures/" . $fileName;
        return $url;

    }
}

$preData = '


{
  "form": [
    {
      "name": "Berni",
      "gender": "male",
      "image": "https:\/\/scontent.xx.fbcdn.net\/v\/t1.0-1\/c134.0.533.533\/564947_540508512642219_1652399921_n.jpg?oh=d025ae49a080d6cf9198d084c6edaf7c&oe=5B0C110B"
    },
    {
      "name": "Maurizio",
      "image": "https:\/\/scontent.xx.fbcdn.net\/v\/t31.0-1\/p720x720\/14257483_1223334907687298_5770953523741588902_o.jpg?oh=77a6e93735851dc43a2b50ca7dc740a6&oe=5B426FA5"
    },
    {
      "name": "Ira",
      "image": "https:\/\/scontent.xx.fbcdn.net\/v\/t1.0-1\/c0.0.720.720\/21314801_1483958651647285_3362088778471442842_n.jpg?oh=19b1cb73e2dc8505672bb21b8d027d5d&oe=5B47BDA8"
    },
    {
      "name": "Michael",
      "image": "https:\/\/scontent.xx.fbcdn.net\/v\/t31.0-1\/c344.0.720.720\/p720x720\/21055000_1781101275252494_8901017328562721445_o.jpg?oh=cfbaf2528e523dabd8c04bc0d9820ae3&oe=5B04210C"
    }
  ]
}


';


$data = json_decode(file_get_contents('php://input'));
if ($data == null) {
    $data = $preData;
}


$data = json_decode($data);
$obj = new Image($data);
$obj->drawImages();
$obj->drawTitle();
$obj->drawNames();
echo $obj->output();

