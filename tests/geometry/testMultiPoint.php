<?php


require_once('./../simpletest/autorun.php');
require_once('./../../lib/Geometry.class.php');
require_once('./../../lib/Geometry/Point.class.php');
require_once('./../../lib/Geometry/Collection.class.php');
require_once('./../../lib/Geometry/MultiPoint.class.php');



class TestMultiPoint extends UnitTestCase 
{
  private $point = null; 
  
  function __construct()
  {
    $this->point = new Point(10, 15);    
  }

  
  function test_MultiPoint_constructor() 
  {      
      $multipoint = new MultiPoint();
      
      $this->assertTrue($multipoint instanceof MultiPoint);
      $this->assertTrue($multipoint instanceof geometry);
      $this->assertTrue(get_class($multipoint), 'multipoint');
      
      $multipoint = new MultiPoint(array($this->point));
      $this->assertTrue($multipoint instanceof MultiPoint);
      $this->assertTrue($multipoint instanceof geometry);  
      $this->asserttrue(count($multipoint->components), 1);      
  }

}