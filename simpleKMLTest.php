<?php
 
require_once('lib/Format/KML.class.php'); 

$options = array("extractStyles"=>true);

$test = new KML($options);

/** read **/ 
$test->read("KML_Samples.kml");

//Util::dump($test->features, "features");


/** write **/
$kml = $test->write($test->features);

Util::dump($kml, "kml !");



