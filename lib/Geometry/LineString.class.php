<?php

/* 
* Copyright (c) 2006-2008 MetaCarta, Inc., published under the Clear BSD
* license.  See http://svn.openlayers.org/trunk/openlayers/license.txt for the
* full text of the license. 
*/

/**
 * A LineString is a Curve which, once two points have been added to it, can
 * never be less than two points long.
 * 
 * @package    simpleGeo
 * @subpackage Geometry
 * @author     http://openlayers.org/dev/doc/authors.txt
 * @version    
 */

class LineString extends Collection
{   
   public function __construct($components)
   {
      $this->componentTypes = "point";       
      parent::__construct($components, __CLASS__);
   }
}