<?php


require_once('./../simpletest/autorun.php');
require_once('./../../lib/Geometry.class.php');
require_once('./../../lib/Geometry/Point.class.php');
require_once('./../../lib/Geometry/Collection.class.php');
require_once('./../../lib/Geometry/LineString.class.php');
require_once('./../../lib/Geometry/LinearRing.class.php');

class TestLinearRing extends UnitTestCase 
{
   private $components = array();
      
   function __construct()
   {
      array_push($this->components, new Point(10,15), new Point(0,0));
//      parent::__construct();
   }                        
   
   function test_LinearRing_constructor()
   {
      $ring = new LinearRing();
      $this->assertTrue($ring instanceof linearring);
      $this->assertTrue($ring instanceof collection);
      $this->assertTrue($ring instanceof geometry);
      $this->assertTrue(get_class($ring), 'linearring');
      
      $ring = new LinearRing($this->components);
      $this->assertTrue(count($this->components), 3);
      
   }
   
  function test_LinearRing_addComponent() {
      
      $ring = new LinearRing();

      $point = new Point(0,0);
      
      $ring->addComponents($point);
      $this->assertTrue(count($ring->components), 2);     
      $this->assertEqual($ring->components[0]->x, $point->x);
      $this->assertEqual($ring->components[0]->y, $point->y);
      $this->assertEqual($ring->components[0]->x, $ring->components[1]->x);
      $this->assertEqual($ring->components[0]->y, $ring->components[1]->y);
      

      
      $newPoint = new Point(10,10);
      $ring->addComponents($newPoint);
      $this->assertTrue(count($ring->components), 3);     
      $this->assertEqual($ring->components[1]->x, $newPoint->x);
      $this->assertEqual($ring->components[1]->y, $newPoint->y);
      $this->assertEqual($ring->components[0]->x, $ring->components[2]->x);
      $this->assertEqual($ring->components[0]->y, $ring->components[2]->y);
    
  }
}