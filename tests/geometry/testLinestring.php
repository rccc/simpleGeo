<?php


require_once('./../simpletest/autorun.php');
require_once('./../../lib/Geometry.class.php');
require_once('./../../lib/Geometry/Point.class.php');
require_once('./../../lib/Geometry/Collection.class.php');
require_once('./../../lib/Geometry/LineString.class.php');

class TestLineString extends UnitTestCase 
{
   private $line = null;
   private $components = array();
      
   function __construct()
   {
     $components[] = new Point(10,15);
     $components[] = new Point(0,0);
   }                        
   
   function test_LineString_constructor()
   {
      $line = new LineString($this->component);
      
      $this->assertTrue($line instanceof linestring);
      $this->assertTrue($line instanceof collection);
      $this->assertTrue($line instanceof geometry);
      $this->assertTrue(get_class($line), 'linestring');
      
      $this->assertTrue(is_array($line->components));
      var_dump($line);
  }

//  function test_LineString_constructor (t) {
//      t.plan( 3 );
//      line = new OpenLayers.Geometry.LineString(components);
//      t.ok( line instanceof OpenLayers.Geometry.LineString, "new OpenLayers.Geometry.LineString returns line object" );
//      t.eq( line.CLASS_NAME, "OpenLayers.Geometry.LineString", "line.CLASS_NAME is set correctly");
//      // TBD FIXME, recursion
//      // t.eq( line.components, components, "line.components is set correctly");
//      t.eq( line.components.length, 2, "line.components.length is set correctly");
//  }

}