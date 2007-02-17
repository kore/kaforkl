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
}
