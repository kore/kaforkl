<?php

/**
 * Main class for KaForkL IDE
 * 
 * @package kaforkl
 * @version $id$
 * @copyright 2007 Kore Nordmann
 * @author Kore Nordmann <kore@php.net>
 */
class kaforkl_Dispatch
{
    /**
     * Dispatch method calls to event handler
     * 
     * @param string $method 
     * @param array $params 
     * @return void
     */
    public function __call( $method, array $params = array() )
    {
        switch ( strtolower( $method ) )
        {
            case 'image_save':
                kaforkl_IdeMain::$handler->writeFile( 'ide.out.png' );
                break;

            case 'zoom_changed':
                kaforkl_IdeMain::$handler->updateWidget();
                break;

            case 'exec_run':
                kaforkl_IdeMain::$handler->setLoop( true );
                kaforkl_IdeMain::$handler->run();
                break;
            case 'exec_stop':
                kaforkl_IdeMain::$handler->setLoop( false );
                break;
            case 'exec_next':
                kaforkl_IdeMain::$handler->setLoop( false );
                kaforkl_IdeMain::$handler->run();
                break;
            case 'exec_reset':
                kaforkl_IdeMain::$handler->resetProcessors();
                break;

            case 'key_command':
                call_user_func_array( array( 'kaforkl_IdeMain', 'keyPressEvent' ), $params );
                break;

            case 'pixel_select':
                call_user_func_array( array( kaforkl_IdeMain::$handler, 'selectPixel' ), $params );
                break;
            case 'pixel_store':
                call_user_func_array( array( kaforkl_IdeMain::$handler, 'storePixel' ), $params );
                break;

            case 'blue_update':
                call_user_func_array( array( kaforkl_IdeMain::$handler, 'updatePixel' ), array( kaforkl_Image::BLUE ) );
                break;
            case 'green_update':
                call_user_func_array( array( kaforkl_IdeMain::$handler, 'updatePixel' ), array( kaforkl_Image::GREEN ) );
                break;
            case 'alpha_update':
                call_user_func_array( array( kaforkl_IdeMain::$handler, 'updatePixel' ), array( kaforkl_Image::ALPHA ) );
                break;
            case 'red_update_n':
                call_user_func_array( array( kaforkl_IdeMain::$handler, 'updatePixel' ), array( kaforkl_Image::RED, 1 ) );
                break;
            case 'red_update_ne':
                call_user_func_array( array( kaforkl_IdeMain::$handler, 'updatePixel' ), array( kaforkl_Image::RED, 2 ) );
                break;
            case 'red_update_e':
                call_user_func_array( array( kaforkl_IdeMain::$handler, 'updatePixel' ), array( kaforkl_Image::RED, 4 ) );
                break;
            case 'red_update_se':
                call_user_func_array( array( kaforkl_IdeMain::$handler, 'updatePixel' ), array( kaforkl_Image::RED, 8 ) );
                break;
            case 'red_update_s':
                call_user_func_array( array( kaforkl_IdeMain::$handler, 'updatePixel' ), array( kaforkl_Image::RED, 16 ) );
                break;
            case 'red_update_sw':
                call_user_func_array( array( kaforkl_IdeMain::$handler, 'updatePixel' ), array( kaforkl_Image::RED, 32 ) );
                break;
            case 'red_update_w':
                call_user_func_array( array( kaforkl_IdeMain::$handler, 'updatePixel' ), array( kaforkl_Image::RED, 64 ) );
                break;
            case 'red_update_nw':
                call_user_func_array( array( kaforkl_IdeMain::$handler, 'updatePixel' ), array( kaforkl_Image::RED, 128 ) );
                break;

            default:
                // No rule assigned for signal
                echo "Missing dispatching rules for: '$method'\n";
                break;
        }
    }
}

