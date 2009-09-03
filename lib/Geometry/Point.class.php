<?php

/* 
* Copyright (c) 2006-2008 MetaCarta, Inc., published under the Clear BSD
* license.  See http://svn.openlayers.org/trunk/openlayers/license.txt for the
* full text of the license. 
*/


/**
 * @package    simpleGeo
 * @subpackage Geometry
 * @author     http://openlayers.org/dev/doc/authors.txt
 * @version    
 */

class Point extends Geometry
{
  /**
   * 
   * @var float
   */
  
  public $x;
  
  /**
   * 
   * @var float
   */
  
  public $y;
   
  
  public function __construct($x, $y)
  {
     $this->x = $x;
     $this->y = $y;
     
     parent::__construct(__CLASS__);
  }
  
  /**
   * 
   * @param object $point Point instance
   * @return bool
   */
  
  public function equals($point)
  {
      $equals = false;
      if(isset($point))
      {
        $equals = (($this->x == $point->x && $this->y = $point->y) || (!is_int($this->x) && !is_int($this->y) && !is_int($point->x) && !is_int($point->y)));     
      }
  }   
}