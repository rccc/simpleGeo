<?php

/* 
* Copyright (c) 2006-2008 MetaCarta, Inc., published under the Clear BSD
* license.  See http://svn.openlayers.org/trunk/openlayers/license.txt for the
* full text of the license. 
*/

//require_once('./lib/FirePHP/FirePHP.class.php');
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

      if(empty($destination)) return $source;
       
      $destination = is_array($destination)? $destination : array($destination);       
      $source = is_array($source)? $source : array($source);
      
      $destination = array_merge($destination, $source); // $destination keys are overwrited      
      
      return $destination;
  }
  
  /**
   * Function: removeItem
   * Remove an object from an array. Iterates through the array
   * to find the item, then removes it.
   *
   * Parameters:
   * @param $components array
   * @param $item       object
   * @return array
   */
  
   public static function removeItem($components = array(), $item) 
   {
       foreach($components as $component):
            static $i = 0;
            if($component == $item) array_splice($components,$i,1);                             
            $i++;
       endforeach;
       
       return $components;
   }
   
  /**
   * Function: removeTail
   * Takes a url and removes everything after the ? and #
   *
   * Parameters:
   * url - {String} The url to process
   *
   * Returns:
   * {String} The string with all queryString and Hash removed
   */
  public static function removeTail($url) 
  {
//      $head = null;
//      
//      $qMark = stristr($url, '?', true); //url.indexOf("?");
//      $hashMark = stristr($url, '#', true);//url.indexOf("#");
//
//      Util::log($qMark, 'qMArk');
//      Util::log($hashMark, 'hashMark');
//      
//      if ($qMark == false) {
//          $head = ($hashMark != false) ? $hashMark : $url;
//      } else {
//          $head = ($hashMark != false) ? $hashMark: $qMark;
//      }
//      
//      Util::log($head, 'head');
//      
//      return $head;
        return $url;
  }

  
  /**
   * Function: concat
   * merge two array
   * 
   * @param   array | string
   * @return  array
   */
  
  public static function concat() 
  {
  $vars=func_get_args();
  $array=array();
  foreach ($vars as $var) {
     if (is_array($var)) {
        foreach ($var as $val) {$array[]=$val;}
     } else {
        $array[]=$var;
     }
  }
  return $array;
  }
  
}
