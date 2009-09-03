<?php

/* 
* Copyright (c) 2006-2008 MetaCarta, Inc., published under the Clear BSD
* license.  See http://svn.openlayers.org/trunk/openlayers/license.txt for the
* full text of the license. 
*/

require_once('./lib/FirePHP/FirePHP.class.php');
ob_start();

/**
 * Utilities
 * @package    simpleGeo
 * @subpackage Util
 * @author     http://openlayers.org/dev/doc/authors.txt
 * @version    
 */
   
class Util
{ 
  
  /**
  * lastSeqID
  * The ever-incrementing count variable
  * Used for generating unique ids.
  * @var Integer 
  */
 
  public static $lastSeqID = 0;
  
  /**
   *  FirePHP wrapper
   */
  
  public static function log() {

    $fire = FirePHP::getInstance(true);

    $args = func_get_args();
    
    return call_user_func_array(array($fire,'fb'),$args);

  }
  
  /**
   * 
   * @param $arg string|array|object
   * @param $argName string
   * @return string
   */
  
  public static function dump($arg, $argName = "")
  {
    if(!empty($argName)) print '<h5 style="margin:5px 0 0 0; border-top: 2px solid #ccc">' . $argName . ' : </h5>';
      
    print '<pre style="padding: 0 0 0 10px; background-color:#eee;">';
    print_r($arg);
    print "<hr />";
      
    $trace = debug_backtrace();
      
    print "func : " .  $trace[1]['function'] . "\t";
    print "line : " .  $trace[1]['line'] . "\t";

    print "</pre>";
        
  } 
  
  /**
   * Generate unique id
   * @return string
   */
  
  public static function createUniqueID($class)
  { 
    // TODO an array wich keys may correspond to class name and increment by class
    return "id_" . $class . '_' . self::$lastSeqID++;      
  }
  
  /**
   * merge two array
   * @param $destination array
   * @param $source      array
   * @return             array
   */
  
  public static function extend($destination, $source)
  {           
      $source = is_array($source)? $source : array();
      
      $destination = array_merge($destination, $source); // $destination keys are overwrited      
      
      return $destination;
  }
}
