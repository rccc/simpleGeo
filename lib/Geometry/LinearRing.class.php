<?php

/* 
* Copyright (c) 2006-2008 MetaCarta, Inc., published under the Clear BSD
* license.  See http://svn.openlayers.org/trunk/openlayers/license.txt for the
* full text of the license. 
*/


/**
 * A Linear Ring is a special LineString which is closed
 * 
 * @package    simpleGeo
 * @subpackage Geometry
 * @author     http://openlayers.org/dev/doc/authors.txt
 * @version    
 */

class LinearRing extends LineString
{
  public function __construct($components)
  {
    $this->componentTypes = "point";
    parent::__construct($components); 
  }
  
  /**
   * (non-PHPdoc)
   * @see lib/Geometry/Collection#addComponent($component, $index)
   */
  
  public function addComponent($component, $index = null)
  {
    $added = false;   
    
    $lastPoint = array_pop($this->components);
    
    if($index != null || empty($lastPoint) || !$component->equals($lastpoint))
    {
        $added = parent::addComponent($component, $index);
    }
    
    //TODO cas index != null à traiter           
    
    //append copy of first point    
    $firstPoint = $this->components[0];
    parent::addComponent($firstPoint);
    
    return $added;
  }
}