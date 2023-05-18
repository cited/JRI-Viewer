<?PHP
    error_reporting(E_ALL);
    session_start();
    require_once 'pg-functions.php';
    
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $user = $_SESSION['user'];
    if(!$user) {
        header('Location: ../login.php');
        exit;
    }
    
    $database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
    $map_row = $database->get('wms', "id = ".$id);
    if(!$map_row) die('Sorry, map not found!');
    $reprow = $database->get('inputs', "report_id = ".$id);

    
    $full_url = "https://" . $map_row['url'] . "/collections/" . $map_row['collection'] . "/items.json?limit=". $map_row['cql'];   

?>



<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Hugo 0.84.0">
    <title>Album example 路 Bootstrap v5.0</title>

    
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
		<script src="choropleth.js"></script>
    
    
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

.leaflet-control-container { position: topleft } 


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
      </a>		 <a href="../" style="text-decoration:none; color: #fff!important; font-size: 1.25rem; font-weight: 300;">Return to Dashboard</a>
    </div>
  </div>
</header>
<div id="loading">
  <img id="loading-image" src="ajax-loader.gif" alt="Loading..." />
</div>

    <div id="map" style="height: 100%; width: 100%;"></div>\
    
    <script>
function humanize(str) {
  var i, frags = str.split('_');
  for (i=0; i<frags.length; i++) {
    frags[i] = frags[i].charAt(0).toUpperCase() + frags[i].slice(1);
  }
  return frags.join(' ');
}
</script>
    
    	<script>
    	    $(document).ready(function() {
                var map = L.map('map').setView([<?=$map_row['lat']?>, <?=$map_row['lon']?>], <?=$map_row['zoom']?>);
                
                L.control.browserPrint({
                	closePopupsOnPrint: false,
                	manualMode: false
                }).addTo(map);


               
                
                
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
                L.tileLayer('<?=$map_row['basemap']?>',{
                  maxZoom: 18,
                  attribution: ''
                }).addTo(map);


                
                //var url = atob('<?=$full_url?>');
                // Add GeoJSON
                $.getJSON('<?=$full_url?>', function (geojson) {
                  var choroplethLayer = L.choropleth(geojson, {
                    valueProperty: '<?=$map_row['metric']?>',
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
                
                    div.innerHTML += '<ul>' + labels.join('') + '</ul> <span>'+humanize('<?=$map_row['metric']?>')+'</span>'
                    
                    return div 
                  }
                  legend.addTo(map);
                
                
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
                    
                    //L.control.zoom({
                    //    position: 'bottomright'
                    //}).addTo(map);
                    
                     L.control.layers( basemapControl ).addTo( map )
                  });


                
                
                /*map.on('load', function() {
                    alert('loaded');
                    var html = `
                        <div class="sidebar">
                            <a href="#" class="btn btn-sm pull-right mt-3 mx-3 close" onclick="$('.sidebar').removeClass('slide-in')">X</a>
                            <div class="clearfix"></div>
                            <div class="table-container px-3 py-3"></div>
                        </div>
                    `;
                    $('.leaflet-control-container .leaflet-top.leaflet-right').append(html);
                });*/




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
