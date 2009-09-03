<?php

/* 
* Copyright (c) 2006-2008 MetaCarta, Inc., published under the Clear BSD
* license.  See http://svn.openlayers.org/trunk/openlayers/license.txt for the
* full text of the license. 
*/

/**
 * Polygon is a collection of Geometry.LinearRings
 * 
 * @package    simpleGeo
 * @subpackage Geometry
 * @author     http://openlayers.org/dev/doc/authors.txt
 * @version    
 */

class Polygon extends Collection
{
   public function __construct($components)
   {
      $this->componentTypes = "LinearRing";
      parent::__construct($components, __CLASS__);
   }
}