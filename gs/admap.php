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
    $map_row = $database->get('wms', "id = ".$id." AND owner = ".$user->id);
    if(!$map_row) die('Sorry, map not found!');
    
    //var full_url = "https://" +URL+ "/collections/" +Workspace+ ".json"
                //var full_url = "https://www.thinkamap.net:9001/collections/public.wards.json"
     echo $full_url = "https://" . $map_row['url'] . "/collections/" . $map_row['collection'] . ".json";

    //$full_url = base64_encode("https://" . $map_row['url'] . "/geoserver/" . $map_row['collection'] . "/ows?service=WFS&version=1.0.0&request=GetFeature&typeName=" . $map_row['collection'] . ":" . $map_row[cql] . "&maxFeatures=3000&outputFormat=application/json");
?>
