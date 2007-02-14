<?php

/**
 * KaForkL image reader
 * 
 * @package kaforkl
 * @version $id$
 * @copyright 2007 Kore Nordmann
 * @author Kore Nordmann <kore@php.net>
 */
abstract class kaforkl_Image
{
    /**
     * Three dimensional array with image values
     * 
     * @var array
     */
    protected $valueArray;

    /**
     * Width of input image
     * 
     * @var int
     */
    protected $width;

    /**
     * Height of input image
     * 
     * @var int
     */
    protected $height;

    /**
     * List of processors
     * 
     * @var array
     */
    protected $processors;

    /**
     * Red channel
     */
    const RED = 0;

    /**
     * Green channel
     */
    const GREEN = 1;

    /**
     * Blue channel
     */
    const BLUE = 2;

    /**
     * Alpha channel
     */
    const ALPHA = 3;

    /**
     * Construct main interpretor from image
     * 
     * @param mixed $fileName 
     * @access public
     * @return void
     */
    public function __construct( $fileName )
    {
        $this->processors = array();
        $this->valueArray = array();
        $this->readFile( $fileName );
    }

    /**
     * Fill up value array from image
     * 
     * @param string $fileName 
     */
    abstract protected function readFile( $fileName );

    /**
     * Fill up value array from image
     * 
     * @param string $fileName 
     */
    abstract protected function writeFile( $fileName );

    /**
     * Set red value on position
     * 
     * @param kaforkl_Position $position 
     * @param int $value 
     */
    public function setRedValue( kaforkl_Position $position, $value )
    {
        $this->valueArray[$position->getX()][$position->getY()][self::RED] = $value;
    }

    /**
     * Set green value on position
     * 
     * @param kaforkl_Position $position 
     * @param int $value 
     */
    public function setGreenValue( kaforkl_Position $position, $value )
    {
        $this->valueArray[$position->getX()][$position->getY()][self::GREEN] = $value;
    }

    /**
     * Set blue value on position
     * 
     * @param kaforkl_Position $position 
     * @param int $value 
     */
    public function setBlueValue( kaforkl_Position $position, $value )
    {
        $this->valueArray[$position->getX()][$position->getY()][self::BLUE] = $value;
    }

    /**
     * Set alpha value on position
     * 
     * @param kaforkl_Position $position 
     * @param int $value 
     */
    public function setAlphaValue( kaforkl_Position $position, $value )
    {
        $this->valueArray[$position->getX()][$position->getY()][self::ALPHA] = $value;
    }

    /**
     * Add a new processor
     * 
     * @param kaforkl_Context $context 
     */
    public function addProcessor( kaforkl_Context $context )
    {
        $this->processors[] = $context;
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
                if ( DEBUG )
                {
                    printf( "Running %d\n", $nr );
                }

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
