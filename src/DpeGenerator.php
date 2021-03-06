<?php

namespace LBIGroupDpeGenerator;


use Exception;
use Imagick;
use ImagickDraw;

/**
 * Class DpeGenerator
 * @package LBIGroupDpeGenerator
 */
class DpeGenerator
{

    /**
     * JSON structure construct for DPEG generation
     * @var mixed
     */
    private $json;
    private $default_json = __DIR__ . DIRECTORY_SEPARATOR . 'json' . DIRECTORY_SEPARATOR . 'dpe.json';

    /**
     * Picture target (ONLY if you want to generate picture on your system)
     * @var  null
     */
    private $pictTarget;

    /**
     * Picture Name
     * @var null
     */
    private $pictName;

    /**
     * Bool value for generating picture, if it's true and pictTarget is implement, your picture is generate in your target folder
     * @var bool
     */
    private $generateImage = false;

    /**
     * type of picture dpe or ges
     * @var string
     */
    private $type = 'dpe';

    /**
     * Value of DPE
     * @var
     */
    private $dpeVal;

    /**
     * value of GES
     * @var
     */
    private $gesVal;

    /**
     * value of iso CODE
     * @var
     */
    private $isoCode;

    /**
     * value of isDpeAltitude
     * @var bool
     */
    private $isDpeAltitude = false;


    private $size = self::PRINT_SIZE_TYPE;

    /**
     * constant to define the type
     */
    public const DPE_TYPE = 'dpe';
    public const GES_TYPE = 'ges';

    public const PRINT_SIZE_TYPE = 'print';
    public const WEB_SIZE_TYPE = 'web';

    private const KG_CO2_M2 = 'kgCO2/m².an';
    private const KWH_M2 = 'kWh/m².an';

    /**
     * DpeGenerator constructor.
     */
    public function __construct($isCode = 'FR')
    {
        $this->isoCode = $isCode;
        $fileName = __DIR__ . DIRECTORY_SEPARATOR . 'json' . DIRECTORY_SEPARATOR . $this->isoCode . DIRECTORY_SEPARATOR . 'dpe.json';
        if (!file_exists($fileName)) {
            $fileName = $this->default_json;
            $this->isoCode = 'FR';
        }
        $this->json = json_decode(file_get_contents($fileName));
    }
#region GETTER/SETTER

    /**
     * @param $generateImage
     */
    public function setImageSize($size): void
    {
        $this->size = $size;
    }

    /**
     * @return bool
     */
    private function getImageSize(): ?string
    {
        return $this->size;
    }

    /**
     * @param $generateImage
     */
    public function setGenerateImage($generateImage): void
    {
        $this->generateImage = $generateImage;
    }

    /**
     * @return bool
     */
    private function getGenerateImage(): ?bool
    {
        return $this->generateImage;
    }

    /**
     * set target to write your picture on your system
     * @param string $path
     */
    public function setPathToWriteImage(string $path): void
    {
        $this->pictTarget = $path;
    }

    /**
     * get target to write your picture on your system
     * @return null|string
     */
    private function getPathToWriteImage(): ?string
    {
        return $this->pictTarget;
    }

    /**
     * @param string $pictname
     */
    public function setNameOfPicture(string $pictname): void
    {
        $this->pictName = $pictname;
    }

    /**
     * @return null
     */
    private function getNameOfPicture(): ?string
    {
        return $this->pictName;
    }

    /**
     * @param string $type
     */
    public function setPictureType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    private function getPictureType(): string
    {
        return $this->type;
    }

    /**
     * @param int $dpeVal
     */
    public function setDpeVal(int $dpeVal): void
    {
        $this->dpeVal = $dpeVal;
    }

    /**
     * @return mixed
     */
    private function getDpeVal(): int
    {
        return $this->dpeVal;
    }

    /**
     * @param int $gesVal
     */
    public function setGesVal(int $gesVal): void
    {
        $this->gesVal = $gesVal;
    }

    /**
     * @return mixed
     */
    private function getGesVal(): int
    {
        return $this->gesVal;
    }

    /**
     * @param bool $isDpeAltitude
     */
    public function setIsDpeAltitude(bool $isDpeAltitude): void
    {
        $this->isDpeAltitude = $isDpeAltitude;
    }

