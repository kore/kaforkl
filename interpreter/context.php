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
        $this->stack = array( false );
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
        if ( DEBUG )
        {
            printf( " -> Command: %d (Value: %d; Var: %d (%d))\n",
                $alpha,
                $blue,
                $green,
                $this->stack[$green]
            );
        }

        // Update context accordingly to parameters
        $fork = true;
        $stepSize = 1;

        switch ( true )
        {
            // Walking options
            case ( $alpha === 0 ):
                // Just walk
                $cmdString = 'None';
                break;
            case ( $alpha === 1 ):
                $stepSize = $this->stack[$green];
                $cmdString = 'Var Jump';
                break;
            case ( $alpha === 2 ):
                $stepSize = $blue;
                $cmdString = 'Value Jump';
                break;

            // IO operations
            case ( $alpha === 10 ):
                $cmdString = 'Echo Value';
                echo chr( $blue );
                break;
            case ( $alpha === 11 ):
                $cmdString = 'Echo Var';
                echo chr( $this->stack[$green] );
                break;
            case ( $alpha === 15 ):
                $cmdString = 'Read Var';
                // Implement
                break;
            case ( $alpha === 16 ):
                $cmdString = 'Read Value to Var';
                $this->stack[$green] = $blue;
                break;

            // Conditional statements
            case ( $alpha === 20 ):
                $cmdString = 'EQ';
                if ( $blue !== $this->stack[$green] )
                {
                    $fork = false;
                }
                break;
            case ( $alpha === 21 ):
                $cmdString = 'NE';
                if ( $blue === $this->stack[$green] )
                {
                    $fork = false;
                }
                break;
            case ( $alpha === 22 ):
                $cmdString = 'LT';
                if ( $blue >= $this->stack[$green] )
                {
                    $fork = false;
                }
                break;
            case ( $alpha === 23 ):
                $cmdString = 'GT';
                if ( $blue <= $this->stack[$green] )
                {
                    $fork = false;
                }
                break;
            case ( $alpha === 24 ):
                $cmdString = 'LE';
                if ( $blue > $this->stack[$green] )
                {
                    $fork = false;
                }
                break;
            case ( $alpha === 25 ):
                $cmdString = 'GE';
                if ( $blue < $this->stack[$green] )
                {
                    $fork = false;
                }
                break;

            // Calculations
            case ( $alpha === 30 ):
                $cmdString = '<<';
                $this->stack[$green] = ( $this->stack[$green] << $blue ) % 256;
                break;
            case ( $alpha === 31 ):
                $cmdString = '>>';
                $this->stack[$green] = ( $this->stack[$green] >> $blue ) % 256;
                break;
            case ( $alpha === 32 ):
                $cmdString = 'AND';
                $this->stack[$green] = ( $this->stack[$green] & $blue ) % 256;
                break;
            case ( $alpha === 33 ):
                $cmdString = 'OR';
                $this->stack[$green] = ( $this->stack[$green] | $blue ) % 256;
                break;
            case ( $alpha === 34 ):
                $cmdString = 'XOR';
                $this->stack[$green] = ( $this->stack[$green] ^ $blue ) % 256;
                break;
            case ( $alpha === 35 ):
                $cmdString = 'ADD';
                $this->stack[$green] = ( $this->stack[$green] + $blue ) % 256;
                break;
            case ( $alpha === 36 ):
                $cmdString = 'SUB';
                $this->stack[$green] = ( $this->stack[$green] - $blue + 256 ) % 256;
                break;
            case ( $alpha === 37 ):
                $cmdString = 'MUL';
                $this->stack[$green] = ( $this->stack[$green] * $blue ) % 256;
                break;

            // Image manipulations
            case ( $alpha === 40 ):
                $cmdString = 'Red Value';
                $this->image->setRedValue( $this->position, $blue );
                break;
            case ( $alpha === 41 ):
                $cmdString = 'Green Value';
                $this->image->setGreenValue( $this->position, $blue );
                break;
            case ( $alpha === 42 ):
                $cmdString = 'Blue Value';
                $this->image->setBlueValue( $this->position, $blue );
                break;
            case ( $alpha === 43 ):
                $cmdString = 'Alpha Value';
                $this->image->setAlphaValue( $this->position, $blue );
                break;
            case ( $alpha === 45 ):
                $cmdString = 'Red Var';
                $this->image->setRedValue( $this->position, $this->stack[$green] );
                break;
            case ( $alpha === 46 ):
                $cmdString = 'Green Var';
                $this->image->setGreenValue( $this->position, $this->stack[$green] );
                break;
            case ( $alpha === 47 ):
                $cmdString = 'Blue Var';
                $this->image->setBlueValue( $this->position, $this->stack[$green] );
                break;
            case ( $alpha === 48 ):
                $cmdString = 'Alpha Var';
                $this->image->setAlphaValue( $this->position, $this->stack[$green] );
                break;
        }

        if ( DEBUG )
        {
            printf( "   -> Executed: %s (Value: %d; Var: %d (%d))\n",
                $cmdString,
                $blue,
                $green,
                $this->stack[$green]
            );

            if ( $fork )
            {
                printf( "   -> Move: %d\n",
                    $red
                );
            }
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
