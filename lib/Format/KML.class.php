<?php
/* 
* Copyright (c) 2006-2008 MetaCarta, Inc., published under the Clear BSD
* license.  See http://svn.openlayers.org/trunk/openlayers/license.txt for the
* full text of the license. 
*/

/**
 * __autoload
 * should be removed from here when other format(s) will be added
 * @param $className
 * 
 */

function __autoload($className)
{
  $paths = array("./lib/", "./lib/Format/", './lib/Geometry/');
  
  foreach($paths as $path):
    $path = $path . $className . '.class.php' ;
    if(file_exists($path)) 
    {
      require $path;
      break; 
    }

  endforeach; 
}

/**
 * KML is a subclass of format - not a subclass of XML as in OpenLayers API
 * many of the XML class methods are implemeted in PHP
 * we don't create the XML subclass of Format since we don't need it yet
 * 
 * @package    simpleGeo
 * @subpackage Format
 * @author     http://openlayers.org/dev/doc/authors.txt
 * @version    
 */

class KML extends Format 
{
  /**
  * kmlns
  * KML Namespace to use. Defaults to 2.0 namespace.
  * @var    String 
  */
  
  public $kmlns = "http://earth.google.com/kml/2.0";
  
  /** 
  * placemarksDesc
  * Name of the placemarks.  Default is "No description available."
  * @var String 
  */
  
  public $placemarksDesc =  "No description available";
  
  /** 
    * foldersName
    * Name of the folders.  Default is "OpenLayers export."
    * @var String 
    */
  
  public $foldersName = "OpenLayers export";
  
  /** 
    * foldersDesc
    * Description of the folders. Default is "Exported on [date]."
    * @var String 
    */
  
  public $foldersDesc = null; //"Exported on " . date('l jS \of F Y h:i:s A');
  
  /**
    * extractAttributes
    * Extract attributes from KML.  Default is true.
    * Extracting styleUrls requires this to be set to true
    * @var Boolean 
    *           
    */
  
  public $extractAttributes = true;
  
  /**
    * extractStyles
    * Extract styles from KML.  Default is false.
    * Extracting styleUrls also requires extractAttributes to be set to true
    * @var Boolean 
    *           
    */
  
  public $extractStyles = false;
  
  /**
    * internalns
    * KML Namespace to use -- defaults to the namespace of the
    * Placemark node being parsed, but falls back to kmlns.
    * @var String  
    */
  
  public $internalns = null;
  
  /**
    * features
    * Array of features
    * @var Array 
    *     
    */
  
  public $features = array();
  
  /**
    * styles
    * Storage of style objects
    * @var Object 
    *     
    */
  
  private $styles = array();
  
  /**
    * styleBaseUrl
    * @var String
    */
  
  public $styleBaseUrl =  "";
  
  /**
    * fetched
    * Storage of KML URLs that have been fetched before
    * in order to prevent reloading them.
    * public so that we can access it from tests
    * @var Object 
    */
  
  public $fetched = array();
  
  /**
    * maxDepth
    * Maximum depth for recursive loading external KML URLs 
    * Defaults to 0: do no external fetching !!
    * @var Integer 
    */
  
  public $maxDepth = 1;
  
  /**
    * $regExes
    * array of regexp
    * @var array
    */
  
  private $regExes = array(
                              "trimSpace"      => "/^\s*|\s*$/",
                              "removeSpace"    => "/\s*/",
                              "splitSpace"     => "/\s+/",
                              "trimComma"      => "/\s*,\s*/",
                              "kmlColor"       => "/(\w{2})(\w{2})(\w{2})(\w{2})/",
                              "kmlIconPalette" => "/root:\/\/icons\/palette-(\d+)(\.\w+)/",
                              "straightBracket"=> "/\$\[(.*?)\]/g"
                            );
                            
  /**
    * $doc
    * reference to a DOMDocument instance
    * @var object
    */                            
  
  private $doc;
  
  
  public function __construct($options=array())
  {
//    $options = array("extractStyles"=>true);
    parent::__construct($options);
    $this->doc = new DOMDocument();
  }
  
