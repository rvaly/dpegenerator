<?php

namespace LBIGroupDpeGenerator;


use Imagick;
use ImagickDraw;

class DpeGenerator
{

    private $json;
    private $pictTarget = null;
    private $pictName = null;
    private $generateImage = false;
    private $type = 'dpe';
    private $dpeVal;
    private $gesVal;

    public function __construct()
    {
        $this->json = json_decode(file_get_contents(__DIR__ . '/dpe.json'));
    }

    public function setGenerateImage($generateImage)
    {
        $this->generateImage = $generateImage;
    }

    private function getGenerateImage()
    {
        return $this->generateImage;
    }

    public function setPathToWriteImage($path)
    {
        $this->pictTarget = $path;
    }

    private function getPathToWriteImage()
    {
        return $this->pictTarget;
    }

    public function setNameOfPicture($pictname)
    {
        $this->pictName = $pictname;
    }

    private function getNameOfPicture()
    {
        return $this->pictName;
    }

    public function setPictureType($type)
    {
        $this->type = $type;
    }

    private function getPictureType()
    {
        return $this->type;
    }

    public function setDpeVal($dpeVal)
    {
        $this->dpeVal = $dpeVal;
    }

    private function getDpeVal()
    {
        return $this->dpeVal;
    }

    public function setGesVal($gesVal)
    {
        $this->gesVal = $gesVal;
    }

    private function getGesVal()
    {
        return $this->gesVal;
    }

    /**
     * @return \Imagick|string|null
     * @throws \ImagickDrawException
     * @throws \ImagickException
     */
    private function generateImgDpe()
    {
        if ($letterDpe = $this->getNewLetterDPEG()) {
            if ($this->json->dpe->{$letterDpe}) {
                $image = new Imagick(__DIR__ . '/images/' . $this->json->dpe->{$letterDpe}->img);
                $draw = new ImagickDraw();
                $draw->setFontSize(90);
                $draw->annotation(90, $this->json->dpe->{$letterDpe}->dpe_val, $this->getDpeVal());
                $draw->setFontSize(25);
                $draw->annotation(95, $this->json->dpe->{$letterDpe}->dpe_text, "kWh/m².an");
                $draw->setFontSize(90);
                $draw->annotation(290, $this->json->dpe->{$letterDpe}->ges_val, $this->getGesVal());
                $draw->setFontSize(25);
                $draw->annotation(296, $this->json->dpe->{$letterDpe}->ges_text, "kgCO2/m².an");
                $image->setImageFormat('png');
                $image->drawImage($draw);
                if ($this->getGenerateImage() && $this->getPathToWriteImage()) {
                    $imgTemporary = $this->getPathToWriteImage() . ($this->getNameOfPicture() ? $this->getNameOfPicture() : 'dpeg_' . $this->getDpeVal() . '_' . $this->getGesVal()) . '.png';
                    $image->writeImage($imgTemporary);

                    return $imgTemporary;
                }

                return $image;

            }
        }

        return null;
    }

    private function generateImgGes()
    {
        if ($letterGes = $this->getNewLetterGES()) {
            if ($this->json->ges->{$letterGes}) {
                $image = new Imagick(__DIR__ . '/images/' . $this->json->ges->{$letterGes}->img);
                $draw = new ImagickDraw();
                $draw->setFontSize(60);
                $draw->annotation($this->json->ges->{$letterGes}->ges_val, $this->json->ges->{$letterGes}->x_val, $this->getGesVal());
                $draw->setFontSize(15);
                $draw->annotation($this->json->ges->{$letterGes}->ges_text, $this->json->ges->{$letterGes}->x_val, "kgCO2/m².an");
                $image->drawImage($draw);
                $image->setImageFormat('png');
                $image->cropImage(475, 530, 80, 220);
                if ($this->getGenerateImage() && $this->getPathToWriteImage()) {
                    $imgTemporary = $this->getPathToWriteImage() . ($this->getNameOfPicture() ? $this->getNameOfPicture() : 'ges_' . $this->getGesVal()) . '.png';
                    $image->writeImage($imgTemporary);

                    return $imgTemporary;
                }

                return $image;
            }
        }

        return null;
    }

    private function getNewLetterDPEG()
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

    private function getNewLetterGES()
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

    public function generatePicture()
    {
        if ($this->getPictureType() === 'dpe') {
            return $this->generateImgDpe();
        }

        return $this->generateImgGes();

    }
}