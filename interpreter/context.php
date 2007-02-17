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

    public static $commandString = array(
        0  => 'None',
        1  => 'Var Jump',
        2  => 'Value Jump',
        10 => 'Echo Value',
        11 => 'Echo Var',
        12 => 'Copy Var',
        15 => 'Read Var',
        16 => 'Read Value to Var',
        20 => 'EQ',
        21 => 'NE',
        22 => 'LT',
        23 => 'GT',
        24 => 'LE',
        25 => 'GE',
        30 => '<<',
        31 => '>>',
        32 => 'AND',
        33 => 'OR',
        34 => 'XOR',
        35 => 'ADD',
        36 => 'SUB',
        37 => 'MUL',
        38 => 'MOD',
        39 => 'DIV',
        40 => 'Red Value',
        41 => 'Green Value',
        42 => 'Blue Value',
        43 => 'Alpha Value',
        45 => 'Red Var',
        46 => 'Green Var',
        47 => 'Blue Var',
        48 => 'Alpha Var',
    );

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
     * Get content from stack
     *
     * Check if stack is initialized, return 0 otherwise. Optional second 
     * parameter to not echo debug output.
     * 
     * @param int $i 
     * @param bool $silence 
     * @return int
     */
    protected function getFromStack( $i, $silence = false )
    {
        if ( !isset( $this->stack[$i] ) )
        {
            if ( !$silence )
            {
                $this->image->debug( "! => Stack position '$i' not initialised." );
            }

            return 0;
        }

        return $this->stack[$i];
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
        $this->image->debug( sprintf( " -> Command: %d (Value: %d; Var: %d (%d))\n",
            $alpha,
            $blue,
            $green,
            $this->getFromStack( $green, true )
        ) );

        // Update context accordingly to parameters
        $fork = true;
        $stepSize = 1;

        switch ( $alpha )
        {
            // Walking options
            case 0:
                // Just walk
                break;
            case 1:
                $stepSize = $this->getFromStack( $green );
                break;
            case 2:
                $stepSize = $blue;
                break;

            // IO operations
            case 10:
                echo chr( $blue );
                break;
            case 11:
                echo chr( $this->getFromStack( $green ) );
                break;
            case 12:
                $this->stack[$blue] = $this->getFromStack( $green );
                break;
            case 15:
                // Implement
                break;
            case 16:
                $this->stack[$green] = $blue;
                break;

            // Conditional statements
            case 20:
                if ( $blue !== $this->getFromStack( $green ) )
                {
                    $fork = false;
                }
                break;
            case 21:
                if ( $blue === $this->getFromStack( $green ) )
                {
                    $fork = false;
                }
                break;
            case 22:
                if ( $blue >= $this->getFromStack( $green ) )
                {
                    $fork = false;
                }
                break;
            case 23:
                if ( $blue <= $this->getFromStack( $green ) )
                {
                    $fork = false;
                }
                break;
            case 24:
                if ( $blue > $this->getFromStack( $green ) )
                {
                    $fork = false;
                }
                break;
            case 25:
                if ( $blue < $this->getFromStack( $green ) )
                {
                    $fork = false;
                }
                break;

            // Calculations
            case 30:
                $this->stack[$green] = ( $this->stack[$green] << $blue ) % 256;
                break;
            case 31:
                $this->stack[$green] = ( $this->stack[$green] >> $blue ) % 256;
                break;
            case 32:
                $this->stack[$green] = ( $this->stack[$green] & $blue ) % 256;
                break;
            case 33:
                $this->stack[$green] = ( $this->stack[$green] | $blue ) % 256;
                break;
            case 34:
                $this->stack[$green] = ( $this->stack[$green] ^ $blue ) % 256;
                break;
            case 35:
                $this->stack[$green] = ( $this->stack[$green] + $blue ) % 256;
                break;
            case 36:
                $this->stack[$green] = ( $this->stack[$green] - $blue + 256 ) % 256;
                break;
            case 37:
                $this->stack[$green] = ( $this->stack[$green] * $blue ) % 256;
                break;
            case 38:
                $this->stack[$green] = $this->stack[$green] % $blue;
                break;
            case 39:
                $this->stack[$green] = (int) floor( $this->stack[$green] / $blue );
                break;

            // Image manipulations
            case 40:
                $this->image->setRedValue( $this->position, $blue );
                break;
            case 41:
                $this->image->setGreenValue( $this->position, $blue );
                break;
            case 42:
                $this->image->setBlueValue( $this->position, $blue );
                break;
            case 43:
                $this->image->setAlphaValue( $this->position, $blue );
                break;
            case 45:
                $this->image->setRedValue( $this->position, $this->getFromStack( $green ) );
                break;
            case 46:
                $this->image->setGreenValue( $this->position, $this->getFromStack( $green ) );
                break;
            case 47:
                $this->image->setBlueValue( $this->position, $this->getFromStack( $green ) );
                break;
            case 48:
                $this->image->setAlphaValue( $this->position, $this->getFromStack( $green ) );
                break;
        }

        if ( isset( self::$commandString[$alpha] ) )
        {
            $cmdString = self::$commandString[$alpha];
        }
        else
        {
            $cmdString = self::$commandString[0];
        }

        $this->image->debug( sprintf( "   -> Executed: %s (Value: %d; Var: %d (%d))\n",
            $cmdString,
            $blue,
            $green,
            // Silence notices from unitialized stack variables
            $this->getFromStack( $green, true )
        ) );

        if ( $fork )
        {
            $this->image->debug( sprintf( "   -> Move: %d\n",
                $red
            ) );
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
