<?php

require_once(dirname(__FILE__) . '/simpletest/autorun.php');

require_once('./../lib/FirePHP/FirePHP.class.php');
require_once('./../lib/Util.class.php');
require_once('./../lib/Format.class.php');
require_once('./../lib/Format/KML.class.php');
require_once('./../lib/Format/KML.class.php');
require_once('./../lib/Feature.class.php');
require_once('./../lib/Geometry.class.php');
require_once('./../lib/Geometry/Point.class.php');
require_once('./../lib/Geometry/Collection.class.php');
require_once('./../lib/Geometry/LineString.class.php');
require_once('./../lib/Geometry/LinearRing.class.php');
require_once('./../lib/Geometry/Polygon.class.php');

class TestKML extends UnitTestCase 
{
//    private $kml;
  
    const test_content = '<kml xmlns="http://earth.google.com/kml/2.0"><Folder><name>OpenLayers export</name><description>Vector geometries from OpenLayers</description><Placemark id="KML.Polygon"><name>OpenLayers.Feature.Vector_344</name><description>A KLM Polygon</description><Polygon><outerBoundaryIs><LinearRing><coordinates>5.001370157823406,49.26855713824488 8.214706453896161,49.630662409673505 8.397385910100951,48.45172350357396 5.001370157823406,49.26855713824488</coordinates></LinearRing></outerBoundaryIs></Polygon></Placemark><Placemark id="KML.LineString"><name>OpenLayers.Feature.Vector_402</name><description>A KML LineString</description><LineString><coordinates>5.838523393080493,49.74814616928052 5.787079558782349,48.410795432216574 8.91427702008381,49.28932499608202</coordinates></LineString></Placemark><Placemark id="KML.Point"><name>OpenLayers.Feature.Vector_451</name><description>A KML Point</description><Point><coordinates>6.985073041685488,49.8682250149058</coordinates></Point></Placemark><Placemark id="KML.MultiGeometry"><name>SF Marina Harbor Master</name><description>KML MultiGeometry</description><MultiGeometry><LineString><coordinates>-122.4425587930444,37.80666418607323 -122.4428379594768,37.80663578323093</coordinates></LineString><LineString><coordinates>-122.4425509770566,37.80662588061205 -122.4428340530617,37.8065999493009</coordinates></LineString></MultiGeometry></Placemark></Folder></kml>';
    const test_style = '<kml xmlns="http://earth.google.com/kml/2.0"> <Placemark>    <Style> <LineStyle> <color>870000ff</color> <width>10</width> </LineStyle> </Style>  <LineString> <coordinates> -112,36 -113,37 </coordinates> </LineString> </Placemark></kml>';
    const test_style_fill = '<kml xmlns="http://earth.google.com/kml/2.0"> <Placemark>    <Style> <PolyStyle> <fill>0</fill> <color>870000ff</color> <width>10</width> </PolyStyle> </Style>  <LineString> <coordinates> -112,36 -113,37 </coordinates> </LineString> </Placemark></kml>';
    const test_nl = '<?xml version="1.0" encoding="utf-8"?><kml xmlns="http://www.opengis.net/kml/2.2">  <NetworkLink>    <Link>     <href>http://kml-samples.googlecode.com/svn/trunk/morekml/Network_Links/Targets/Network_Links.Targets.Simple.kml</href>    </Link></NetworkLink></kml>';
  
//    $kml = null;
    
//    function test_Format_KML_constructor() 
//    {  
//        $options = array("foo"=>"bar");
//          
//        $kml = new KML($options);       
//        
//        $this->assertTrue($kml instanceof Format);
//        $this->assertEqual($kml->options['foo'], "bar");
//        $this->assertEqual(method_exists($kml, "read"), true);
//      
//    }
//    
//    
//    function test_Format_KML_read()
//    {
//        $kml = new KML();
//        
//        $features = $kml->read(self::test_content);
//                
//        $this->assertEqual(count($features), 4);
//        $this->assertEqual($features[0]->geometry instanceof Polygon, true);
//  //      $this->assertEqual(count($features[0]->geometry->components[0]->components), 4);
//        $this->assertEqual($features[1]->geometry instanceof LineString, true);
//        $this->assertEqual(count($features[1]->geometry->components), 3);
//        $this->assertEqual($features[2]->geometry instanceof Point, 2);
//        $this->assertEqual($features[3]->geometry instanceof Collection, 3);      
//        
//    }
//    
//    function test_Format_KML_readCdataAttributes_20() 
//    {   
//        $kml = new KML();
//        $cdata = '<kml xmlns="http://earth.google.com/kml/2.0"><Document><Placemark><name><![CDATA[Pezinok]]></name><description><![CDATA[Full of text.]]></description><styleUrl>#rel1.0</styleUrl><Point> <coordinates>17.266666, 48.283333</coordinates></Point></Placemark></Document></kml>';
//        $features = $kml->read($cdata);
//        
////        var_dump($features[0]->attributes);
//        
//        $this->assertEqual($features[0]->attributes['description'], "Full of text.");
//        $this->assertEqual($features[0]->attributes['name'], "Pezinok");
//    }
    
    
    function test_Format_KML_networklink() 
    {    
        $kml = new KML(array('maxDepth'=>1));
        $features = $kml->read(self::test_nl);
        
        var_dump($features);
    }
}