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
     * variables JSON pour la gestion du DPEG petites surfaces, législation 2024
     */
    private $jsonSmallSurface;
    private $small_surface_file = __DIR__ . DIRECTORY_SEPARATOR . 'json' . DIRECTORY_SEPARATOR . 'dpeSmallSurfaces.json';



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
     * value of final consumption
     * @var
     */
    private $valFinalConsumption = 0;

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

    /**
     * valeur de la superficie
     *  @var float|null
     */
    private ?float $superficie;


    private $size = self::PRINT_SIZE_TYPE;

    /**
     * Min value for consumption
     * @var int
     */
    private $depensesMin = 0;
    /**
     * MAX value for consumption
     * @var int
     */
    private $depensesMax = 0;
    /**
     * Currency
     * @var string
     */
    private $devise = "€";
    /**
     * Reference years for consuption data
     * @var int
     */
    private $yearRef = 0;


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
        $this->jsonSmallSurface = json_decode(file_get_contents($this->small_surface_file));
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
     * @param int $valFinalConsumption
     */
    public function setValFinalConsumption(int $valFinalConsumption): void
    {
        $this->valFinalConsumption = $valFinalConsumption;
    }

    /**
     * @return mixed
     */
    private function getValFinalConsumption(): int
    {
        return $this->valFinalConsumption;
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

    /**
     * @return int
     */
    public function getDepensesMin()
    {
        return $this->depensesMin;
    }

    /**
     * @param int $depensesMin
     */
    public function setDepensesMin($depensesMin)
    {
        $this->depensesMin = $depensesMin;
    }

    /**
     * @return int
     */
    public function getDepensesMax()
    {
        return $this->depensesMax;
    }

    /**
     * @param int $depensesMax
     */
    public function setDepensesMax($depensesMax)
    {
        $this->depensesMax = $depensesMax;
    }

    /**
     * @return string
     */
    public function getDevise()
    {
        return $this->devise;
    }

    /**
     * @param string $devise
     */
    public function setDevise($devise)
    {
        $this->devise = $devise;
    }

    /**
     * @return int
     */
    public function getYearRef()
    {
        return $this->yearRef;
    }

    /**
     * @param int $yearRef
     */
    public function setYearRef($yearRef)
    {
        $this->yearRef = $yearRef;
    }

    /**
     * @param float|null $superficie
     */
    public function setSuperficie(?float $superficie): void
    {
        $this->superficie = $superficie;
    }

    /**
     * @return float|null
     */
    public function getSuperficie(): ?float
    {
        return $this->superficie;
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

                $draw = new ImagickDraw();
                $draw->setStrokeColor('black');
                $draw->setFillColor('black');
                $draw->setFont(__DIR__ . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . 'arial.ttf');
                $draw->setStrokeWidth(1);
                $draw->setFontSize($fontValue);
                $image->annotateimage($draw, $x_dpe_val, $dpeConf->val, 0, $this->getDpeVal());

                $draw = new ImagickDraw();
                $draw->setStrokeColor('black');
                $draw->setFillColor('black');
                $draw->setFont(__DIR__ . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . 'arial.ttf');
                $draw->setStrokeWidth(1);
                $draw->setFontSize($fontText);
                $image->annotateimage($draw, $x_dpe_text, $dpeConf->text, 0, self::KWH_M2);

                $draw = new ImagickDraw();
                $draw->setStrokeColor('black');
                $draw->setFillColor('black');
                $draw->setFont(__DIR__ . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . 'arial.ttf');
                $draw->setStrokeWidth(1);
                $draw->setFontSize($fontValue);
                $image->annotateimage($draw, $x_ges_val, $dpeConf->val, 0, $this->getGesVal());

                $draw = new ImagickDraw();
                $draw->setStrokeColor('black');
                $draw->setFillColor('black');
                $draw->setFont(__DIR__ . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . 'arial.ttf');
                $draw->setStrokeWidth(1);
                $draw->setFontSize($fontText);
                $image->annotateimage($draw, $x_ges_text, $dpeConf->text, 0, self::KG_CO2_M2);


                $draw = new ImagickDraw();
                $draw->setStrokeColor('black');
                $draw->setFillColor('black');
                $draw->setFont(__DIR__ . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . 'arial.ttf');
                $draw->setStrokeWidth(1);
                $draw->setFontSize($fontText);
                $image->annotateimage($draw, $x_ges_text + 10, $dpeConf->text - 80, 0, "émissions");

                $draw = new ImagickDraw();
                $draw->setStrokeColor('black');
                $draw->setFillColor('black');
                $draw->setFont(__DIR__ . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . 'arial.ttf');
                $draw->setStrokeWidth(1);
                $draw->setFontSize($fontText);
                $image->annotateimage($draw, $x_dpe_text - 10, $dpeConf->text - 95, 0, "consommation");

                $draw = new ImagickDraw();
                $draw->setStrokeColor('grey');
                $draw->setFillColor('grey');
                $draw->setFont(__DIR__ . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . 'arial.ttf');
                $draw->setStrokeWidth(1);
                $draw->setFontSize($fontText);
                $image->annotateimage($draw, $x_dpe_text - 20, $dpeConf->text - 80, 0, "(énergie primaire)");

                if ($this->getValFinalConsumption() && $this->getValFinalConsumption() > 0) {
                    $draw = new ImagickDraw();
                    $draw->setStrokeColor('grey');
                    $draw->setFillColor('grey');
                    $draw->setFont(__DIR__ . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . 'arial.ttf');
                    $draw->setStrokeWidth(1);
                    $draw->setFontSize($fontText);
                    $image->annotateimage($draw, $x_dpe_text - 13, $dpeConf->text + 25, 0, $this->getValFinalConsumption() . " kWh/m2/an");

                    $draw = new ImagickDraw();
                    $draw->setStrokeColor('grey');
                    $draw->setFillColor('grey');
                    $draw->setFont(__DIR__ . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . 'arial.ttf');
                    $draw->setStrokeWidth(1);
                    $draw->setFontSize($fontText);
                    $image->annotateimage($draw, $x_dpe_text - 15, $dpeConf->text + 35, 0, "d'énergie finale");
                }


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

                $draw = new ImagickDraw();
                $draw->setStrokeColor('black');
                $draw->setFillColor('black');
                $draw->setFont(__DIR__ . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . 'arial.ttf');
                $draw->setStrokeWidth(1);
                $draw->setFontSize(60);
                $image->annotateimage($draw, $gesConf->ges_val, $gesConf->x_val, 0, $this->getGesVal());

                $draw = new ImagickDraw();
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
        }
        throw new Exception('Your value for GES is not correct, please fill in a valid integer', 500);
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
        $superficie = $this->getSuperficie();

        if ($superficie && $superficie <= 40) {
            return $this->getLetterDPEGSmallSurface();
        }

        if ($dpe_cons < 70 && $dpe_ges < 6) {
            return 'A';
        }
        if ($dpe_cons < 110 && $dpe_ges < 11) {
            return 'B';
        }
        if ($dpe_cons < 180 && $dpe_ges < 30) {
            return 'C';
        }
        if ($dpe_cons < 250 && $dpe_ges < 55) {
            return 'D';
        }
        if ($isDpeAltitude) {
            if ($dpe_cons < 390 && $dpe_ges < 80) {
                return 'E';
            }
            if ($dpe_cons < 500 && $dpe_ges < 110) {
                return 'F';
            }
            if ($dpe_cons >= 500 || $dpe_ges >= 110) {
                return 'G';
            }
        }
        if ($dpe_cons < 330 && $dpe_ges < 70) {
            return 'E';
        }
        if ($dpe_cons < 420 && $dpe_ges < 100) {
            return 'F';
        }
        if ($dpe_cons >= 420 || $dpe_ges >= 100) {
            return 'G';
        }

        return null;
    }

    /**
     * This function allows you to retrieve the letter of the DPEG according to its value DPE
     * @return string|null
     */
    public function getNewLetterDPE(): ?string
    {
        $dpe_cons = $this->getDpeVal();
        $isDpeAltitude = $this->getIsDpeAltitude();
        $superficie = $this->getSuperficie();

        if ($superficie && $superficie <= 40) {
            return $this->getNewLetterDPESmallSurface();
        }
        if ($dpe_cons < 70) {
            return 'A';
        }
        if ($dpe_cons < 110) {
            return 'B';
        }
        if ($dpe_cons < 180) {
            return 'C';
        }
        if ($dpe_cons < 250) {
            return 'D';
        }
        if ($isDpeAltitude) {
            if ($dpe_cons < 390) {
                return 'E';
            }
            if ($dpe_cons < 500) {
                return 'F';
            }

            return 'G'; // >= 500
        }
        if ($dpe_cons < 330) {
            return 'E';
        }
        if ($dpe_cons < 420) {
            return 'F';
        }

        return 'G'; // >= 420
    }

    /**
     * This function allows you to retrieve the letter of the GES according to its value of GES only
     * @return string|null
     */
    public function getNewLetterGES(): ?string
    {
        $dpe_ges = $this->getGesVal();
        $isDpeAltitude = $this->getIsDpeAltitude();
        $superficie = $this->getSuperficie();

        if ($superficie && $superficie <= 40) {
            return $this->getNewLetterGESSmallSurface();
        }

        if ($dpe_ges < 6) {
            return 'A';
        }
        if ($dpe_ges < 11) {
            return 'B';
        }
        if ($dpe_ges < 30) {
            return 'C';
        }
        if ($dpe_ges < 50) {
            return 'D';
        }
        if ($isDpeAltitude) {
            if ($dpe_ges < 80) {
                return 'E';
            }
            if ($dpe_ges < 110) {
                return 'F';
            }

            return 'G'; // > 110
        }
        if ($dpe_ges < 70) {
            return 'E';
        }
        if ($dpe_ges < 100) {
            return 'F';
        }

        return 'G'; // > 100
    }


    public function getLegalMention()
    {
        $depensesMin = $this->depensesMin;
        $devise = $this->devise;
        $depensesMax = $this->depensesMax;
        $yearRef = $this->yearRef;

        if ($depensesMin && $devise && $depensesMax && $yearRef) {
            $string = <<<HTML
 Montant estimé des dépenses annuelles d'énergie de ce logement pour un usage standard est compris entre {$depensesMin}{$devise} et {$depensesMax}{$devise}. {$yearRef} étant l'année de référence des prix de l'énergie utilisés pour établir cette estimation.
HTML;
            if ($this->dpeVal > 330) {
                $string .= <<<HTML
<br>Logement à consommation énergétique excessive.
HTML;
            }

            return $string;
        }

        return null;
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

                $draw = new ImagickDraw();
                $draw->setStrokeColor('white');
                $draw->setFillColor('white');
                $draw->setFont(__DIR__ . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . 'arial.ttf');
                $draw->setStrokeWidth(1);
                $draw->setFontSize(40);
                $image->annotateimage($draw, $this->json->dpe->{$letterDPEG}->value_x, $this->json->dpe->{$letterDPEG}->value_y, 0, $this->getDpeVal());
                $draw = new ImagickDraw();
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
        }
        throw new Exception('Your value for GES is not correct, please fill in a valid integer', 500);
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

    /**
     * Fonction permettant le calcul de la lettre DPE pour les petites surfaces depuis la nouvelle législation de 2024
     * 
     * @return string
     */
    private function getLetterDPEGSmallSurface(): ?string
    {
        $superficieInt = $this->getSuperficie() < 8 ? 8 : (int)floor($this->getSuperficie());
        $dpeCons = $this->getDpeVal();
        $gesCons = $this->getGesVal();
        $smallSurfaceObj = $this->jsonSmallSurface->standard;

        if (property_exists($smallSurfaceObj,$superficieInt)) {
            //superficie entiere ou égale à 40 => pas d'interpolation
            if ($superficieInt === 40 || (floor($this->getSuperficie()) === $this->getSuperficie())) {
                $referenciel = $smallSurfaceObj->{$superficieInt};
                if ($this->isDpeAltitude) {
                    $altitudeObj = $this->jsonSmallSurface->altitude->{$superficieInt};
                    $referenciel->CEP_e = $altitudeObj->CEP_e;
                    $referenciel->EGES_e = $altitudeObj->EGES_e;
                    $referenciel->CEP_f = $altitudeObj->CEP_f;
                    $referenciel->EGES_f = $altitudeObj->EGES_f;
                }
            } else {
                $referenciel = $this->getReferentielByInterpolation($this->getSuperficie());
            }

            if ($dpeCons < $referenciel->CEP_a && $gesCons < $referenciel->EGES_a) {
                return "A";
            }

            $conditions = [
                "B" => [
                    [$dpeCons >= $referenciel->CEP_a, $dpeCons < $referenciel->CEP_b, $gesCons < $referenciel->EGES_b],
                    [$gesCons >= $referenciel->EGES_a, $gesCons < $referenciel->EGES_b, $dpeCons < $referenciel->CEP_b],
                ],
                "C" => [
                    [$dpeCons >= $referenciel->CEP_b, $dpeCons < $referenciel->CEP_c, $gesCons < $referenciel->EGES_c],
                    [$gesCons >= $referenciel->EGES_b, $gesCons < $referenciel->EGES_c, $dpeCons < $referenciel->CEP_c],
                ],
                "D" => [
                    [$dpeCons >= $referenciel->CEP_c, $dpeCons < $referenciel->CEP_d, $gesCons < $referenciel->EGES_d],
                    [$gesCons >= $referenciel->EGES_c, $gesCons < $referenciel->EGES_d, $dpeCons < $referenciel->CEP_d],
                ],
                "E" => [
                    [$dpeCons >= $referenciel->CEP_d, $dpeCons < $referenciel->CEP_e, $gesCons < $referenciel->EGES_e],
                    [$gesCons >= $referenciel->EGES_d, $gesCons < $referenciel->EGES_e, $dpeCons < $referenciel->CEP_e],
                ],
                "F" => [
                    [$dpeCons >= $referenciel->CEP_e, $dpeCons < $referenciel->CEP_f, $gesCons < $referenciel->EGES_f],
                    [$gesCons >= $referenciel->EGES_e, $gesCons < $referenciel->EGES_f, $dpeCons < $referenciel->CEP_f],
                ]
            ];

            foreach ($conditions as $letter => $conds) {
                foreach ($conds as $cond) {
                    if ($cond[0] && $cond[1] && $cond[2]) {
                        return $letter;
                    }
                }
            }

            return "G";
        }

        return "";
    }

    /**
     * @return string
     */
    private function getNewLetterGESSmallSurface(): string
    {
        $ges = $this->getGesVal();
        $superficieInt = $this->getSuperficie() < 8 ? 8 : (int)floor($this->getSuperficie());
        $smallSurfaceObj = $this->jsonSmallSurface->standard;

        if (property_exists($smallSurfaceObj, $superficieInt)) {
            //superficie entiere ou égale à 40 => pas d'interpolation
            if ($superficieInt === 40 || (floor($this->getSuperficie()) === $this->getSuperficie())) {
                $referenciel = $smallSurfaceObj->{$superficieInt};
                if ($this->isDpeAltitude) {
                    $altitudeObj = $this->jsonSmallSurface->altitude->{$superficieInt};
                    $referenciel->EGES_e = $altitudeObj->EGES_e;
                    $referenciel->EGES_f = $altitudeObj->EGES_f;
                }
            } else {
                $referenciel = $this->getReferentielByInterpolation($this->getSuperficie());
            }

            if ($ges < $referenciel->EGES_a) {
                return 'A';
            }
            if ($ges <= $referenciel->EGES_b) {
                return 'B';
            }
            if ($ges <= $referenciel->EGES_c) {
                return 'C';
            }
            if ($ges <= $referenciel->EGES_d) {
                return 'D';
            }
            if ($ges <= $referenciel->EGES_e) {
                return 'E';
            }
            if ($ges <= $referenciel->EGES_f) {
                return 'F';
            }

            return 'G';
        }
        return "";
    }

    /**
     * @return string
     */
    private function getNewLetterDPESmallSurface(): string
    {
        $dpe = $this->getDpeVal();
        $superficieInt = $this->getSuperficie() < 8 ? 8 : (int)floor($this->getSuperficie());
        $smallSurfaceObj = $this->jsonSmallSurface->standard;

        if (property_exists($smallSurfaceObj, $superficieInt)) {
            //superficie entiere ou égale à 40 => pas d'interpolation
            if ($superficieInt === 40 || (floor($this->getSuperficie()) === $this->getSuperficie())) {
                $referenciel = $smallSurfaceObj->{$superficieInt};
                if ($this->isDpeAltitude) {
                    $altitudeObj = $this->jsonSmallSurface->altitude->{$superficieInt};
                    $referenciel->CEP_e = $altitudeObj->CEP_e;
                    $referenciel->CEP_f = $altitudeObj->CEP_f;
                }
            } else {
                $referenciel = $this->getReferentielByInterpolation($this->getSuperficie());
            }

            if ($dpe < $referenciel->CEP_a) {
                return 'A';
            }
            if ($dpe <= $referenciel->CEP_b) {
                return 'B';
            }
            if ($dpe <= $referenciel->CEP_c) {
                return 'C';
            }
            if ($dpe <= $referenciel->CEP_d) {
                return 'D';
            }
            if ($dpe <= $referenciel->CEP_e) {
                return 'E';
            }
            if ($dpe <= $referenciel->CEP_f) {
                return 'F';
            }
            return 'G';
        }
        return "";
    }

    /**
     * calcul un nouveau référentiel par interpolation linéraire
     * @param float|null $superficie
     * @return object
     */
    private function getReferentielByInterpolation(?float $superficie): object
    {
        $lowValue = (int)floor($superficie);
        $highValue = (int)ceil($superficie);
        $lowReferentiel = $this->jsonSmallSurface->standard->{$lowValue};
        $highReferentiel = $this->jsonSmallSurface->standard->{$highValue};
        if ($this->isDpeAltitude) {
            $lowReferentiel->CEP_e = $this->jsonSmallSurface->altitude->{$lowValue}->CEP_e;
            $lowReferentiel->EGES_e = $this->jsonSmallSurface->altitude->{$lowValue}->EGES_e;
            $lowReferentiel->CEP_f = $this->jsonSmallSurface->altitude->{$lowValue}->CEP_f;
            $lowReferentiel->EGES_f = $this->jsonSmallSurface->altitude->{$lowValue}->EGES_f;

            $highReferentiel->CEP_e = $this->jsonSmallSurface->altitude->{$highValue}->CEP_e;
            $highReferentiel->EGES_e = $this->jsonSmallSurface->altitude->{$highValue}->EGES_e;
            $highReferentiel->CEP_f = $this->jsonSmallSurface->altitude->{$highValue}->CEP_f;
            $highReferentiel->EGES_f = $this->jsonSmallSurface->altitude->{$highValue}->EGES_f;
        }
        $newReferentiel = new \stdClass();
        $referentielKeys = ['CEP_a', 'EGES_a', 'CEP_b', 'EGES_b', 'CEP_c', 'EGES_c', 'CEP_d', 'EGES_d', 'CEP_e', 'EGES_e', 'CEP_f', 'EGES_f'];
        foreach ($referentielKeys as $key) {
            $lowReferentielCeiling = $lowReferentiel->{$key};
            $highReferentielCeiling = $highReferentiel->{$key};
            $newReferentiel->{$key} = $lowReferentielCeiling + ($highReferentielCeiling - $lowReferentielCeiling) * (($superficie - $lowValue) / ($highValue - $lowValue));
        }

        return $newReferentiel;
    }
    #endregion
}

