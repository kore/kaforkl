<?php

require_once 'interpreter/context.php';
require_once 'interpreter/position.php';
require_once 'interpreter/image.php';

/**
 * KaForkL main program
 *
 * @package kaforkl
 * @version $id$
 * @copyright 2007 Kore Nordmann
 * @author Kore Nordmann <kore@php.net>
 */
class kaforkl_Main
{
    /**
     * CLI options array
     * 
     * @var array
     */
    protected $cliOptions = array(
        'output' => array(
            'short' => 'O',
            'type' => 'string',
            'desc' => 'Filename for output image',
            'default' => false,
        ),
        'start' => array(
            'short' => 'o',
            'type' => 'string',
            'desc' => 'Start coordinate',
            'default' => '0,0',
        ),
        'handler' => array(
            'short' => 'h',
            'type' => 'string',
            'desc' => 'Handler for image processing',
            'default' => 'GD',
        ),
        'offset' => array(
            'short' => 'o',
            'type' => 'int',
            'desc' => 'Offset for values and commands',
            'default' => 0,
        ),
        'debug' => array(
            'short' => 'd',
            'type' => 'boolean',
            'desc' => 'Enable debug mode when set to 1',
            'default' => false,
        ),
    );

    /**
     * Create interpreter from cli options
     * 
     * @param array $argv 
     * @return kaforkl_Main
     */
    public function __construct( array $argv )
    {
        $cliValues = $this->parseOptions( $argv );

        // Parse cli coordinate value
        if ( !preg_match( '/^(\d+)\D(\d+)$/', $cliValues['start'], $match ) )
        {
            echo "Could not parse coordinate ", $cliValues['start'], "\n\n";
            $this->showHelp();
        }

        // Set debug mode
        define( 'DEBUG', $cliValues['debug'] );

        // Build image handler
        $handlerClassName = 'kaforkl_Image_' . $cliValues['handler'];
        $handlerFileName = 'interpreter/image/' . strtolower( $cliValues['handler'] ) . '.php';

        require_once $handlerFileName;
        $handler = new $handlerClassName( $cliValues['input'] );
        $handler->run( $match[1], $match[2] );
    }

    /**
     * Parse CLI options and return their values
     * 
     * @param array $argv 
     * @return array
     */
    protected function parseOptions( array $argv )
    {
        $optionsLong = array();
        $types = array();
        $optionsShort = array();
        $cliValues = array();

        foreach ( $this->cliOptions as $long => $data )
        {
            $optionsLong[$long] = $long;
            $types[$long] = $data['type'];
            $cliValues[$long] = $data['default'];

            if ( $data['short'] )
            {
                $optionsShort[$data['short']] = $long;
            }
        }

        // Check input file
        $fileName = end($argv);
        if ( !is_file( $fileName ) )
        {
            echo $fileName, ' is not a valid file.';
            exit( 1 );
        }

        $fileName = realpath( $fileName );
            
        // Check other options
        $parameters = $argv;
        while ( $param = next( $parameters ) )
        {
            if ( $param[0] !== '-' )
            {
                continue;
            }

            if ( $param[1] !== '-' )
            {
                // Short option
                $short = $param[1];
                if ( !isset( $optionsShort[$short] ) )
                {
                    echo "Unknown option -$short.\n\n";
                    $this->showHelp();
                }

                $value = next( $parameters );
                settype( $value, $types[$optionsShort[$short]] );
                $cliValues[$optionsShort[$short]] = $value;
            } else {
                // Long option
                if ( !preg_match( '/^--([a-z0-9_]+)=(\S+)$/', $param, $match ) )
                {
                    echo "Could not parse option <$param> - skipping.\n";
                    continue;
                }
                
                $long = $match[1];
                $value = $match[2];

                if ( !isset( $optionsLong[$long] ) )
                {
                    echo "Unknown option --$long.\n\n";
                    $this->showHelp();
                }
                
                settype( $value, $types[$long] );
                $cliValues[$long] = $value;
            }
        }

        $cliValues['input'] = $fileName;
        return $cliValues;
    }

    /**
     * Show interpreter help
     * 
     * @return void
     */
    protected function showHelp()
    {
        echo 'KaForkL interpreter
by Kore Nordmann

Use of this command:
kaforkl [<parameters>] <image>
Where <image> is the code of the programm to process and <parameters>
one of the following:

';
        foreach ( $this->cliOptions as $long => $data )
        {
            echo str_pad( ' --' . $long . '=', 23 );
            if ( $data['short'] )
            {
                echo ', -', $data['short'], '   ';
            } else {
                echo '       ';
            }

            echo $data['desc'], "\n";
        }
        echo "\n";
        exit ( 1 );
    }
}