  /**
  * Read data from a string, and return a list of features. 
  * 
  * Parameters: 
  * @param  string or DOMElement data to read/parse.
  * @return object  List of Feature instances.
  */

  public function read($uri)
  {

    if(empty($uri)) return false;
        
    $ext = substr(strtolower(strrchr(basename($uri), ".")), 1);  
    
    if(!empty($ext) && $ext == "kml")
        $this->doc->load($uri);
    else
        $this->doc->loadXML($uri);

    return $this->_parseData();
  }
  
  /**
   * Read data from a string, and return a list of features. 
   * @return array of Feature instances
   */
  
  private function _parseData()
  {
    $types = array( "NetworkLink","Link", "Style", "StyleMap", "Placemark");    
    
    foreach($types as $type):   
          
          $nodes = $this->doc->getElementsByTagName($type); //getElementsByTagNameNS ?
          
          if($nodes->length == 0 ) continue; // skip to next iteration
//             Util::log($nodes->item(0)->nodeName, 'TYPE');   
          switch(strtolower($type)):  
            // Fetch external links 
             case "link":
             case "networklink":          
               $this->_parseLinks($nodes, $options);
               break;
//                 
             // parse style information
             case "style":
               if($this->extractStyles == true) 
               {
                 $this->_parseStyles($nodes, $options);
               }
               break;
//               
//             case "stylemap":
//               if(isset($this->_extractStyles)) {
//                 $this->_parseStyleMaps($nodes, $options);
//               }
//               break;
      
            // parse features
            case "placemark":
              $this->_parseFeatures($nodes, $options);
              break;
                 
          endswitch;
                
    endforeach;
    
    return $this->features;
  }
  
  /**
   * Finds URLs of linked KML documents and fetches them
   * @param $nodes array of DOMElement instances
   * @param $options array
   */
  
  private function _parseLinks($nodes, $options = array())
  {

    // Fetch external links <NetworkLink> and <Link>
    // Don't do anything if we have reached our maximum depth for recursion
    
//    Util::log($this->options['depth'], '$this->options[depth]');
//    Util::log($this->options['depth'] > $this->maxDepth, "$this->options['depth'] > $this->maxDepth");
    
    if($this->options['depth'] > $this->maxDepth)
        return false;  
   
    //increase depth
    $this->options['depth']++;    
        
    foreach($nodes as $node):
//        Util::log($node->nodeName, 'nodeName');
        
        $href =  $this->_parseProperty($node, '*', 'href');
        
        if(!empty($href) && !isset($this->fetched[$href]))
        {
          $this->fetched[$href] = true;
          
          $data = $this->_fetchLink($href);
                  
          if(!empty($data))
              $this->read($data);
        }
        
    endforeach;
    
  }
  
  /**
   * Fetches a URL and returns the result
   * @param $href string
   * @return string  
   */
  
  private function _fetchLink($href)
  {
    $request = file_get_contents($href);
    if(!empty($request))
        return $request;    
  }
  
  
  /**
  * Looks for <Style> nodes in the data and parses them
  * Also parses <StyleMap> nodes, but only uses the 'normal' key
  * 
  * @param nodes    - array of {DOMElement} data to read/parse.
  * @param options  - array
  * 
  */
  
  private function _parseStyles($nodes, $options=array())
  {
      foreach($nodes as $node):
      
        $style= $this->_parseStyle($node);
        
        if(empty($tyle['id'])) { //!!!!
          $this->styles[$style['id']] = $style;
        }
        
      endforeach;

  }
  
