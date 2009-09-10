<?php


require_once('./../simpletest/autorun.php');
require_once('./../../lib/Geometry.class.php');
require_once('./../../lib/Geometry/Point.class.php');
require_once('./../../lib/Geometry/Collection.class.php');
require_once('./../../lib/Geometry/LineString.class.php');

class TestCollection extends UnitTestCase 
{
   
   function test_Collection_constructor()
   {    
      $coll = new Collection();
      
      $this->assertTrue($coll instanceof collection);
      $this->assertTrue($coll instanceof geometry);
      $this->assertTrue(get_class($coll), 'collection');   
      $this->assertTrue(is_array($coll->components));
      var_dump($coll);
  }

  function test_Collection_addComponents() 
  {

      $coll = new Collection();

      $coll->addComponents(null);
      $this->assertTrue($coll instanceof collection);
      $coll->addComponents(new Point(0,0));
      $coll->addComponents(new Point(10,10));
      $this->asserttrue(count($coll->components), 2);
        
    }
  
}