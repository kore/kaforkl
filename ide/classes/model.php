<?php

require_once 'interpreter/image.php';
require_once 'interpreter/context.php';
require_once 'interpreter/position.php';

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
     * Direction checkbox association
     * 
     * @var array
     */
    protected static $redCheckBoxes = array(
        1 => 'red_value_n',
        2 => 'red_value_ne',
        4 => 'red_value_e',
        8 => 'red_value_se',
        16 => 'red_value_s',
        32 => 'red_value_sw',
        64 => 'red_value_w',
        128 => 'red_value_nw',
    );

    /**
     * Selected pixel 
     * 
     * @var null
     */
    protected $xSelected;

    /**
     * Selected pixel 
     * 
     * @var null
     */
    protected $ySelected;

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
     * Write back to file
     *
     * We use imagemagick here, because libgd produces inaccurate results
     * 
     * @param string $fileName 
     */
    public function writeFile( $fileName )
    {
        $width = $this->width;
        $height = $this->height;
        $data = $this->valueArray;

        shell_exec( "convert -size {$width}x{$height} xc:transparent -type TrueColorMatte $fileName" );

        // Default background to black
        for ( $x = 0; $x < $width; ++$x )
        {
            $cmd = "convert $fileName";
            for ( $y = 0; $y < $height; ++$y )
            {
                // Fix alpha value for imagick
                $data[$x][$y][kaforkl_Image::ALPHA] = str_replace( ',', '.', 1 - ( $data[$x][$y][kaforkl_Image::ALPHA] / 127 ) );

                if ( isset( $data[$x][$y] ) )
                {
                    $cmd .= " -fill \"rgba(" . implode( ',', $data[$x][$y] ) . ")\" -draw \"point {$x},{$y}\"";
                }
                else
                {
                    $cmd .= " -fill \"rgba(0,0,0,0)\" -draw \"point {$x},{$y}\"";
                }
            }
            $cmd .= ' ' . $fileName;
            shell_exec( $cmd );
        }
    }

    /**
     * Returns the current zoom multiplier
     * 
     * @return int
     */
    protected function getCurrentZoomFactor()
    {
        $zoom = kaforkl_IdeMain::$glade->get_widget( 'zoom' )->get_active_text();
        if ( $zoom === null )
        {
            return 10; // Default zoom
        }
        else
        {
            return (int) $zoom / 100;
        }
    }

    public function updateWidget()
    {
        // Recreate image to display with gd
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

        imagepng( $image, $tmpFile = dirname( __FILE__ ) . '/../data/tmp.png' );

        // Get zoom factor
        $zoom = $this->getCurrentZoomFactor();

        // Scale using pixbuf scaling
        $pixbuf = GDKPixbuf::new_from_file( $tmpFile );
        $pixbuf = $pixbuf->scale_simple( $this->width * $zoom, $this->height * $zoom, Gdk::INTERP_TILES );

        $widget = kaforkl_IdeMain::$glade->get_widget( 'drawingarea' );
        $widget->set_from_pixbuf( $pixbuf );
    }

    /**
     * Evaluate click and select the clicked pixel and display its values
     * 
     * @param GtkWidget $widget 
     * @param GtkEvent $event 
     * @return void
     */
    public function selectPixel( $widget, $event )
    {
        $viewport = kaforkl_IdeMain::$glade->get_widget( 'drawing_viewport' );
        $widgetSize = $viewport->get_allocation();

        $zoom = $this->getCurrentZoomFactor();
        $width = $this->width * $zoom;
        $height = $this->height * $zoom;

        $xOffset = max( 0, ( $widgetSize->width - $width ) / 2 ) - $widget->get_hadjustment()->value;
        $yOffset = max( 0, ( $widgetSize->height - $height ) / 2 ) - $widget->get_vadjustment()->value;

        $this->xSelected = floor( ( $event->x - $xOffset ) / $zoom );
        $this->ySelected = floor( ( $event->y - $yOffset ) / $zoom );

        if ( isset( $this->valueArray[$this->xSelected] ) 
          && isset( $this->valueArray[$this->xSelected][$this->ySelected] ) )
        {
            $values = $this->valueArray[$this->xSelected][$this->ySelected];

            $green = kaforkl_IdeMain::$glade->get_widget( 'green_value' );
            $green->set_value( $values[kaforkl_Image::GREEN] );

            $blue = kaforkl_IdeMain::$glade->get_widget( 'blue_value' );
            $blue->set_value( $values[kaforkl_Image::BLUE] );

            $alpha = kaforkl_IdeMain::$glade->get_widget( 'alpha_value' );
            $alpha->set_value( $values[kaforkl_Image::ALPHA] );

            // Update all direction checkboxes
            for ( $i = 1; $i < 256; $i = $i << 1 )
            {
                $checkbox = kaforkl_IdeMain::$glade->get_widget( self::$redCheckBoxes[$i] );
                $checkbox->set_active( $values[kaforkl_Image::RED] & $i );
            }
        }
    }

    /**
     * Pixel information were update - update model and GUI
     * 
     * @param int $color 
     * @param int $direction 
     * @return void
     */
    public function updatePixel( $color, $direction = 0 )
    {
        switch ( $color )
        {
            case kaforkl_Image::RED:
                $value = 0;
                for ( $i = 1; $i < 256; $i = $i << 1 )
                {
                    $checkbox = kaforkl_IdeMain::$glade->get_widget( self::$redCheckBoxes[$i] );
                    if ( $checkbox->get_active() )
                    {
                        $value |= $i;
                    }
                }

                $this->valueArray[$this->xSelected][$this->ySelected][kaforkl_Image::RED] = $value;
                break;
            case kaforkl_Image::GREEN:
                $green = kaforkl_IdeMain::$glade->get_widget( 'green_value' );
                $value = $green->get_value();

                $this->valueArray[$this->xSelected][$this->ySelected][kaforkl_Image::GREEN] = $value;
                break;
            case kaforkl_Image::BLUE:
                $blue = kaforkl_IdeMain::$glade->get_widget( 'blue_value' );
                $value = $blue->get_value();

                if ( $value > 31 && $value < 127 )
                {
                    $char = chr( $value );
                }
                else
                {
                    $char = 'Non readable';
                }

                $blueLabel = kaforkl_IdeMain::$glade->get_widget( 'blue_label' );
                $blueLabel->set_label( 'Char: ' . $char );

                $this->valueArray[$this->xSelected][$this->ySelected][kaforkl_Image::BLUE] = $value;
                break;
            case kaforkl_Image::ALPHA:
                $alpha = kaforkl_IdeMain::$glade->get_widget( 'alpha_value' );
                $value = $alpha->get_value();

                if ( isset( kaforkl_Context::$commandString[$value] ) )
                {
                    $command = kaforkl_Context::$commandString[$value];
                }
                else
                {
                    $command = kaforkl_Context::$commandString[0];
                }

                $alphaLabel = kaforkl_IdeMain::$glade->get_widget( 'alpha_label' );
                $alphaLabel->set_label( $command );

                $this->valueArray[$this->xSelected][$this->ySelected][kaforkl_Image::ALPHA] = $value;
                break;
        }

        // Only store on command
        // $this->updateWidget();
    }

    /**
     * Update displayed value from model
     * 
     * @return void
     */
    public function storePixel()
    {
        $this->updateWidget();
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
