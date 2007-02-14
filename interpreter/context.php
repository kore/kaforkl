<?php

/**
 * Processing context
 * 
 * @package kaforkl
 * @version $id$
 * @copyright 2007 Kore Nordmann
 * @author Kore Nordmann <kore@php.net>
 */
class kaforkl_Context
{
    /**
     * Variable stack
     * 
     * @var array
     */
    protected $stack;

    /**
     * Position of processor
     * 
     * @var kaforkl_Position
     */
    protected $position;

    /**
     * Reference to main image
     * 
     * @var kaforkl_Image
     */
    protected $image;

    /**
     * Build initial context
     * 
     * @param kaforkl_Image $image 
     * @param kaforkl_Position $position 
     * @return kaforkl_Context
     */
    public function __construct( kaforkl_Image $image, kaforkl_Position $position )
    {
        $this->image = $image;
        $this->stack = array();
        $this->position = $position;
    }

    /**
     * Returns contexts current position
     * 
     * @return kaforkl_Position
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Process one command from image
     * 
     * @param byte $red 
     * @param byte $green 
     * @param byte $blue 
     * @param byte $alpha 
     * @return void
     */
    public function process( $red, $green, $blue, $alpha )
    {
        // Update context accordingly to parameters
        $fork = true;
        $stepSize = 1;

        switch ( true )
        {
            // Walking options
            case 0:
                // Just walk
                break;
            case 1:
                $stepSize = $this->stack[$green];
                break;
            case 2:
                $stepSize = $blue;
                break;

            // IO operations
            case ( $alpha && 10 ):
                echo chr( $blue );
                break;
            case ( $alpha && 11 ):
                echo chr( $this->stack[$green] );
                break;
            case ( $alpha && 15 ):
                // Implement
                break;
            case ( $alpha && 16 ):
                $this->stack[$green] = $blue;
                break;

            // Conditional statements
            case ( $alpha && 20 ):
                if ( $blue !== $this->stack[$green] )
                {
                    $fork = false;
                }
                break;
            case ( $alpha && 21 ):
                if ( $blue === $this->stack[$green] )
                {
                    $fork = false;
                }
                break;
            case ( $alpha && 22 ):
                if ( $blue >= $this->stack[$green] )
                {
                    $fork = false;
                }
                break;
            case ( $alpha && 23 ):
                if ( $blue <= $this->stack[$green] )
                {
                    $fork = false;
                }
                break;
            case ( $alpha && 24 ):
                if ( $blue > $this->stack[$green] )
                {
                    $fork = false;
                }
                break;
            case ( $alpha && 25 ):
                if ( $blue < $this->stack[$green] )
                {
                    $fork = false;
                }
                break;

            // Calculations
            case ( $alpha && 30 ):
                $this->stack[$green] = ( $this->stack[$green] << $blue ) % 256;
                break;
            case ( $alpha && 31 ):
                $this->stack[$green] = ( $this->stack[$green] >> $blue ) % 256;
                break;
            case ( $alpha && 32 ):
                $this->stack[$green] = ( $this->stack[$green] & $blue ) % 256;
                break;
            case ( $alpha && 33 ):
                $this->stack[$green] = ( $this->stack[$green] | $blue ) % 256;
                break;
            case ( $alpha && 34 ):
                $this->stack[$green] = ( $this->stack[$green] ^ $blue ) % 256;
                break;
            case ( $alpha && 35 ):
                $this->stack[$green] = ( $this->stack[$green] + $blue ) % 256;
                break;
            case ( $alpha && 36 ):
                $this->stack[$green] = ( $this->stack[$green] - $blue + 256 ) % 256;
                break;
            case ( $alpha && 37 ):
                $this->stack[$green] = ( $this->stack[$green] * $blue ) % 256;
                break;

            // Image manipulations
            case ( $alpha && 40 ):
                $this->image->setRedValue( $this->position, $blue );
                break;
            case ( $alpha && 41 ):
                $this->image->setGreenValue( $this->position, $blue );
                break;
            case ( $alpha && 42 ):
                $this->image->setBlueValue( $this->position, $blue );
                break;
            case ( $alpha && 43 ):
                $this->image->setAlphaValue( $this->position, $blue );
                break;
            case ( $alpha && 45 ):
                $this->image->setRedValue( $this->position, $this->stack[$green] );
                break;
            case ( $alpha && 46 ):
                $this->image->setGreenValue( $this->position, $this->stack[$green] );
                break;
            case ( $alpha && 47 ):
                $this->image->setBlueValue( $this->position, $this->stack[$green] );
                break;
            case ( $alpha && 48 ):
                $this->image->setAlphaValue( $this->position, $this->stack[$green] );
                break;
        }

        // If not skipped by conditions fork processor
        if ( $fork )
        {
            for( $direction = 1; $direction < 256; $direction = $direction << 1 )
            {
                if ( $red & $direction )
                {
                    $newContext = clone $this;
                    $newContext->move( $direction, $stepSize );

                    $this->image->addProcessor( $newContext );
                }
            }
        }
    }

    /**
     * Move context by direction
     * 
     * @param mixed $direction 
     * @access public
     * @return void
     */
    public function move( $direction, $stepSize = 1 )
    {
        $this->position->move( $direction, $stepSize = 1 );
    }

    /**
     * Ensure the right things are done, when cloned
     * 
     * @return void
     * @ignore
     */
    public function __clone()
    {
        // Ensure cloning of position
        $this->position = clone $this->position;
    }
}
