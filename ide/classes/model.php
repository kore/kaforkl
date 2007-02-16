<?php

require_once 'interpreter/image.php';

/**
 * KaForkL IDE image model
 * 
 * @package kaforkl
 * @version $id$
 * @copyright 2007 Kore Nordmann
 * @author Kore Nordmann <kore@php.net>
 */
class kaforkl_ImageModel extends kaforkl_Image
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

        $this->updateWidget();
    }

    /**
     * Fill up value array from image
     * 
     * @param string $fileName 
     */
    protected function writeFile( $fileName )
    {
        // Not yet implemented
    }

    public function updateWidget()
    {
        $widget = kaforkl_IdeMain::$glade->get_widget( 'drawingarea' );

        // Gett zoom factor
        $zoom = kaforkl_IdeMain::$glade->get_widget( 'zoom' )->get_active_text();
        if ( $zoom === null )
        {
            $zoom = 5;
        }
        else
        {
            $zoom = (int) $zoom / 100;
        }

        // Create image to display with gd
        $image = imagecreatetruecolor( $this->width * $zoom, $this->height *$zoom );
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

                if ( $zoom === 1 )
                {
                    imagesetpixel( $image, $x, $y, $color );
                }
                else
                {
                    imagefilledrectangle( 
                        $image,
                        $x * $zoom, $y * $zoom,
                        $x * $zoom + $zoom - 1, $y * $zoom + $zoom - 1,
                        $color
                    );
                }
            }
        }

        imagepng( $image, $tmpFile = dirname( __FILE__ ) . '/../data/tmp.png' );
        $widget->set_from_file( $tmpFile );
    }

    /**
     * Main run method
     *
     * Starts processing
     */
    public function run( $x = 0, $y = 0 )
    {
        // Create initial processor
        $this->addProcessor(
            new kaforkl_Context(
                $this,
                new kaforkl_Position( 
                    $this->width,
                    $this->height,
                    $x,
                    $y
                )
            )
        );

        // Start processing
        do {
            if ( DEBUG )
            {
                echo "\nNext step:\n";
            }

            foreach ( $this->processors as $nr => $context )
            {
                // Test for max step count
                if ( ( $this->maxStepCount !== false ) && ( $nr >= $this->maxStepCount ) )
                {
                    break 2;
                }

                if ( DEBUG )
                {
                    printf( "Running %d\n", $nr );
                }

                // Process current processor
                $position = $context->getPosition();
                call_user_func_array( 
                    array( $context, 'process' ),
                    $this->valueArray[$position->getX()][$position->getY()]
                );

                // Processor fork or dies
                unset( $this->processors[$nr] );
            }
        } while ( count( $this->processors ) );
    }
}
