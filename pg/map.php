<?PHP
    error_reporting(E_ALL);

    require_once 'functions.php';
    
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    $database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
    $map_row = $database->get('pguser', $id);
    if(!$map_row) die('Sorry, map not found!');
    
    
    
    
    /* Second DB Connection */
    $db = new Database($map_row['host'], $map_row['database'], $map_row['username'], $map_row['password'], 5432, $map_row['schema']);
    
    $geom_row = $db->getGEOM($map_row['tbl'], $map_row['geom']); //$ignore
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Hugo 0.84.0">
    <title><?=$map_row['name']?></title>
    
    <link href="assets/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.0.2/leaflet.css">
		<style type="text/css">
		.legend {
			color: #555;
			padding: 6px 8px;
			font: 12px Arial, Helvetica, sans-serif;
			font-weight: bold;
			background: white;
			background: rgba(255,255,255,0.8);
			box-shadow: 0 0 15px rgba(0,0,0,0.2);
			border-radius: 5px;
			max-width: 500px;
		}
		.legend .labels {
		    display: flex;
		    justify-content: space-between;
		}
		.legend ul {
			list-style-type: none;
			padding: 0;
			margin: 0;
			clear: both;
			display: flex;
		} 
		.legend li {
			display: inline-block;
			width: 800px;
            height: 7px;
		}
		.legend .quarter1 {
			/*float: left;*/
			padding-bottom: 5px;
		}
		.legend .quarter2 {
			/*float: left;*/
			padding-bottom: 5px;
		}
		.legend .quarter3 {
			/*float: left;*/
			padding-bottom: 5px;
		}
		.legend .quarter4 {
			/*float: right;*/
		}
		</style>
		
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.0.2/leaflet.js"></script>
		<script src="leaflet.browser.print.min.js"></script>
		<script src="../choropleth.js"></script>
    
    
    <style type="text/css">
      html, body, #map {
        margin: 0px;
        height: 100%;
        width: 100%;
      }
      
      .sidebar {
        max-width: 300px;
        background: white;
        max-height: 400px;
        overflow-x: hidden;
        overflow-y: auto;
        display: none;
      }
      .sidebar .close {
          position: absolute;
          right: 0;
      }

#loading {
  position: fixed;
  display: block;
  width: 100%;
  height: 100%;
  top: 0;
  left: 0;
  text-align: center;
  opacity: 0.7;
  background-color: #fff;
  z-index: 99;
}

#loading-image {
  position: absolute;
  top: 300px;
  left: 45%;
  z-index: 100;
}
    </style>
    
  </head>
  <body>
    
<header>
  
  <div class="navbar navbar-dark bg-dark shadow-sm" style="background-color:var(--neutral-secondary-color,#666)!important">
    <div class="container">
      <a href="#" class="navbar-brand d-flex align-items-center">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-text" viewBox="0 0 16 16">
  <path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1h-5zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5z"/>
  <path d="M9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5L9.5 0zm0 1v2A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/>
</svg>        <strong> &nbsp;JRI Report Viewer</strong>
      </a>				 <a href="../" style="text-decoration:none; color: #fff!important; font-size: 1.25rem; font-weight: 300;">Return to Dashboard</a>

    </div>
  </div>
</header>

<div id="loading">
  <img id="loading-image" src="ajax-loader.gif" alt="Loading..." />
