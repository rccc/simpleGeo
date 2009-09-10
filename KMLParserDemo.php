<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title>simpleGeo KML Parser Example</title>
    <style type="text/css">
     body{font-family: sans serif;}
     h3{margin: 0 0 0 10px;}
     ul{margin: 15px 0 15px 20px;}
    </style>
  </head>
  <body>

      <h1 id="title">KML Parser Example</h1>

      <div id="tags"></div>

      <p id="shortdesc">
          Demonstrate the operation of the KML parser with this KML file : 
          <a href="http://code.google.com/apis/kml/documentation/KML_Samples.kml">code.google.com/apis/kml/documentation/KML_Samples.kml</a> 
      </p>
      <div id="output">
      <?php $url = '<script type="text/javascript">document.write(document.getElementById(\'file_url\'))</script>'; ?>
      <?php
        
        require_once('lib/Format/KML.class.php'); 

        $options = array("extractStyles"=>true);

        $test = new KML($options);

        /** read **/ 
        $features = $test->read("http://code.google.com/apis/kml/documentation/KML_Samples.kml");
        
        $html = "";
        
        foreach($features as $feature):
        
          
            $html .= "<h2>Geometry : <b>" . get_class($feature->geometry) ."</b></h2>";
            
            if(count($feature->attributes) > 0):
            
                $html .= "<h3>attributes : </h3>";
                $html .= "<ul>";
                
                foreach($feature->attributes as $key => $value)
                {
                 
                  $html .= "<li>" . $key.  " : " . $value . "</li>";          
                }    
                
                $html .= "</ul>";
                
            endif;       
          
  
            if(count($feature->style) > 0):
            
                $html .= "<h3>style : </h3>";
                
                foreach($feature->style as $style)
                {
                    $html .= "<ul>"; 
                                  
                    foreach($style as $key => $value)
                    {
                      $html .= "<li>" . $key.  " : " . $value . "</li>";
                    }
                    
                    $html .= "</ul>";          
                }    
                
            endif;
                   
        endforeach;
        
        echo $html;
      ?>
      </div>

    
      <h2>PHP object structure :</h2>
      <?php var_dump($features); ?> 

  </body>
</html>
