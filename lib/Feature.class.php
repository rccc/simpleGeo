<?php

/* 
* Copyright (c) 2006-2008 MetaCarta, Inc., published under the Clear BSD
* license.  See http://svn.openlayers.org/trunk/openlayers/license.txt for the
* full text of the license. 
*/


 /**
 * Feature instances are combinations of geography and attributes.
 * @package    simpleGeo
 * @subpackage Feature
 * @author     http://openlayers.org/dev/doc/authors.txt
 * @version    
 */

class Feature
{
  /**
   * 
   * @var string
   */
  
  public $id = null;
  
  /**
   * 
   * @var string
   */
  
  public $fid = null;
    
  /**
   * 
   * @var object Geometry instance
   */
  
  public $geometry = null;
  
  /**
   * array of atributes
   * @var array
   */
  
  public $attributes = array();
  
  /**
   * 
   * @var string
   */
  
  public $style = null;

  /**
   * 
   * @param $geometry object Geometry instance
   * @param $attributes array
   * @param $style array
   */
  
  public function __construct($geometry, $attributes = array(), $style = array())
  {   
    $this->geometry   = $geometry;
    $this->attributes = $attributes;
    $this->style      = $style;
    $this->id         = Util::createUniqueID(__CLASS__);
  }
      
}