</div>

    <div id="map" style="height: 100%; width: 100%;"></div>
    
    
        <style>
            .leaflet-pdf-control {
                background-size: 20px 20px !important;
                display: block;
                border-radius: 2px;
                background: #fff url('data:image/png;base64,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') no-repeat 5px
            }
        </style>
    	<script>
            const queryString = window.location.search;
            const urlParams = new URLSearchParams(queryString);
            
            var Lat = <?=($map_row['lat'] ? $map_row['lat'] : 0)?>;
            var Lon = <?=($map_row['lon'] ? $map_row['lon'] : 0)?>;
            var Zoom = <?=($map_row['zoom'] ? $map_row['zoom'] : 0)?>;
    	
    	
    	    $(document).ready(function() {
                var map = L.map('map').setView([Lat, Lon], Zoom);
                
                
                L.control.browserPrint({
                	closePopupsOnPrint: false,
                	manualMode: false
                }).addTo(map);
                
                
                
                
                // Define a custom control class that extends L.Control
                var CustomControlDownload = L.Control.extend({
                  options: {
                    position: 'topleft' // Set the position of the control to top left
                  },
                
                  onAdd: function(map) {
                    // Create a container element for the control
                    var container = L.DomUtil.create('div', 'leaflet-bar leaflet-control');
                
                    // Create a button element for the control
                    var button = L.DomUtil.create('a', 'leaflet-pdf-control', container);
                    button.href="download.php?id=<?=$id?>"
                    //button.innerHTML = 'Custom Control';
                
                    // Add a click event listener to the button
                    L.DomEvent.addListener(button, 'click', function() {
                      //alert('Custom control button clicked!');
                    });
                
                    // Return the container element
                    return container;
                  }
                });
                
                // Add the custom control to the map
                map.addControl(new CustomControlDownload());
                
                
                
                
                
                
                
                
                
                var customControl = L.Control.extend({
                  options: {
                    position: 'topleft' // set the position of the control
                  },
                
                  onAdd: function (map) {
                    // Create a container div for the control
                    var container = L.DomUtil.create('div', 'leaflet-bar leaflet-control leaflet-control-custom');
                
                    // Add content to the container
                    container.innerHTML = `
                        <div class="sidebar">
                            <a href="#" class="btn btn-sm mt-1 mx-3 close" id="fg-close-it" onclick="$(this).closest('.sidebar').hide()">X</a>
                            <div class="table-container px-3 py-4"></div>
                        </div>
                    `;
                    // Add an event listener to the container
                    /*container.querySelector('#fg-close-it').addEventListener('click', function () {
                      
                    });*/
                    // Return the container
                    return container;
                  }
                });
                map.addControl(new customControl());
                
                // Add basemap
                L.tileLayer('<?=$map_row['basemap']?>', {
                  maxZoom: 18,
                  attribution: ''
                }).addTo(map)
                
                
                var featurecollection = <?=$geom_row['featurecollection']?>;
                
                var choroplethLayer = L.choropleth(featurecollection, {
                    <?PHP if($map_row['metric']) { ?>
                    valueProperty: '<?=$map_row['metric']?>',
                    <?PHP } ?>
                    scale: ['#fbfad4', 'green'],
                    steps: 5,
                    mode: 'q',
                    style: {
                      color: '#fff',
                      weight: 2,
                      fillOpacity: 0.8
                    },
                    onEachFeature: function (feature, layer) {
                        
                        layer.on('click', function(event) {
                            var properties = feature.properties;
                            
                            var html = '<table style="text-align: left; font-size: 100%;">';
                            for (const key in properties) {
                                html += '<tr><th>'+ key +"</th> <td>"+ properties[key] + "</td></tr>";
                            }
                            html += '</table>';
                            
                            $('.sidebar').show();
                            $('.sidebar .table-container').html(html);
                        });
                        
                        //layer.bindPopup(html)
                    }
                }).addTo(map)

                            
                
                <?PHP if($map_row['metric']) { ?>
                  // Add legend (don't forget to add the CSS from index.html)
                  var legend = L.control({ position: 'topright' })
                  legend.onAdd = function (map) {
                        var div = L.DomUtil.create('div', 'info legend')
                        var limits = choroplethLayer.options.limits
                        var colors = choroplethLayer.options.colors
                        var labels = [] 
                    
                        // Add min & max
                        div.innerHTML = `
                            <div class="labels">
                                <div class="quarter1">` + limits[0] + `</div>
                                <div class="quarter2">` + Math.round(((limits[limits.length - 1]-limits[0])*.25)+limits[0]) + `</div>
                                <div class="quarter3">` + Math.round(((limits[limits.length - 1]-limits[0])*.75)+limits[0]) + `</div>
                    			<div class="quarter4">` + limits[limits.length - 1] + `</div>
                    		</div>`
                    
                        limits.forEach(function (limit, index) {
                          labels.push('<li style="background-color: ' + colors[index] + '"></li>')
                        })
                    
                        div.innerHTML += '<ul>' + labels.join('') + '</ul> <span><?=ucwords(str_replace("_", " ", $map_row['metric']))?></span>'



                        
                        return div
                   }
                   legend.addTo(map)



// BaseMaps
let cartoLight = L.tileLayer( 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}.png' )
let cartoDark = L.tileLayer( 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}.png' )
let esriSatallite = L.tileLayer( 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}.png')
let openStreetmap = L.tileLayer( 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png' )

// BaseMap List
let basemapControl = {
 
  "Carto Light": cartoLight,
  "Carto Dark": cartoDark,
  "Esri Satellite": esriSatallite,
  "OpenStreetMap": openStreetmap
}

// Add BaseMap Controler
L.control.layers( basemapControl ).addTo( map )
                   
                  
                <?PHP } ?>
            });



        </script>











    
    
    

<footer class="text-muted py-5">
  <div class="container">
    <p class="float-end mb-1">
      <a href="#">Back to top</a>
    </p>
    
    <p class="mb-0"> <a href="sign-in/">Login</a> </p>
  </div>
</footer>
    <script src="assets/dist/js/bootstrap.bundle.min.js"></script>
<script>
  $(window).on('load', function () {
    $('#loading').hide();
  }) 
</script>
  </body>
</html>
