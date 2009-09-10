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

class MultiPoint extends Collection
{
  
  /**
   * Property: componentTypes
   * {Array(String)} An array of class names representing the types of
   * components that the collection can include.  A null value means the
   * component types are not restricted.
   */
  
  public $componentTypes = "Point";
  
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
   
  
  public function __construct($x = null, $y = null)
  {     
     parent::__construct(__CLASS__);
  }
  
  /**
   * APIMethod: addPoint
   * Wrapper for <OpenLayers.Geometry.Collection.addComponent>
   *
   * Parameters:
   * point - {<OpenLayers.Geometry.Point>} Point to be added
   * index - {Integer} Optional index
   */
  
  public function addPoint($point, $index) 
  {
      self::addComponent($point, $index);
  }
  
  
  /**
   * APIMethod: removePoint
   * Wrapper for <OpenLayers.Geometry.Collection.removeComponent>
   *
   * Parameters:
   * point - {<OpenLayers.Geometry.Point>} Point to be removed
   */
  
  public function removePoint($point)
  {
      self::removeComponent($point);
      //TODO clearBounds
  } 

}