  private function _parseStyle($node)
  {
    $style = array();
    
	$types = array("LineStyle", "PolyStyle", "IconStyle"/*, "BalloonStyle"*/);
	
	foreach($types as $type):
		$styleTypeNode = $node->getElementsByTagName($type);
        switch(strtolower($type)):
    
          case "linestyle":
//             Util::log("linestyle");     
            $color = $this->_parseProperty($node, "*", "color");
            
            if(!empty($color))
            {
                preg_match_all($this->regExes["kmlColor"], $color, $matches);
                $alpha = $matches[1][0];
                $style["strokeOpacity"] = $alpha;
                
                
                $b = $matches[2][0];
                $g = $matches[3][0];
                $r = $matches[4][0];
                
                $style["strokeColor"] = "#" . $r . $g . $b;              
                
            }
            
            $width = $this->_parseProperty($node, "*", "width");
            
            if(!empty($width)) 
            {
              $style["strokeWidth"] = $width;
            }
                        
            
          break;
  
          case "polystyle":
//             Util::log('polyStyle');
            $color = $this->_parseProperty($node, "*", "color");
            if(!empty($color))
            {
                preg_match_all($this->regExes["kmlColor"], $color, $matches);
                $alpha = $matches[1][0];
                $style["fillOpacity"] = $alpha;
                
                // rgb colors (google uses bgr)
                $b = $matches[2][0];
                $g = $matches[3][0];
                $r = $matches[4][0];
                $style["fillColor"] = "#" . $r . $g . $b;
            }
            
            // Check is fill is disabled
            $fill = $this->_parseProperty($node, "*", "fill");
            if($fill == "0") 
            {
                $style["fillColor"] = "none";
            }
            
            break;   
            
            case "iconstyle":
              // set scale
              $scale = round($this->_parseProperty($node, "*", "scale"));
              
              $scale = !empty($scale)? $scale : 1;
                
              // set default width and height of icon
              $width = 32 * $scale;
              $height = 32 * $scale;
  
              $iconNode = $node->getElementsByTagNameNS("*", "Icon");
              
              if($iconNode->length > 0)
              {
                $iconNode = $iconNode->item(0);
                
//                 Util::log($iconNode->nodeValue, 'iconNode');
                
                $href = $this->_parseProperty($iconNode, "*", "href");
                

                
                if (!empty($href)) 
                {                                                                  
                  $w = $this->_parseProperty($iconNode, "*", "w");
                  $h = $this->_parseProperty($iconNode, "*", "h");
  
                  
                  // Settings for Google specific icons that are 64x64
                  // We set the width and height to 64 and halve the
                  // scale to prevent icons from being too big
                  $google = "http://maps.google.com/mapfiles/kml";
                  
                  $startWith = strstr($href, $google);

                  
                  if(!empty($startWith) && empty($w) && empty($h)) 
                  {
                    $w = 64;
                    $h = 64;
                    $scale = $scale / 2;
                  }
                      
                  // if only dimension is defined, make sure the
                  // other one has the same value
                  $w = !empty($w)? $w : $h;
                  $h = !empty($h)? $h : $w;
 
                  if(!empty($w)){
                    $width = $w * $scale;
                  }

                  if(!empty($h)) {
                    $height = $h * $scale;
                  }
             
                  // support for internal icons 
                  //    (/root://icons/palette-x.png)
                  // x and y tell the position on the palette:
                  // - in pixels
                  // - starting from the left bottom
                  // We translate that to a position in the list 
                  // and request the appropriate icon from the 
                  // google maps website
                  //TODO
                      
                      
                  $style["graphicOpacity"] = 1; // fully opaque
                  $style["externalGraphic"] = $href;
                }
              }
              
              // hotSpots define the offset for an Icon
              $hotSpotNode = $node->getElementsByTagNameNS("*", "hotSpot");
              
              
              
              if ($hotSpotNode->length > 0) 
              {
                  $hotSpotNode = $hotSpotNode->item(0);
                      
                  $x = $hotSpotNode->getAttribute("x");
                  $y = $hotSpotNode->getAttribute("y");

                  $xUnits = $hotSpotNode->getAttribute("xunits");
                  if ($xUnits == "pixels") {
                      $style["graphicXOffset"] = -$x * $scale;
                  }
                  elseif ($xUnits == "insetPixels") {
                      $style["graphicXOffset"] = -$width + ($x * $scale);
                  }
                  elseif ($xUnits == "fraction") {
                      $style["graphicXOffset"] = -$width * $x;
                  }

                  $yUnits = $hotSpotNode->getAttribute("yunits");
                  if($yUnits == "pixels") {
                      $style["graphicYOffset"] = -$height + ($y * $scale) + 1;
                  }
                  elseif ($yUnits == "insetPixels") {
                      $style["graphicYOffset"] = -($y * $scale) + 1;
                  }
                  elseif ($yUnits == "fraction") {
                      $style["graphicYOffset"] =  -$height * (1 - $y) + 1;
                  }
              }

              $style["graphicWidth"] = $width;
              $style["graphicHeight"] = $height;
                            
            break;
            
            //TODO BALLOON STYLE
          endswitch;
                
	endforeach;
    
    // Some polygons have no line color, so we use the fillColor for that
    if (empty($style["strokeColor"]) && !empty($style["fillColor"]))
        $style["strokeColor"] = $style["fillColor"];
    

    $id = $node->getAttribute("id");
    if (!empty($id) && !empty($style)) {
        $style['id'] =  $id;  // TODO : CHECK THIS
    }
    return $style;
  }
  
