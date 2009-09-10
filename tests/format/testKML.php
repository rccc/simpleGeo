<?php


require_once('./../simpletest/autorun.php');

//require_once('./../lib/Util.class.php');
require_once('./../../lib/Format.class.php');
require_once('./../../lib/Format/KML.class.php');
require_once('./../../lib/Feature.class.php');
require_once('./../../lib/Geometry.class.php');
require_once('./../../lib/Geometry/Point.class.php');
require_once('./../../lib/Geometry/Collection.class.php');
require_once('./../../lib/Geometry/LineString.class.php');
require_once('./../../lib/Geometry/LinearRing.class.php');
require_once('./../../lib/Geometry/MultiPoint.class.php');
require_once('./../../lib/Geometry/Polygon.class.php');


class TestKML extends UnitTestCase 
{
    private $kml;
  
    const test_content = '<kml xmlns="http://earth.google.com/kml/2.0"><Folder><name>OpenLayers export</name><description>Vector geometries from OpenLayers</description><Placemark id="KML.Polygon"><name>OpenLayers.Feature.Vector_344</name><description>A KLM Polygon</description><Polygon><outerBoundaryIs><LinearRing><coordinates>5.001370157823406,49.26855713824488 8.214706453896161,49.630662409673505 8.397385910100951,48.45172350357396 5.001370157823406,49.26855713824488</coordinates></LinearRing></outerBoundaryIs></Polygon></Placemark><Placemark id="KML.LineString"><name>OpenLayers.Feature.Vector_402</name><description>A KML LineString</description><LineString><coordinates>5.838523393080493,49.74814616928052 5.787079558782349,48.410795432216574 8.91427702008381,49.28932499608202</coordinates></LineString></Placemark><Placemark id="KML.Point"><name>OpenLayers.Feature.Vector_451</name><description>A KML Point</description><Point><coordinates>6.985073041685488,49.8682250149058</coordinates></Point></Placemark><Placemark id="KML.MultiGeometry"><name>SF Marina Harbor Master</name><description>KML MultiGeometry</description><MultiGeometry><LineString><coordinates>-122.4425587930444,37.80666418607323 -122.4428379594768,37.80663578323093</coordinates></LineString><LineString><coordinates>-122.4425509770566,37.80662588061205 -122.4428340530617,37.8065999493009</coordinates></LineString></MultiGeometry></Placemark></Folder></kml>';
    const test_style = '<kml xmlns="http://earth.google.com/kml/2.0"><Placemark><Style> <LineStyle> <color>870000ff</color> <width>10</width> </LineStyle> </Style>  <LineString> <coordinates> -112,36 -113,37 </coordinates> </LineString> </Placemark></kml>';
    const test_style_fill = '<kml xmlns="http://earth.google.com/kml/2.0"><Placemark><Style><PolyStyle><fill>0</fill><color>8700cccc</color> <width>10</width> </PolyStyle> </Style>  <LineString> <coordinates> -112,36 -113,37 </coordinates> </LineString> </Placemark></kml>';
    const test_nl = '<?xml version="1.0" encoding="utf-8"?><kml xmlns="http://www.opengis.net/kml/2.2"><NetworkLink><Link><href>http://kml-samples.googlecode.com/svn/trunk/morekml/Network_Links/Targets/Network_Links.Targets.Simple.kml</href></Link></NetworkLink></kml>';
    const ext_data = '<kml xmlns="http://earth.google.com/kml/2.2"><Document><Placemark><styleUrl>#default</styleUrl><ExtendedData><Data name="all_bridges"><displayName><![CDATA[all bridges]]></displayName><value><![CDATA[3030]]></value></Data><Data name="latitude"><displayName><![CDATA[latitude]]></displayName><value><![CDATA[43]]></value></Data><Data name="longitude"><displayName><![CDATA[longitude]]></displayName><value><![CDATA[-107.55]]></value></Data><Data name="functionally_obsolete__percent"><displayName><![CDATA[functionally obsolete, percent]]></displayName><value><![CDATA[8]]></value></Data><Data name="structurally_deficient__percent"><displayName><![CDATA[structurally deficient, percent]]></displayName><value><![CDATA[13]]></value></Data><Data name="state"><displayName><![CDATA[state]]></displayName><value><![CDATA[Wyoming]]></value></Data></ExtendedData><Point><coordinates>-107.55,43.0</coordinates></Point></Placemark></Document></kml>';
    
