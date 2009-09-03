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
  
  private $kmlns = "http://earth.google.com/kml/2.0";
  
  /** 
  * placemarksDesc
  * Name of the placemarks.  Default is "No description available."
  * @var String 
  */
  
  private $placemarksDesc =  "No description available";
  
  /** 
    * foldersName
    * Name of the folders.  Default is "OpenLayers export."
    * @var String 
    */
  
  private $foldersName = "OpenLayers export";
  
  /** 
    * foldersDesc
    * Description of the folders. Default is "Exported on [date]."
    * @var String 
    */
  
  private $foldersDesc = null; //"Exported on " . date('l jS \of F Y h:i:s A');
  
  /**
    * extractAttributes
    * Extract attributes from KML.  Default is true.
    * Extracting styleUrls requires this to be set to true
    * @var Boolean 
    *           
    */
  
  private $extractAttributes = true;
  
  /**
    * extractStyles
    * Extract styles from KML.  Default is false.
    * Extracting styleUrls also requires extractAttributes to be set to true
    * @var Boolean 
    *           
    */
  
  private $extractStyles = false;
  
  /**
    * internalns
    * KML Namespace to use -- defaults to the namespace of the
    * Placemark node being parsed, but falls back to kmlns.
    * @var String  
    */
  
  private $internalns = null;
  
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
  
  private $styleBaseUrl =  "";
  
  /**
    * fetched
    * Storage of KML URLs that have been fetched before
    * in order to prevent reloading them.
    * @var Object 
    */
  
  private $fetched = array();
  
  /**
    * maxDepth
    * Maximum depth for recursive loading external KML URLs 
    * Defaults to 0: do no external fetching !!
    * @var Integer 
    */
  
  private $maxDepth = 1;
  
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
    
    $this->doc = new DOMDocument();
    
    $ext = substr(strtolower(strrchr(basename($uri), ".")), 1);  
    
    
    if(!empty($ext) && $ext == "kml")
        $this->doc->load($uri);
    else
        $this->doc->loadXML($uri);

    $this->options['depth'] = 0;
    
    echo $this->doc->saveXML();
    
    return $this->_parseData();
  }
  
  /**
   * Read data from a string, and return a list of features. 
   * @return array of Feature instances
   */
  
  private function _parseData()
  {
    $types = array( "NetworkLink","Link", /*"Style", "StyleMap",*/ "Placemark");    
    
    foreach($types as $type):   
          
          $nodes = $this->doc->getElementsByTagName($type); //getElementsByTagNameNS ?
          
          if($nodes->length == 0 ) continue; // skip to next iteration
             Util::log($nodes->item(0)->nodeName, 'TYPE');   
          switch(strtolower($type)):  
            // Fetch external links 
             case "link":
             case "networklink":          
               $this->_parseLinks($nodes, $options);
               break;
//                 
//             // parse style information
//             case "style":
//               if(isset($extractStyles)) 
//               {
//                 $this->_parseStyles($nodes, $options);
//               }
//               break;
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
          
          return $this->features;
          
    endforeach; 
    
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
    
    Util::log($this->options['depth'], '$this->options[depth]');
    Util::log($this->options['depth'] > $this->maxDepth, "$this->options['depth'] > $this->maxDepth");
    
    if($this->options['depth'] > $this->maxDepth)
        return false;  
   
    //increase depth
    $this->options['depth']++;    
        
    foreach($nodes as $node):
        Util::log($node->nodeName, 'nodeName');
        
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
   * Convenience method to find a node and return its value
   * @param $xmlNode DOMElement
   * @param $namespace string
   * @param $tagName string
   * @return string
   */
  
  private function _parseProperty($xmlNode, $namespace, $tagName)
  { 
     Util::log($xmlNode->nodeName, "xmlNode in parseProperty");
     
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
      
      foreach($nodes as $node)
      { 
        static $i = 0;
        $features[$i] = $this->_parseFeature($node);
        $i++;

        //TODO implements style extraction
        
        if(!$features.length > 0 ) throw new Exception('Bad Placemark');
        
      }      
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
    
    $feature = new Feature($geometry, $attributes);

    // try to set 'fid' property
    if($node->getAttribute('id'))
      $fid = $node->getAttribute('id');
    elseif($node->getAttribute('name'))
      $fid = $node->getAttribute('name');

    if(!empty($fid)) $feature->fid = $fid;   
    
    return $feature;
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
            $ed['displayName'] = $nameNode->nodeValue;
        
        $attributes[$key] = $ed;
        
    endforeach;
    
    return $attributes;
    
  }
    
}
 
