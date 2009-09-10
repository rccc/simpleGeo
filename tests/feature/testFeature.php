<?php


require_once('./../simpletest/autorun.php');

require_once('./../../lib/Feature.class.php');
require_once('./../../lib/Geometry.class.php');
require_once('./../../lib/Geometry/Point.class.php');



class TestFeature extends UnitTestCase 
{
  function test_Feature_constructor() 
  {   
      
      $point = new Point(10, 20);
      $layer = array();
      $iconURL = 'http://boston.openguides.org/features/ORANGE.png';
//      $iconSize = new OpenLayers.Size(12, 17);
      $data =  array("iconURL"=> $iconURL);
     
      
      $feature = new Feature($point, array(), data);
      
      $this->assertTrue($feature instanceof feature);
      $this->assertNotNull($feature->id);
      
  }

}