    function test_Format_KML_constructor() 
    {  
        $options = array("foo"=>"bar", "foldersDesc"=>"zest", "extractStyles"=>true);
          
        $kml = new KML($options);       
        
        $this->assertTrue($kml instanceof Format);
        $this->assertEqual($kml->foo, "bar");
        $this->assertEqual($kml->foldersDesc, "zest");
        $this->assertEqual($kml->extractStyles, true);
        $this->assertEqual(method_exists($kml, "read"), true);
      
    }
    
    
    function test_Format_KML_read()
    {
        $kml = new KML();
        
        $features = $kml->read(self::test_content);
                
        $this->assertEqual(count($features), 4);
        $this->assertEqual($features[0]->geometry instanceof Polygon, true);
        //$this->assertEqual(count($features[0]->geometry->components[0]->components), 4);
        $this->assertEqual($features[1]->geometry instanceof LineString, true);
        $this->assertEqual(count($features[1]->geometry->components), 3);
        $this->assertEqual($features[2]->geometry instanceof Point, 2);
        $this->assertEqual($features[3]->geometry instanceof Collection, 3);      
        
    }
    
    function test_Format_KML_readCdataAttributes_20() 
    {   
        $kml = new KML();
        $cdata = '<kml xmlns="http://earth.google.com/kml/2.0"><Document><Placemark><name><![CDATA[Pezinok]]></name><description><![CDATA[Full of text.]]></description><styleUrl>#rel1.0</styleUrl><Point> <coordinates>17.266666, 48.283333</coordinates></Point></Placemark></Document></kml>';
        $features = $kml->read($cdata);
        
        
        $this->assertEqual($features[0]->attributes['description'], "Full of text.");
        $this->assertEqual($features[0]->attributes['name'], "Pezinok");
    }
    
    
    function test_Format_KML_networklink() 
    {    
        $kml = new KML(array('maxDepth'=>1));
        $features = $kml->read(self::test_nl);
        $url = array_keys($kml->fetched);
       
        $this->assertEqual($url[0], "http://kml-samples.googlecode.com/svn/trunk/morekml/Network_Links/Targets/Network_Links.Targets.Simple.kml");
 
    }
    
    function test_Format_KML_write() 
    {
        // make sure id, name, and description are preserved
        $kmlExpected = self::test_content;
        
        $options = array(
                        "folderName"=> "OpenLayers export",
                        "foldersDesc"=> "Vector geometries from OpenLayers"
                        );

        $format = new KML($options);
        $features = $format->read($kmlExpected);
        $kmlOut = $format->write($features);
        
        $kmlOut = preg_replace("/<\?[^>]*\?>/", '', $kmlOut); // Remove XML Prolog
               
        $this->assertTrue($kmlExpected == trim($kmlOut));
        $this->assertEqual($kmlExpected, trim($kmlOut));
    }
    
    function test_Format_KML_extractStyle() 
    {
        $f = new KML();
        $features = $f->read(self::test_style);
        $this->assertTrue(empty($features[0]->style));    
    } 
  
    function test_Format_KML_extractStyleFill() 
    {    
        $f = new KML(array("extractStyles"=> true));

        $features = $f->read(self::test_style);
        $this->assertEqual($features[0]->style['fillColor'],"#ff0000");
        
        $f = new KML(array("extractStyles"=> true));   
        $features = $f->read(self::test_style_fill);    
        var_dump($features[0]->style);
        $this->assertEqual($features[0]->style['fillColor'],"none");           
    } 
    
    function test_Format_KML_getStyle() 
    {   
        $style = array("t"=> true);
        $f = new KML();
        $f->styles = array("test"=> "style");
        $gotStyle = $f->getStyle('test');
        $gotStyle["t"] = false;
        $this->assertNotNull($style['t']);
    }
    
    function test_Format_KML_extendedData() 
    {
        $f = new KML();
        $features = $f->read(self::ext_data);
        $this->assertEqual($features[0]->attributes["all_bridges"]["value"], "3030");
        $this->assertEqual($features[0]->attributes["all_bridges"]["displayName"], "all bridges");        
    }
}