     /**
     * Looks for <Style> nodes in the data and parses them
     * Also parses <StyleMap> nodes, but only uses the 'normal' key
     * 
     * @param nodes    - {Array} of {DOMElement} data to read/parse.
     * @param options  - {Object} Hash of options
     * 
     */
  
    private function _parseStyleMaps($nodes, $options = array())
    {
        // Only the default or "normal" part of the StyleMap is processed now
        // To do the select or "highlight" bit, we'd need to change lots more
        
        foreach($nodes as $node):
            
            $pairs = $node->getElementsByTagNameNS("*", "Pair");
            $id = $node->getAttribute('id');
            
            foreach($pairs as $pair):
                // Use the shortcut in the SLD format to quickly retrieve the 
                // value of a node. Maybe it's good to have a method in 
                // Format.XML to do this
                $key = $this->_parseProperty($pair, "*", "key");
                $styleUrl = $this->_parseProperty($pair, "*", "styleUrl");  
                
                if (!empty($styleUrl) && $key == "normal") {
                  $this->styles[$this->styleBaseUrl . "#" . $id]  =$this->styles[$this->styleBaseUrl . $styleUrl];//TODO CHECK ORGINAL CODE
                }

//                 if (!empty($styleUrl) && $key == "highlight") {
//                     // TODO: implement the "select" part
//                 }
                
            endforeach;
            
        endforeach;
        

    }

  
  /**
   * Convenience method to find a node and return its value
   * @param $xmlNode DOMElement
   * @param $namespace string
   * @param $tagName string
   * @return string
   */
  
  private function _parseProperty($xmlNode, $namespace, $tagName)
  { 
    
     $value = null;
     $nodeList = $xmlNode->getElementsByTagNameNS($namespace, $tagName);

     //TODO implements a try / catch
     
     if($nodeList->length > 0) $value = $nodeList->item(0)->nodeValue;
     
     return $value;
  }
  
  /**
   * Loop through all Placemark nodes and parse them.
   * Will create a list of features
   * @param $nodes DOMElement
   * @param $options array
   */
  
  private function _parseFeatures($nodes, $options=null)
  {
      if(empty($nodes)) return false;
      
      $features = array();
      
      foreach($nodes as $node):

        static $i = 0;
        
        $features[$i] = $this->_parseFeature($node);
                       
    
        if(!empty($features[$i]))
        {
            //TODO check for 2nd arguments options        
            //$features[$i]->style = array();
            
            if($this->extractStyles == true 
              && !empty($features[$i]->attributes)
                && isset($features[$i]->attributes['styleUrl']))
            {
                $features[$i]->style = $this->getStyle($feature->attributes->styleUrl);

            } 
  
            if($this->extractStyles == true)
            {
//                var_dump($features[$i]->style);
                // Make sure that <Style> nodes within a placemark are 
                // processed as well
                $inlineStyleNode = $node->getElementsByTagNameNS("*","Style");
                $inlineStyleNode = $inlineStyleNode->item(0);
                
                if (!empty($inlineStyleNode)) 
                {
                    $inlineStyle= $this->_parseStyle($inlineStyleNode);

                    if (!empty($inlineStyle)) 
                    {
                        $features[$i]->style = Util::extend($feature[$i]->style, $inlineStyle);
                    }
                }
            }
        }
        else
        {
           throw new Exception('Bad Placemark');
        }
   
        $i++;    
           
      endforeach;
     
      $this->features = array_merge($this->features, $features);
      
  }
  
