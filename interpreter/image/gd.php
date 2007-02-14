<?php

/**
 * KaForkL GD image reader
 * 
 * @package kaforkl
 * @version $id$
 * @copyright 2007 Kore Nordmann
 * @author Kore Nordmann <kore@php.net>
 */
class kaforkl_Image_GD extends kaforkl_Image
{
    /**
     * Fill up value array from image
     * 
     * @param string $fileName 
     */
    protected function readFile( $fileName )
    {
        $imageData = getimagesize( $fileName );

        switch ( $imageData[2] )
        {
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng( $fileName );
                break;
            default:
                throw new Exception( 'Unsupported image type.' );
        }

        $this->width = $imageData[0];
        $this->height = $imageData[1];

        for ( $x = 0; $x < $this->width; ++$x )
        {
            for ( $y = 0; $y < $this->height; ++$y )
            {
                $color = imagecolorat( $image, $x, $y );

                $this->valueArray[$x][$y] = array(
                    kaforkl_Image::RED  => ( $color & 0xFF0000 ) >> 16,
                    kaforkl_Image::GREEN  => ( $color & 0xFF00 ) >> 8,
                    kaforkl_Image::BLUE  => $color & 0xFF,
                    kaforkl_Image::ALPHA  => ( $color & 0xFF000000 ) >> 24,
                );
            }
        }
    }

    /**
     * Fill up value array from image
     * 
     * @param string $fileName 
     */
    protected function writeFile( $fileName )
    {
        $image = imagecreatetruecolor( $this->width, $this->height );
        imagesavealpha( $image, true );

        for ( $x = 0; $x < $this->width; ++$x )
        {
            for ( $y = 0; $y < $this->height; ++$y )
            {
                $color = imagecolorallocatealpha( 
                    $image,
                    $this->valueArray[$x][$y][kaforkl_Image::RED],
                    $this->valueArray[$x][$y][kaforkl_Image::GREEN],
                    $this->valueArray[$x][$y][kaforkl_Image::BLUE],
                    $this->valueArray[$x][$y][kaforkl_Image::ALPHA]
                );
                imagesetpixel( $image, $x, $y, $color );
            }
        }

        imagepng( $image, $fileName );
    }
}
