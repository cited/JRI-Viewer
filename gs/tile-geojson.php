<?PHP
    session_start();
    
    require_once 'pg-functions.php';
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    $user = $_SESSION['user'];
    if(!$user) {
        header('Location: ../login.php');
        exit;
    }
    
    $database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
    $row = $database->get('wms', "id = ".$id." AND owner = ".$user->id);
    if(!$row) die('Sorry, map not found!');
    
    $full_url = "https://" . $map_row['url'] . "/collections/" . $map_row['collection'] . "/items.json?limit=100". $map_row[cql];   echo file_get_contents($full_url);
?>