  /**
   * This function is the core of the KML parsing code in OpenLayers.
   * It creates the geometries that are then attached to the returned
   * feature, and calls parseAttributes() to get attribute data out.
   * 
   * @param $node DOMElement
   * @return object Feature instance
   */
  
  private function _parseFeature($node)
  {
    $orders = array("MultiGeometry", "Polygon", "LineString", "Point");
     
    foreach($orders as $order):
        
        $this->internalns = !empty($node->namespaceURI)? $node->namespaceURI : $this->kmlns;    
            
        $nodeList = $node->getElementsByTagNameNS($this->internalns, $order);
        
        if($nodeList->length > 0)
        {              
            $geometry = $this->_parseGeometry($node, $order);
            
            //TODO implements projection check
            
            // stop looking for different geometry types
            break;
        }
    
    endforeach;

    // construct feature (optionally with attributes)
    if(isset($this->extractAttributes)) $attributes = $this->_parseAttributes($node);

    $feature = new Feature($geometry, $attributes, $this->styles);

    // try to set 'fid' property
    if($node->getAttribute('id'))
      $fid = $node->getAttribute('id');
    elseif($node->getAttribute('name'))
      $fid = $node->getAttribute('name');

    if(!empty($fid)) $feature->fid = $fid;   
    
    return $feature;
  }

  /**
   * Method: getStyle
   * Retrieves a style from a style hash using styleUrl as the key
   * If the styleUrl doesn't exist yet, we try to fetch it 
   * Internet
   * 
   * Parameters: 
   * styleUrl  - {String} URL of style
   * options   - {Object} Hash of options 
   *
   * Returns:
   * {Object}  - (reference to) Style hash
   */
  public function getStyle($styleUrl, $options=array()) 
  { 
    $styleBaseUrl = Util::removeTail($styleUrl);

    $newOptions = Util::extend(array(), $options);
    
    //TODO newOptions.depth++;
    
    $newOptions['styleBaseUrl'] = $styleBaseUrl;

    // Fetch remote Style URLs (if not fetched before) 
    if (empty($this->styles['styleUrl'])  
            && strpos($styleUrl, "#") === 0 
//              && newOptions.depth <= this.maxDepth
                && empty($this->fetched[styleBaseUrl])) 
    {
        $data = $this->_fetchLink($styleBaseUrl);
        if(!empty($data)) $this->_parseData($data, $newOptions);        
    }

    // return requested style
    $style = Util::extend(array(), $this->styles['styleUrl']);
    return $style;
    }
  
  /**
   * Given a KML node representing a geometry, create an OpenLayers Feature 
   * for this geometry.
   * 
   * @param $node DOMElement
   * @param $geometry
   * @param $ring
   * @return object a Geometry instance
   */
  
