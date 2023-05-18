<?PHP
    require_once 'functions.php';
    session_start();
    
    if(!isset($_SESSION['user'])) {
        header('Location: ../login.php');
        exit;
    }
    
    $id = intval($_GET['id']);
    
	$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
	$row = $database->get('pguser', 'id = '.intval($id) .' AND owner = '.$_SESSION['user']->id);
    
    if($row) {
        die(file_get_contents($row['pgtileurl']));
    }
    
    die('Error');
?>