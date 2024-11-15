# DPE Generator

DPE Generator is a library that allows you to quickly generate an image of the new DPE and GES. If you generate DPEG
picture for Guadaloupe (GP), you must add GP value in construct.

Available on https://packagist.org/packages/lbigroupp/dpegenerator

### list of available functions (and their type)

```php 
(boolean) setGenerateImage 
```

```php 
(string) setPathToWriteImage 
```

```php 
(string) setNameOfPicture 
```

```php 
(string) setPictureType 
```

```php 
(int) setDpeVal 
```

```php 
(int) setGesVal 
```

```php
(int) setSuperficie 
```

### List of available constants

It's constants allow you to define the type of image you want DPE or GES.

```php 
const DPE_TYPE;
```

```php 
const GES_TYPE;
```

#### Example for generate picture on your personal folder

```php
$type = \LBIGroupDpeGenerator\DpeGenerator::DPE_TYPE; 
// OR $type = \LBIGroupDpeGenerator\DpeGenerator::GES_TYPE
$dpeVal = 29;
$gesVal = 2;
$superficie = 35;
$imgTarget = "YOUR_TARGET";
$pictureName = "YOUR_PICTURE_NAME";

if (file_exists($imgTarget . $pictureName . '.png')) {
    return $imgTarget . $pictureName . '.png';
}

$dpe = new \LBIGroupDpeGenerator\DpeGenerator();
$dpe->setDpeVal($dpeVal);
$dpe->setGesVal($gesVal);
$dpe->setSuperficie($superficie);
$dpe->setPictureType($type);
$dpe->setPathToWriteImage($imgTarget);
$dpe->setNameOfPicture($pictureName);
$dpe->setGenerateImage(true);

// return file location
echo $dpe->generatePicture();
```

#### Example for see picture direcly on your website

```php
$type = \LBIGroupDpeGenerator\DpeGenerator::DPE_TYPE; 
// OR $type = \LBIGroupDpeGenerator\DpeGenerator::GES_TYPE
$dpeVal = 29;
$gesVal = 2;
$superficie = 35;

$dpe = new \LBIGroupDpeGenerator\DpeGenerator();
$dpe->setDpeVal($dpeVal);
$dpe->setGesVal($gesVal);
$dpe->setSuoerficie($superficie);
$dpe->setPictureType($type);

// return file location
echo $dpe->generatePicture();
```

## Release Notes

`v1.0` / `v1.1` / `v1.1` : it's not a stable versions ;

``1.2.1`` : stable version for all PHP versions (5, 7 and 8) ;

``2.0.1`` : stable version ONLY for PHP >= 7.1 ;

``2.1`` : addition DPEG for Guadeloupe via iso code GP;

``2.3`` : addition Final consuption and PHP =>7.4;
