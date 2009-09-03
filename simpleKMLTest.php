<?php
 
require_once('lib/Format/KML.class.php'); 

$options = array("foo"=>"bar");

$test = new KML($options);

$test->read("ex.kml");

Util::dump($test->features, "features");


