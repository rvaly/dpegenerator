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
     * constant to define the type
     */
    public const DPE_TYPE = 'dpe';
    public const GES_TYPE = 'ges';

    private const KG_CO2_M2 = 'kgCO2/m².an';
    private const KWH_M2 = 'kWh/m².an';

    /**
     * DpeGenerator constructor.
     */
    public function __construct()
    {
        $this->json = json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'dpe.json'));
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
     * DPE image generation function
     * @return \Imagick|string
     * @throws \ImagickDrawException
     * @throws \ImagickException
     * @throws \Exception
     */
    private function generateImgDpe()
    {
        if ($letterDpe = $this->getNewLetterDPEG()) {
            if ($this->json->dpe->{$letterDpe}) {
                $image = new Imagick(__DIR__ . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $this->json->dpe->{$letterDpe}->img);
                $draw = new ImagickDraw();
                $draw->setFontSize(90);
                $draw->annotation(90, $this->json->dpe->{$letterDpe}->dpe_val, $this->getDpeVal());
                $draw->setFontSize(25);
                $draw->annotation(95, $this->json->dpe->{$letterDpe}->dpe_text, self::KWH_M2);
                $draw->setFontSize(90);
                $draw->annotation(290, $this->json->dpe->{$letterDpe}->ges_val, $this->getGesVal());
                $draw->setFontSize(25);
                $draw->annotation(296, $this->json->dpe->{$letterDpe}->ges_text, self::KG_CO2_M2);
                $image->setImageFormat('png');
                $image->drawImage($draw);
                if ($this->getGenerateImage() && $this->getPathToWriteImage()) {
                    $imgTemporary = $this->getPathToWriteImage() . ($this->getNameOfPicture() ?: 'dpeg_' . $this->getDpeVal() . '_' . $this->getGesVal()) . '.png';
                    $image->writeImage($imgTemporary);

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
            if ($this->json->ges->{$letterGes}) {
                $image = new Imagick(__DIR__ . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $this->json->ges->{$letterGes}->img);
                $draw = new ImagickDraw();
                $draw->setFontSize(60);
                $draw->annotation($this->json->ges->{$letterGes}->ges_val, $this->json->ges->{$letterGes}->x_val, $this->getGesVal());
                $draw->setFontSize(15);
                $draw->annotation($this->json->ges->{$letterGes}->ges_text, $this->json->ges->{$letterGes}->x_val, self::KG_CO2_M2);
                $image->drawImage($draw);
                $image->setImageFormat('png');
                $image->cropImage(475, 530, 80, 220);
                if ($this->getGenerateImage() && $this->getPathToWriteImage()) {
                    $imgTemporary = $this->getPathToWriteImage() . ($this->getNameOfPicture() ?: 'ges_' . $this->getGesVal()) . '.png';
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
     * This function allows you to retrieve the letter of the DPEG according to its value DPE AND GES
     * @return string|null
     */
    private function getNewLetterDPEG(): ?string
    {
        $dpe_cons = $this->getDpeVal();
        $dpe_ges = $this->getGesVal();
        if ($dpe_cons > 420 || $dpe_ges > 100) {
            return 'G';
        }
        if ($dpe_cons <= 70 && $dpe_ges <= 6) {
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
        if ($dpe_cons <= 330 && $dpe_ges <= 70) {
            return 'E';
        }
        if ($dpe_cons <= 420 && $dpe_ges <= 100) {
            return 'F';
        }

        return null;
    }

    /**
     * This function allows you to retrieve the letter of the GES according to its value of GES only
     * @return string|null
     */
    private function getNewLetterGES(): ?string
    {
        $dpe_ges = $this->getGesVal();

        if ($dpe_ges > 100) {
            return 'G';
        }
        if ($dpe_ges <= 6) {
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
        if ($dpe_ges <= 70) {
            return 'E';
        }
        if ($dpe_ges <= 100) {
            return 'F';
        }

        return null;
    }

    /**
     * This function allows you to launch the generation of your image according to the parameters entered
     * @return \Imagick|string|null
     * @throws \ImagickDrawException
     * @throws \ImagickException
     */
    public function generatePicture()
    {
        try {
            if ($this->getPictureType() === 'dpe') {
                return $this->generateImgDpe();
            }

            return $this->generateImgGes();
        } catch (Exception $exception) {
            throw $exception;
        }
    }

}