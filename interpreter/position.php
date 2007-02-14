<?php

/**
 * Processor position
 * 
 * @package kaforkl
 * @version $id$
 * @copyright 2007 Kore Nordmann
 * @author Kore Nordmann <kore@php.net>
 */
class kaforkl_Position
{
    /**
     * Current horizontal position
     * 
     * @var int
     */
    protected $x;

    /**
     * Current vertical position
     * 
     * @var int
     */
    protected $y;

    /**
     * Total width of image
     * 
     * @var int
     */
    protected $width;

    /**
     * Total height of image
     * 
     * @var int
     */
    protected $height;

    /**
     * Create position from image dimensions
     * 
     * @param int $width 
     * @param int $height 
     * @param int $x 
     * @param int $y 
     * @return kaforkl_Position
     */
    public function __construct( $width, $height, $x = 0, $y = 0 )
    {
        $this->width = $width;
        $this->height = $height;

        $this->x = $x % $width;
        $this->y = $y % $height;
    }

    /**
     * Return current x coordinate
     * 
     * @return int
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * Return current y coordinate
     * 
     * @return int
     */
    public function getY()
    {
        return $this->y;
    }

    /**
     * Modify position coordinate by direction and stepsize
     * 
     * @param int $direction 
     * @param int $stepSize 
     * @return void
     */
    public function move( $direction, $stepSize = 1 )
    {
        switch ( $direction )
        {
            case 1:
                $this->y = ( $this->y - $stepSize + $this->height ) % $this->height;
                $directionString = 'N';
                break;
            case 2:
                $this->x = $this->x + $stepSize % $this->width;
                $this->y = ( $this->y - $stepSize + $this->height ) % $this->height;
                $directionString = 'NE';
                break;
            case 4:
                $this->x = $this->x + $stepSize % $this->width;
                $this->y = ( $this->y - $stepSize + $this->height ) % $this->height;
                $directionString = 'E';
                break;
            case 8:
                $this->x = $this->x + $stepSize % $this->width;
                $this->y = $this->y + $stepSize % $this->height;
                $directionString = 'SE';
                break;
            case 16:
                $this->y = $this->y + $stepSize % $this->height;
                $directionString = 'S';
                break;
            case 32:
                $this->x = ( $this->x - $stepSize + $this-width ) % $this->width;
                $this->y = $this->y + $stepSize % $this->height;
                $directionString = 'SW';
                break;
            case 64:
                $this->x = ( $this->x - $stepSize + $this-width ) % $this->width;
                $directionString = 'W';
                break;
            case 128:
                $this->x = ( $this->x - $stepSize + $this-width ) % $this->width;
                $this->y = ( $this->y - $stepSize + $this->height ) % $this->height;
                $directionString = 'NW';
                break;
        }

        if ( DEBUG )
        {
            printf( " -> Move %s (%d) (Step size: %d)\n",
                $directionString,
                $direction,
                $stepSize
            );
        }
    }
}
