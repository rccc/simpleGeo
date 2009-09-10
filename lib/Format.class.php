<?php

/* 
* Copyright (c) 2006-2008 MetaCarta, Inc., published under the Clear BSD
* license.  See http://svn.openlayers.org/trunk/openlayers/license.txt for the
* full text of the license. 
*/


/**
 * Base class for format reading/writing a variety of formats
 * @package    simpleGeo
 * @subpackage Format
 * @author     http://openlayers.org/dev/doc/authors.txt
 * @version    
 */

class Format
{
  /**
   * When passed a externalProjection and
   * internalProjection, the format will reproject the geometries it reads or writes. 
   * @var object - Projection instance
   */
  
  public $internalProjection = null;
  
  /**
  * When passed a externalProjection and
  * internalProjection, the format will reproject the geometries it reads or writes. 
  * @var object - Projection instance
  */
  
  public $externalProjection = null;
  
  /**
   * When <keepData> is true, this is the parsed string sent to <read>.
   * @var object
   */
  
  public $data = null;
  
  /**
   * Maintain a reference (<data>) to the most recently read data.
   * @var bool
   */
  
  public $keepData = false;
  
  
  public function __set($name, $value) {
      $this->$name = $value;
  }
  
  /**
   * 
   * @param $options array
   * 
   */
  
  public function __construct($options = array())
  {
     if(!empty($options))
     {
        foreach($options as $property => $value):
            $this->__set($property, $value);
        endforeach;
     }
     
      //  $this->options = Util::extend($this->options, $options);
  }
}