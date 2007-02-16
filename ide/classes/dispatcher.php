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
            case 'zoom_changed':
                kaforkl_IdeMain::$handler->updateWidget();
                break;

            default:
                // No rule assigned for signal
                echo "Missing dispatching rules for: '$method'\n";
                break;
        }
    }
}

