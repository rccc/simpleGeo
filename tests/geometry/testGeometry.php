<?php 


require_once('./../simpletest/autorun.php');
require_once('./../../lib/Geometry.class.php');

class TestGeometry extends UnitTestCase 
{
  function test_Geometry_constructor() 
  {
    $g = new Geometry();
    
    $this->assertEqual(get_class($g), 'geometry');
    $this->assertTrue(!empty($g->id));
  }
}