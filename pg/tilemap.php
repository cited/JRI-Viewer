<?PHP
    ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

    require_once 'functions.php';
    session_start();
    
    
    if(!isset($_SESSION['user'])) {
        header('Location: ../login.php');
        exit;
    }
    $user = $_SESSION['user'];
    
    $database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    $rpt = $database->get('pguser', 'id = '.$id.' AND owner = '.$user->id);
    if(!$rpt) die('Report with ID: '.$id. ' is not found');
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Hugo 0.84.0">
    <title><?=$rpt['name']?></title>

    <script src="https://unpkg.com/maplibre-gl@2.1.9/dist/maplibre-gl.js"></script>
    <link href="https://unpkg.com/maplibre-gl@2.1.9/dist/maplibre-gl.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <link href='https://watergis.github.io/mapbox-gl-export/mapbox-gl-export.css' rel='stylesheet' />
    <script src='https://api.mapbox.com/mapbox-gl-js/v1.13.1/mapbox-gl.js'></script>
    <script src="https://watergis.github.io/mapbox-gl-export/mapbox-gl-export.js"></script>

    <!-- Bootstrap core CSS -->
    <link href="https://geoexhibit.com/assets/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    
    <!--<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.0.2/js/bootstrap.min.js"></script>

    <style type="text/css">
      .bd-placeholder-img {
        font-size: 1.125rem;
        text-anchor: middle;
        -webkit-user-select: none;
        -moz-user-select: none;
        user-select: none;
      }

      @media (min-width: 768px) {
        .bd-placeholder-img-lg {
          font-size: 3.5rem;
        }
      }

      nav {
        position: sticky; top: 0!important;
      }
      #meta {
          z-index: 1 !important;
          position: absolute;
      }
    </style>

    <style type="text/css">
      #map {margin-top:55px!important;position:absolute;top:0;bottom:0;left:0;right:0;}
    </style>

  </head>
  <body>
    <nav>
<header style="z-index:0!important">
  
  <div class="navbar navbar-dark bg-dark shadow-sm" style="background-color:var(--neutral-secondary-color,#666)!important">
    <div class="container">
      <a href="#" class="navbar-brand d-flex align-items-center">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-text" viewBox="0 0 16 16">
  <path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1h-5zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5z"/>
  <path d="M9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5L9.5 0zm0 1v2A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/>
</svg>        <strong> &nbsp;JRI Report Viewer</strong>
      </a>

<?php
if($user && $user->accesslevel == 'Admin') { 
  echo '<a href="admin/index.php" style="text-decoration:none; color: #fff!important; font-size: 1.25rem; font-weight: 300;">Administration</a>';
}
?>


      <a href="/" style="text-decoration:none; color: #fff!important; font-size: 1.25rem; font-weight: 300;">Return to Dashboard</a>


    </div>
  </div>  
</header>

</nav>