  private function _parseGeometry($node, $geometry, $ring=null)
  {
    $nodeList = null;
   
    switch($geometry):
             
        case "Point":
          $nodeList = $node->getElementsByTagNameNS($this->internalns, 'coordinates'); 
          $coords = split(',', $nodeList->item(0)->nodeValue);        
          
          return new Point($coords[0], $coords[1]);         
          break;
          
        case "LineString":
          $line = null;                 
          $nodeList = $node->getElementsByTagNameNS($this->internalns, 'coordinates');
//           $coords = split(',', $nodeList->item(0)->nodeValue);   ne doit pas servir
                            
          if($nodeList->length > 0 )
          {
            $coordString = $nodeList->item(0)->childNodes->item(0)->nodeValue;
            
            $coordString = preg_replace($this->regExes['trimSpace'], '', $coordString);
            $coordString = preg_replace($this->regExes['trimComma'], ',', $coordString);          
            $pointList = preg_split($this->regExes['splitSpace'], $coordString);
            
            $points = array();
            
            foreach($pointList as $coords):
            
               $coords = preg_split('/,/', $coords);
               
               if(count($coords) > 0 )
                  $points[] = new Point($coords[0], $coords[1]);     //TODO preserve third dimension
               else
                 throw new Exception('Bad LineString point coordinates' . $coords); 
               
            endforeach;
            
            if(count($pointList) > 0){                         
              $line =  isset($ring)? new LinearRing($points) : new LineString($points);            
            }
            else
              throw new Exception('Bad LineString coordinates:' . $coordString);

            return $line;
          }
          
          break;
          
        case "Polygon":
          $nodeList = $node->getElementsByTagNameNS($this->internalns, 'LinearRing');
          $components = array();
          
          if($nodeList->length > 0)
          {
             $ring = null;
             
             foreach($nodeList as $node):     
                    
                 $ring = $this->_parseGeometry($node, 'LineString', true);
                 
                 if(is_array($ring->components)) 
                   $components[] = $ring;
                 else
                   throw new Exception("Bad LinearRing geometry");
                                                         
             endforeach;
          }
          
          return new Polygon($components);
          
          break;
          
          
        case "MultiGeometry":
          $parser = null;
          $parts = array();
          $children = $node->childNodes;
          
          foreach($children as $child):
          
              $nodeType = $child->nodeType;             
              if($nodeType == 1)
              {
                if(!empty($child->prefix))
                {
                  $type = split(':', $child->nodeName);         
                } 
                
                $type = is_array($type) && !empty($type[1])? $type[1] : $child->nodeName;
                
                $parser = $this->_parseGeometry($child, $type); 
                
                if(!empty($parser))
                {
                  $parts[] = $parser;
                }
              }
          
          endforeach;
                    
          return new Collection($parts);
          
          break;
          
    endswitch;
    
  }
  
  /**
   * parse a DOMelement for attributes
   * @param $node DOMElement
   * @return array lisy of extracted attributes
   */
  
  private function _parseAttributes($node)
  {
      $attributes = array();
      
      $edNodes = $node->getElementsByTagName("ExtendedData");
      
      if($edNodes->length > 0){
            $attributes = $this->_parseExtendedData($edNodes->item(0));
      }      
      
      // assume attribute nodes are type 1 (ELEMENT_NODE) children with a type 3 (TEXT_NODE) or 4 child (CDATA_SECTION_NODE)
      $child = null; 
      $grandchildren = array();
      $grandchild = null;
      $children = $node->childNodes;
      
      foreach($children as $child){
        if($child->nodeType == 1)
        {
          $grandchildren = $child->childNodes;
          if($grandchildren->length == 1 || $grandchildren->length == 3) 
          {
            $grandchild = null;
            switch ($grandchildren->length) {
              case 1:
                $grandchild = $grandchildren->item(0);
                break;
              case 3:
              default:
                $grandchild = $grandchildren->item(1);
                break;
            }
            
            if($grandchild->nodeType == 3 || $grandchild->nodeType == 4) 
            {
       
              if(!empty($child->prefix)) $prefix = split(':', $child->nodeName);
       
              $name = is_array($prefix) && !empty($prefix[1])? $prefix[1] : $child->nodeName;
       
              // OpenLayers : var value = OpenLayers.Util.getXmlNodeValue(grandchild);
              // but when testing we noticed that  $child->nodeValue == $grandchild->nodeValue
              if(empty($child->nodeValue) && !empty($grandchild->nodeValue))
                $value = $grandchild->nodeValue;
              else
                $value = $child->nodeValue;
              
              if(!empty($value)) 
              {
                $value = preg_replace($this->regExes['trimSpace'], '', $value);
                $attributes[$name] = $value;
              }
            }
          } 
        }
      }
      
      return $attributes;
      
  }
  
