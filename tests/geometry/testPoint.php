<?php


require_once('./../simpletest/autorun.php');
require_once('./../../lib/Geometry.class.php');
require_once('./../../lib/Geometry/Point.class.php');

class TestPoint extends UnitTestCase 
{

function test_Point_constructor() 
{  
    $x = 10;
    $y = 20;
    
    $point = new Point($x, $y);

    var_dump($point);
    
    $this->assertTrue($point instanceof point);
    $this->assertTrue($point instanceof geometry);
    $this->assertTrue(get_class($point), 'point');

    $this->assertNotNull($point->x);
    $this->assertNotNull($point->y);
    $this->assertNotNull($point->id);
    
  }

}