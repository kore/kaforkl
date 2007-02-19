<?php

require_once 'classes/dispatcher.php';
require_once 'classes/model.php';

/**
 * Main class for KaForkL IDE
 * 
 * @package kaforkl
 * @version $id$
 * @copyright 2007 Kore Nordmann
 * @author Kore Nordmann <kore@php.net>
 */
class kaforkl_IdeMain
{
    /**
     * Refenrence to glade handle
     * 
     * @var GladeXML
     */
    public static $glade;

    /**
     * Image model
     * 
     * @var kaforkl_ImageModel
     */
    public static $handler;

    /**
     * File to work with
     * 
     * @var string
     */
    protected static $filename;

    /**
     * List of available icons for the window
     * 
     * @var array
     */
    protected $iconList = array(
        'icons/16x16.png',
        'icons/32x32.png',
        'icons/48x48.png',
        'icons/64x64.png',
        'icons/128x128.png',
    );

    /**
     * Construct from CLI arguments
     * 
     * @param array $argv 
     * @return void
     */
    protected function __construct( array $argv = array() )
    {
        self::$glade = new GladeXML( dirname( __FILE__ ) . '/../gui.glade' );
        $window = self::$glade->get_widget( 'main' );

        $pixbufs = array();
        foreach ( $this->iconList as $iconFile )
        {
            $pixbufs[] = GdkPixbuf::new_from_file( dirname( __FILE__ ) . '/../' . $iconFile );
        }
        $window->set_icon_list( $pixbufs );

        $this->connectAll();
        $this->parseArgv( $argv );

        // Import image
        self::$handler = new kaforkl_ImageModel( self::$filename );

        Gtk::main();
    }

    /**
     * Static initialization of IDE
     * 
     * @param array $argv 
     * @return kaforkl_IdeMain
     */
    public static function init( array $argv = array() )
    {
        if ( self::$glade === null )
        {
            return new kaforkl_IdeMain( $argv );
        }
    }

    /**
     * Parse CLI arguments (none yet available)
     * 
     * @param array $argv 
     * @return void
     */
    protected function parseArgv( array $argv = array() )
    {
        self::$filename = end( $argv );
    }

    /**
     * Connect all signals to global signal dispatcher
     * 
     * @return void
     */
    protected function connectAll()
    {
        // Main quit
        $window = self::$glade->get_widget( 'main' );
        $window->connect_simple( 'destroy', array( 'Gtk', 'main_quit' ) );

        // Auto-dispatch all defined events
        $dispatcher = new kaforkl_Dispatch();
        self::$glade->signal_autoconnect_instance( $dispatcher );
    }

    public static function keyPressEvent( $widget, $event )
    {
        switch ( $event->keyval )
        {
            case 65361: // Left
                self::$handler->move( -1, 0 );
                break;
            case 65364: // Down
                self::$handler->move( 0, 1 );
                break;
            case 65363: // Right
                self::$handler->move( 1, 0 );
                break;
            case 65362: // Top
                self::$handler->move( 0, -1 );
                break;

            case 65436: // Num 1
                self::$handler->changeValue( kaforkl_Image::RED, 32 );
                break;
            case 65433: // Num 2
                self::$handler->changeValue( kaforkl_Image::RED, 16 );
                break;
            case 65435: // Num 3
                self::$handler->changeValue( kaforkl_Image::RED, 8 );
                break;
            case 65430: // Num 4
                self::$handler->changeValue( kaforkl_Image::RED, 64 );
                break;
            case 65437: // Num 5
                break;
            case 65432: // Num 6
                self::$handler->changeValue( kaforkl_Image::RED, 4 );
                break;
            case 65429: // Num 7
                self::$handler->changeValue( kaforkl_Image::RED, 128 );
                break;
            case 65431: // Num 8
                self::$handler->changeValue( kaforkl_Image::RED, 1 );
                break;
            case 65434: // Num 9
                self::$handler->changeValue( kaforkl_Image::RED, 2 );
                break;

            default:
//                echo "Unhandled key pressed: ", $event->keyval, "\n";
        }
    }
}