    /**
     * @return bool
     */
    private function getIsDpeAltitude(): ?bool
    {
        return $this->isDpeAltitude;
    }


#endregion

    /**
     * This function allows you to launch the generation of your image according to the parameters entered
     * @return \Imagick|string|null
     * @throws \ImagickDrawException
     * @throws \ImagickException
     */
    public function generatePicture()
    {
        try {
            if ($this->isoCode === 'FR') {
                if ($this->getPictureType() === 'dpe') {

                    return $this->generateImgDpe();
                }

                return $this->generateImgGes();
            }
            if ($this->isoCode === 'GP') {
                return $this->generateImgDPEG();
            }

            return null;
        } catch (Exception $exception) {
            throw $exception;
        }
    }


    #region FRENCH DPE

    /**
     * DPE image generation function
     * @return \Imagick|string
     * @throws \ImagickDrawException
     * @throws \ImagickException
     * @throws \Exception
     */
    private function generateImgDpe()
    {
        if ($letterDpe = $this->getNewLetterDPEG()) {
            $dpeConf = $this->json->dpe->{$letterDpe};
            $dirSource = __DIR__ . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $this->isoCode . DIRECTORY_SEPARATOR;
            $fontValue = 90;
            $fontText = 25;
            $x_dpe_val = 90 + (20 * (3 - strlen($this->getDpeVal())));
            $x_dpe_text = 95;
            $x_ges_val = 296 + (20 * (3 - strlen($this->getGesVal())));
            $x_ges_text = 296;

            if ($this->getImageSize() === self::WEB_SIZE_TYPE && isset($this->json->web)) {
                $dpeConf = $this->json->web->dpe->{$letterDpe};
                $dirSource = __DIR__ . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $this->isoCode . DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR;
                $fontValue = 58;
                $fontText = 12;
                $x_dpe_val = 6 + (20 * (3 - strlen($this->getDpeVal())));
                $x_dpe_text = 25;
                $x_ges_val = 90 + (20 * (3 - strlen($this->getGesVal())));
                $x_ges_text = 110;
            }

            if ($dpeConf) {
                $image = new Imagick($dirSource . $dpeConf->img);

                $draw = new \ImagickDraw();
                $draw->setStrokeColor('black');
                $draw->setFillColor('black');
                $draw->setFont(__DIR__ . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . 'arial.ttf');
                $draw->setStrokeWidth(1);
                $draw->setFontSize($fontValue);
                $image->annotateimage($draw, $x_dpe_val, $dpeConf->val, 0, $this->getDpeVal());

                $draw = new \ImagickDraw();
                $draw->setStrokeColor('black');
                $draw->setFillColor('black');
                $draw->setFont(__DIR__ . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . 'arial.ttf');
                $draw->setStrokeWidth(1);
                $draw->setFontSize($fontText);
                $image->annotateimage($draw, $x_dpe_text, $dpeConf->text, 0, self::KWH_M2);

                $draw = new \ImagickDraw();
                $draw->setStrokeColor('black');
                $draw->setFillColor('black');
                $draw->setFont(__DIR__ . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . 'arial.ttf');
                $draw->setStrokeWidth(1);
                $draw->setFontSize($fontValue);
                $image->annotateimage($draw, $x_ges_val, $dpeConf->val, 0, $this->getGesVal());

                $draw = new \ImagickDraw();
                $draw->setStrokeColor('black');
                $draw->setFillColor('black');
                $draw->setFont(__DIR__ . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . 'arial.ttf');
                $draw->setStrokeWidth(1);
                $draw->setFontSize($fontText);

                $image->annotateimage($draw, $x_ges_text, $dpeConf->text, 0, self::KG_CO2_M2);
                $image->setImageFormat('png');
                $image->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);

                if ($this->getGenerateImage() && $this->getPathToWriteImage()) {
                    $imgTemporary = $this->getPathToWriteImage() . ($this->getNameOfPicture() ?: 'dpeg_' . $this->getDpeVal() . '_' . $this->getGesVal()) . '.png';
                    $imgTemporaryJpg = $this->getPathToWriteImage() . ($this->getNameOfPicture() ?: 'dpeg_' . $this->getDpeVal() . '_' . $this->getGesVal()) . '.jpg';
                    $image->writeImage($imgTemporary);
                    $image->setImageFormat('jpg');
                    $image->writeImage($imgTemporaryJpg);

                    return $imgTemporary;
                }

                return $image;

            }
            throw new Exception('Sorry our JSON is gone away', 500);
        } else {
            throw new Exception('Your value for DPE is not correct, please fill in a valid integer', 500);
        }
    }

    /**
     * GES image generation function
     * @return \Imagick|string|null
     * @throws \ImagickDrawException
     * @throws \ImagickException
     * @throws \Exception
     */
    private function generateImgGes()
    {
        if ($letterGes = $this->getNewLetterGES()) {
            $gesConf = $this->json->ges->{$letterGes};
            $dirSource = __DIR__ . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $this->isoCode . DIRECTORY_SEPARATOR;
            if ($this->getImageSize() === self::WEB_SIZE_TYPE && isset($this->json->web)) {
                $gesConf = $this->json->web->ges->{$letterGes};
                $dirSource = __DIR__ . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $this->isoCode . DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR;
            }
            if ($gesConf) {
                $image = new Imagick($dirSource . $gesConf->img);

                $draw = new \ImagickDraw();
                $draw->setStrokeColor('black');
                $draw->setFillColor('black');
                $draw->setFont(__DIR__ . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . 'arial.ttf');
                $draw->setStrokeWidth(1);
                $draw->setFontSize(60);
                $image->annotateimage($draw, $gesConf->ges_val, $gesConf->x_val, 0, $this->getGesVal());

                $draw = new \ImagickDraw();
                $draw->setStrokeColor('black');
                $draw->setFillColor('black');
                $draw->setFont(__DIR__ . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . 'arial.ttf');
                $draw->setStrokeWidth(1);
                $draw->setFontSize(15);
                $image->annotateimage($draw, $gesConf->ges_text, $gesConf->x_val, 0, self::KG_CO2_M2);
                $image->setImageFormat('png');
                $image->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
                if ($this->getGenerateImage() && $this->getPathToWriteImage()) {
                    $imgTemporary = $this->getPathToWriteImage() . ($this->getNameOfPicture() ?: 'ges_' . $this->getGesVal()) . '.png';
                    $imgTemporaryJpg = $this->getPathToWriteImage() . ($this->getNameOfPicture() ?: 'ges_' . $this->getGesVal()) . '.jpg';
                    $image->writeImage($imgTemporary);
                    $image->setImageFormat('jpg');
                    $image->writeImage($imgTemporaryJpg);

                    return $imgTemporary;
                }

                return $image;
            }
            throw new Exception('Sorry our JSON is gone away', 500);
        } else {
            throw new Exception('Your value for GES is not correct, please fill in a valid integer', 500);
        }
    }

    /**
     * This function allows you to retrieve the letter of the DPEG according to its value DPE AND GES
     * @return string|null
     */
    public function getNewLetterDPEG(): ?string
    {
        $dpe_cons = $this->getDpeVal();
        $dpe_ges = $this->getGesVal();
        $isDpeAltitude = $this->getIsDpeAltitude();

        if ($dpe_cons < 70 && $dpe_ges < 6) {
            return 'A';
        }
        if ($dpe_cons <= 110 && $dpe_ges <= 11) {
            return 'B';
        }
        if ($dpe_cons <= 180 && $dpe_ges <= 30) {
            return 'C';
        }
        if ($dpe_cons <= 250 && $dpe_ges <= 50) {
            return 'D';
        }
        if ($isDpeAltitude) {
            if ($dpe_cons <= 390 && $dpe_ges <= 80) {
                return 'E';
            }
            if ($dpe_cons <= 500 && $dpe_ges <= 110) {
                return 'F';
            }
            if ($dpe_cons > 500 || $dpe_ges > 110) {
                return 'G';
            }
        }
        if ($dpe_cons <= 330 && $dpe_ges <= 70) {
            return 'E';
        }
        if ($dpe_cons <= 420 && $dpe_ges <= 100) {
            return 'F';
        }
        if ($dpe_cons > 420 || $dpe_ges > 100) {
            return 'G';
        }

        return null;
    }


    /**
     * This function allows you to retrieve the letter of the GES according to its value of GES only
     * @return string|null
     */
    public function getNewLetterGES(): ?string
    {
        $dpe_ges = $this->getGesVal();
        $isDpeAltitude = $this->getIsDpeAltitude();
        if ($dpe_ges < 6) {
            return 'A';
        }
        if ($dpe_ges <= 11) {
            return 'B';
        }
        if ($dpe_ges <= 30) {
            return 'C';
        }
        if ($dpe_ges <= 50) {
            return 'D';
        }
        if ($isDpeAltitude) {
            if ($dpe_ges <= 80) {
                return 'E';
            }
            if ($dpe_ges <= 110) {
                return 'F';
            }

            return 'G'; // > 110
        }
        if ($dpe_ges <= 70) {
            return 'E';
        }
        if ($dpe_ges <= 100) {
            return 'F';
        }

        return 'G'; // > 100
    }
    #endregion

    #region GUADELOUPE DPE
    /**
     * DPEG image generation function
     * @return \Imagick|string
     * @throws \ImagickDrawException
     * @throws \ImagickException
     */
    private function generateImgDPEG()
    {
        if ($letterDPEG = $this->getLetterDPEGGP()) {
            if ($this->json->dpe->{$letterDPEG}) {
                $image = new Imagick(__DIR__ . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $this->isoCode . DIRECTORY_SEPARATOR . "base_cons_dpeg.png");

                $imageEtiquette = new Imagick(__DIR__ . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . "etiquette_cons_void.png");
                $image->compositeImage($imageEtiquette, Imagick::COMPOSITE_OVER, $this->json->dpe->{$letterDPEG}->img_x, $this->json->dpe->{$letterDPEG}->img_y);

                $draw = new \ImagickDraw();
                $draw->setStrokeColor('white');
                $draw->setFillColor('white');
                $draw->setFont(__DIR__ . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . 'arial.ttf');
                $draw->setStrokeWidth(1);
                $draw->setFontSize(40);
                $image->annotateimage($draw, $this->json->dpe->{$letterDPEG}->value_x, $this->json->dpe->{$letterDPEG}->value_y, 0, $this->getDpeVal());
                $draw = new \ImagickDraw();
                $draw->setStrokeColor('black');
                $draw->setFillColor('black');
                $draw->setFont(__DIR__ . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . 'arial.ttf');
                $draw->setStrokeWidth(0);
                $draw->setFontSize(25);
                $nombreTirets = $this->json->dpe->{$letterDPEG}->nombreTirets;
                for ($i = 0; $i < $nombreTirets; ++$i) {
                    $tirets .= '-';
                }

                $image->annotateimage($draw, $this->json->dpe->{$letterDPEG}->tiret_x, $this->json->dpe->{$letterDPEG}->tiret_y, 0, $tirets);

                $image->setImageFormat('png');
                $image->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
                if ($this->getGenerateImage() && $this->getPathToWriteImage()) {
                    $imgTemporary = $this->getPathToWriteImage() . ($this->getNameOfPicture() ?: 'dpeg_' . $this->getDpeVal() . '_' . $this->getGesVal()) . '.png';
                    $image->writeImage($imgTemporary);

                    return $imgTemporary;
                }

                return $image;
            }
            throw new Exception('Sorry our JSON is gone away', 500);
        } else {
            throw new Exception('Your value for GES is not correct, please fill in a valid integer', 500);
        }
    }

    /**
     * This function allows you to retrieve the letter of the DPEG according to its value DPE AND iso code GP
     * @return string|null
     */
    private function getLetterDPEGGP(): ?string
    {
        $dpe_cons = $this->getDpeVal();

        if ($dpe_cons > 90) {
            return 'G';
        }
        if ($dpe_cons <= 15) {
            return 'A';
        }
        if ($dpe_cons <= 25) {
            return 'B';
        }
        if ($dpe_cons <= 30) {
            return 'C';
        }
        if ($dpe_cons <= 45) {
            return 'D';
        }
        if ($dpe_cons <= 60) {
            return 'E';
        }
        if ($dpe_cons <= 90) {
            return 'F';
        }

        return null;
    }
    #endregion
}

