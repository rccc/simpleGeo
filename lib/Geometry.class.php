<?php
 
/* 
* Copyright (c) 2006-2008 MetaCarta, Inc., published under the Clear BSD
* license.  See http://svn.openlayers.org/trunk/openlayers/license.txt for the
* full text of the license. 
*/

require_once('Util.class.php');

/**
 * A Geometry is a description of a geographic object.  Create an instance of
 * @package    simpleGeo
 * @subpackage Geometry
 * @author     http://openlayers.org/dev/doc/authors.txt
 * @version    
 */

class geometry
{
  /** 
   * A unique identifier for this geometry.
   * @var string 
   */
  
  public $id = null;
  
  /**
   * @parent
   * This is set when a Geometry is added as component of another geometry
   */
  
  public $parent = null;

  //TODO implements bounds
  
  public function __construct($class = "geometry")
  {  
     $this->id = Util::createUniqueID($class);
  }

}