  /**
   * Parse ExtendedData from KML. No support for schemas/datatypes
   * @param $node DOMElement
   * @return array 
   */
  
  private function _parseExtendedData($node)
  {
     
    $attributes = array();
    
    $dataNodes = $node->getElementsByTagName("Data");
    
    foreach($dataNodes as $data):
    
        $key = $data->getAttribute("name");
              
        $ed = array();
        
        $valueNode = $data->getElementsByTagName("value");
                
        if($valueNode->length > 0 ) 
            $ed['value'] = $valueNode->item(0)->nodeValue;
        
        $nameNode = $data->getElementsByTagName("displayName");

        if($nameNode->length > 0)
            $ed['displayName'] = $nameNode->item(0)->nodeValue;
        
        $attributes[$key] = $ed;
        
    endforeach;
    
    return $attributes;
    
  }
  
  /**
   * APIMethod: write
   * Accept Feature Collection, and return a string. 
   * 
   * Parameters:
   * features - {Array(<OpenLayers.Feature.Vector>} An array of features.
   *
   * Returns:
   * {String} A KML string.
   */
  
  public function write($features) 
  {
      if(!is_array($features)) $features = array($features);
      
      $kml = $this->doc->createElementNS($this->kmlns, "kml");
      $folder = $this->_createFolderXML();
      
      foreach($features as $feature):
          $folder->appendChild($this->_createPlacemarkXML($feature));
      endforeach;
      
      $kml->appendChild($folder);

      return $this->doc->saveXML();
  }

  
  /**
   * Method: createFolderXML
   * Creates and returns a KML folder node
   * 
   * Returns:
   * {DOMElement}
   */
  
  private function _createFolderXML()
  {
      // Folder name
      $folderName = $this->doc->createElementNS($this->kmlns, "name");
      $folderNameText = $this->doc->createTextNode($this->foldersName); 
      $folderName->appendChild($folderNameText);
  
      // Folder description
      $folderDesc = $this->doc->createElementNS($this->kmlns, "description");        
      $folderDescText = $this->doc->createTextNode($this->foldersDesc); 
      $folderDesc->appendChild($folderDescText);
  
      // Folder
      $folder = $this->doc->createElementNS($this->kmlns, "Folder");
      $folder->appendChild($folderName);
      $folder->appendChild($folderDesc);
      
      return $folder;
  }
  
  /**
   * Method: createPlacemarkXML
   * Creates and returns a KML placemark node representing the given feature. 
   * 
   * Parameters:
   * feature - {<OpenLayers.Feature.Vector>}
   * 
   * Returns:
   * {DOMElement}
   */
  
  private function _createPlacemarkXML($feature) 
  {          
      if(empty($feature)) return false;
      
      // Placemark name
      $placemarkName = $this->doc->createElementNS($this->kmlns, "name");
      
      $name = !empty($feature->attributes->name)? $feature->attributes->name : $feature->id;
                  
      $placemarkName->appendChild($this->doc->createTextNode($name));

      // Placemark description
      $placemarkDesc = $this->doc->createElementNS($this->kmlns, "description");
      $desc = !empty($feature->attributes->description) ? $feature->attributes->description : $this->placemarksDesc;
      
      $placemarkDesc->appendChild($this->doc->createTextNode($desc));
      
      // Placemark
      $placemarkNode = $this->doc->createElementNS($this->kmlns, "Placemark");
      
      if(!empty($feature->fid)) $placemarkNode->setAttribute("id", $feature->fid);
      
      $placemarkNode->appendChild($placemarkName);
      $placemarkNode->appendChild($placemarkDesc);
            
      // Geometry node (Point, LineString, etc. nodes)
      $geometryNode = $this->_buildGeometryNode($feature->geometry);
     
      if($geometryNode instanceof DOMElement) $placemarkNode->appendChild($geometryNode);        
      
      // TBD - deal with remaining (non name/description) attributes.
      return $placemarkNode;
  }
  