<main style="padding-top: 25px;">
    <?PHP
        $row = $database->get('pguser', 'id = '.$id);
    ?>  
    
    
    <div id="meta">
    <!--<h1>public.wards.json</h1>-->
    
  </div>
  
  <div id="map"></div>

  <script>

    var map;
    var mapcolor = "blue";

    $.getJSON("tilemap-url.php?id=<?=$row['id']?>", function(layer) {
      
      console.log("public.wards.json");
      console.log(layer);

      var mapConfig = {
        'container': 'map',
        
        
        
        'bounds': layer['bounds'],
        'hash': true,
        'style': {
          'version': 8,
          'sources': {
            'carto-dark': {
              'type': 'raster',
              'tiles': [
                "https://a.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}@2x.png",
                "https://b.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}@2x.png",
                "https://c.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}@2x.png",
                "https://d.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}@2x.png"
              ]
            },
            'carto-light': {
              'type': 'raster',
              'tiles': [
                "https://a.basemaps.cartocdn.com/light_all/{z}/{x}/{y}@2x.png",
                "https://b.basemaps.cartocdn.com/light_all/{z}/{x}/{y}@2x.png",
                "https://c.basemaps.cartocdn.com/light_all/{z}/{x}/{y}@2x.png",
                "https://d.basemaps.cartocdn.com/light_all/{z}/{x}/{y}@2x.png"
              ]
            },
            'wikimedia': {
              'type': 'raster',
              'tiles': [
                "https://maps.wikimedia.org/osm-intl/{z}/{x}/{y}.png"
              ]
            }
          },
          'layers': [{
            'id': 'carto-light-layer',
            'source': 'carto-light',
            
            
            'type': 'raster',
            'minzoom': 0,
            'maxzoom': 22
          }]
        }
      };

      var paints = {
        "circle":{
          "circle-color": mapcolor,
          "circle-radius": 3
        },
        "line":{
          "line-color": mapcolor,
          "line-width": 1.5
        },
        "fill":{
          "fill-color": mapcolor,
          "fill-outline-color": mapcolor,
          "fill-opacity": 0.1
        }
      };

      var painttypes = {
        "Point":"circle",
        "MultiPoint":"circle",
        "LineString":"line",
        "MultiLineString":"line",
        "Polygon":"fill",
        "MultiPolygon":"fill",
      };

      function layerSource(tileurl) {
        return {
          "type": "vector",
          "tiles": [tileurl],
          "minzoom": layer["minzoom"],
          "maxzoom": layer["maxzoom"]
        }
      };

      function layerId(id, gtype, paint) {
        return id+"."+gtype+"."+paint;
      }

      function layerConfig(id, gtype, paint) {
        return {
          "id": layerId(id, gtype, paint),
          "source": id,
          "source-layer": id,
          "type": paint,
          "paint": paints[paint],
          "filter": ["match", ["geometry-type"], [gtype, "Multi"+gtype], true, false]
        }
      };

      
      function featureHtml(f) {
        var p = f.properties;
        var h = "<p>";
        for (var k in p) {
          h += "<b>" + k + ":</b> " + p[k] + "<br/>"
        }
        h += "</p>";
        return h
      }
        
      function addHomeButton(map) {
          class HomeButton {
            onAdd(map) {
              const div = document.createElement("div");
              div.className = "mapboxgl-ctrl mapboxgl-ctrl-group";
              div.innerHTML = `<button>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"> <title>Download</title> <path d="M21,13h-6V3H9v10H3l9,9L21,13z"/> </svg>
                </button>`;
              div.addEventListener("contextmenu", (e) => e.preventDefault());
              div.addEventListener("click", () => location.href="tilemap-download.php?id=<?=$id?>" );
        
              return div;
            }
          }
          const homeButton = new HomeButton();
          map.addControl(homeButton, "top-right");
      }

      function addLayerBehavior(id) {
        
        
        map.on('click', id, function(e) {
          new maplibregl.Popup()
          .setLngLat(e.lngLat)
          .setHTML(featureHtml(e.features[0]))
          .addTo(map);
        });

        
        map.on('mouseenter', id, function() {
          map.getCanvas().style.cursor = 'pointer';
        });

        
        map.on('mouseleave', id, function() {
          map.getCanvas().style.cursor = '';
        });
      }

      function addOneLayer(id, gtypebasic) {
        map.addLayer(layerConfig(id, gtypebasic, painttypes[gtypebasic]));
        addLayerBehavior(layerId(id, gtypebasic, painttypes[gtypebasic]));
        if (gtypebasic == "Polygon") {
          map.addLayer(layerConfig(id, gtypebasic, "line"));
        }
      }

      function addLayers(id, gtype, url) {
        map.addSource(id, layerSource(url));
        var gtypebasic = gtype.replace("Multi", "");
        var gtypes = ["Point", "LineString", "Polygon"];
        
        if (gtypes.includes(gtypebasic)) {
          addOneLayer(id, gtypebasic);
        }
        
        else {
          gtypes.forEach(gt => {
            addOneLayer(id, gt);
          });
        }

      }

       


      
      map = new maplibregl.Map(mapConfig);
      map.addControl(new maplibregl.NavigationControl());
      map.addControl(new MapboxExportControl({
          PageSize: Size.A3,
          PageOrientation: PageOrientation.Portrait,
          Format: Format.PNG,
          DPI: DPI[96],
          Crosshair: true,
          PrintableArea: true,
      }), 'top-right');
      addHomeButton(map);
      
      map.on("load", function() {
        queryParam = new URLSearchParams(window.location.search).toString();
        
        tileUrl = layer["tileurl"] + "?" + queryParam;
        addLayers(layer["id"], layer["geometrytype"], tileUrl);
      });

    });

  </script>  

<script>
</script>                      
                 
            
    </tr>
</table>



</main>

<footer class="text-muted py-5">
  <div class="container">
    <p class="float-end mb-1">
<a href="#" style="text-decoration:none; color: #6c757d!important; font-size: 1.25rem; font-weight: 300;">Back to top</a>    </p>
  </div>
</footer>
    <script src="https://geoexhibit.com/assets/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        $(function () {
          $('[data-bs-toggle="popover"]').popover()
        });
    </script>
      
  </body>
</html>