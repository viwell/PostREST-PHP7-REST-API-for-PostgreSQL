<?php
/**
 * Created by PhpStorm.
 * User: dgreenberg
 * Date: 10/5/16
 * Time: 9:14 PM
 */

namespace Alquemedia_PostREST;


class Request_URI {

    /**
     * @param $partNumber
     * @return string Part number
     */
    public function part( $partNumber ){

    	if (count($_GET))
    	{
			return (string) @explode("/", @explode("?", $_SERVER["REQUEST_URI"])[0])[$partNumber];
    	}

        return (string) @explode('/',$_SERVER['REQUEST_URI'])[$partNumber];

    }

    /**
    * Return a parameter passed by the query string
    *
    * @param string $name
    * @return string|null
    */
    public static function getParam($name)
    {
    	if (isset($_GET[$name]))
    	{
    		return $_GET[$name];
    	}

    	return null;
    }

}