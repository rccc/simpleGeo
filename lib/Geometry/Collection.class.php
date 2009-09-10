<?php

/* 
* Copyright (c) 2006-2008 MetaCarta, Inc., published under the Clear BSD
* license.  See http://svn.openlayers.org/trunk/openlayers/license.txt for the
* full text of the license. 
*/

/**
 * A collection of different Geometries
 * @package    simpleGeo
 * @subpackage Geometry
 * @author     http://openlayers.org/dev/doc/authors.txt
 * @version    
 */

class Collection extends geometry
{
  /**
   * 
   * @var array
   */
  
  public $components     = array();
  
  /**
   * 
   * @var string
   */
  
  public $componentTypes = "";
   
  /**
   *  
   * @param $components
   * @param $class
   * @return unknown_type
   */
  
  public function __construct($components = array(), $class=null)
  {
    if(!empty($components))
    {
      $this->addComponents($components); 
    }
    
    parent::__construct(isset($class)? $class : __CLASS__);
  }
  
  /**
   * 
   * @param $components array of Feature instance
   */
  
  public function addComponents($components)
  {
    if(!is_array($components)) $components = array($components); 
            
    foreach($components as $component):
        $this->addComponent($component);
    endforeach;
      
  }
  
  /**
   * 
   * @param $component Feature instance
   * @param $index int
   * @return bool
   */
  
  public function addComponent($component, $index=null)
  { 
    $added = false;
    
    //TODO traitement si component est !null ou componentType est null
    if(!empty($component))
    {
      //TODO set $component->parent
           
      $this->components[] = $component;     
      return $added = true;
    } 
  }
  
 /**
  * APIMethod: removeComponents
  * Remove components from this geometry.
  *
  * Parameters:
  * components - {Array(<OpenLayers.Geometry>)} The components to be removed
  */
  
  public function removeComponents($components) 
  {
     if(!is_array($components)) $components = array($components);
     
     foreach($components as $component):
        self::removeComponent($component);
     endforeach;
     
  }
  
 /**
  * Method: removeComponent
  * Remove a component from this geometry.
  *
  * Parameters:
  * component - {<OpenLayers.Geometry>}
  */
  
  public function removeComponent($component) 
  {  
     Util::removeItem($this->components, $component);
     // clearBounds() so that it gets recalculated on the next call
     // to this.getBounds();
     //this.clearBounds();
  }
  
  //TODO add getLength method
}