  /**
   * Method: buildGeometryNode
   * Builds and returns a KML geometry node with the given geometry.
   * 
   * Parameters:
   * geometry - {<OpenLayers.Geometry>}
   * 
   * Returns:
   * {DOMElement}
   */
  private function _buildGeometryNode($geometry) {
//      Util::log($geometry, "_buildGeometryNode arg");
    //TODO
//      if (!empty($this->internalProjection) && !empty($this->externalProjection)) 
//      {
//          $geometry = $geometry.clone(); 
//          geometry.transform(this.internalProjection, 
//                             this.externalProjection);
//      }
      
      
      $type= get_class($geometry);
      
//      Util::log($type, "type");
      
      $builder = $this->_buildGeometry(strtolower($type), $geometry);
      
//      Util::log($builder, "builder");
      
      return $node = $builder;

  }
  
  /**
   * Property: buildGeometry
   * Object containing methods to do the actual geometry node building
   *     based on geometry type.
   */
  
  private function _buildGeometry($type, $geometry) 
  { 
    
//    Util::log(func_get_args(), "_buildGeometry args");
    
    if(empty($geometry) || empty($type)) return false;
    
    switch(strtolower($type)):
    
      case "point":        
        $kml = $this->doc->createElementNS($this->kmlns, "Point");
        $kml->appendChild($this->_buildCoordinatesNode($geometry));
        return $kml;        
        break;
      
      case "multipoint":
        return $this->_buildGeometry("collection", $geometry);
        break;
        
      case "linestring":
        $kml = $this->doc->createElementNS($this->kmlns, "LineString");
        $kml->appendChild($this->_buildCoordinatesNode($geometry));
        return $kml;
        break;
        
      case "multilinestring":
        return $this->buildGeometry("collection", $geometry);
        break;
        
      case "linearring":
        $kml = $this->doc->createElementNS($this->kmlns, "LinearRing");
        $kml->appendChild($this->_buildCoordinatesNode($geometry));
        return $kml;
        break;
        
      case "polygon":
        $kml = $this->doc->createElementNS($this->kmlns, "Polygon");
        $rings = $geometry->components;       
        
        foreach($rings as $ring):
          static $i = 0;
          $type = $i == 0 ? "outerBoundaryIs" : "innerBoundaryIs";
          $i++;
          $ringMember = $this->doc->createElementNS($this->kmlns, $type);
          $ringGeom = $this->_buildGeometry("linearring", $ring);
          $ringMember->appendChild($ringGeom);
          $kml->appendChild($ringMember);
        endforeach;
        
        return $kml;
        break;

      case "multipolygon":
        return $this->_buildGeometry("collection", $geometry);        
        break;

      case "collection":
        $kml = $this->doc->createElementNS($this->kmlns, "MultiGeometry");

        foreach($geometry->components as $component):
          $child = $this->_buildGeometryNode($component);
          if(!empty($child)) $kml->appendChild($child);
        endforeach;

        return $kml;
        break;
        
    endswitch;
    
  }
  
  /**
   * Method: buildCoordinatesNode
   * Builds and returns the KML coordinates node with the given geometry
   * <coordinates>...</coordinates>
   * 
   * Parameters:
   * geometry - {<OpenLayers.Geometry>}
   * 
   * Return:
   * {DOMElement}
   */  
     
  private function _buildCoordinatesNode($geometry) 
  {
      $coordinatesNode = $this->doc->createElementNS($this->kmlns, "coordinates");

      $points = $geometry->components;
      
      if(!empty($points)) 
      {
          // LineString or LinearRing          
//          $numPoints = count($points);          
//          var parts = new Array(numPoints);
          $parts = array();
          
          foreach($points as $point):
            $parts[] = $point->x . "," . $point->y; 
          endforeach;
          
          //$path = parts.join(" ");
      } 
      else 
      {
          // Point
          $path = $geometry->x . "," . $geometry->y;
      }
      
      $txtNode = $this->doc->createTextNode($path);
      $coordinatesNode->appendChild($txtNode);
      
      return $coordinatesNode;
  }
}
 
