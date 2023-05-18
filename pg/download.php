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
    
    
    
    
    
    
    $output_file = time().'-'.rand().'.pdf';
    $vrtFile = '/home/exhibit1836/vrt/'.$id.'.vrt';
    $txtFile = '/home/exhibit1836/vrt/'.$id.'.txt';
    $command = 'ogr2ogr -f "PDF" '.$output_file.' ';
    
    $command .= file_exists($txtFile) ? str_replace("\n", " ", trim(file_get_contents($txtFile) ) ). ' ' : '-dsco OGR_DISPLAY_FIELD='.$map_row['tbl'].' ';
    $command .= file_exists($vrtFile) ? $vrtFile.' ' : 'PG:"host='.$map_row['host'].' user='.$map_row['username'].' dbname='.$map_row['database'].' password='.$map_row['password'].'" "'.$map_row['tbl'].'" ';
    $command .= '2>&1';
    
    exec($command);
    
    // exec('ogr2ogr -f "PDF" '.$output_file.' '.$content.' PG:"host='.$map_row['host'].' user='.$map_row['username'].' dbname='.$map_row['database'].' password='.$map_row['password'].'" "'.$map_row['tbl'].'" '. $vrtFile.' 2>&1',$output);
    // exec('ogr2ogr -f "PDF" '.$output_file.' '.$content.'  '. $vrtFile.' 2>&1', $result);
    //print_r($result);die('done');
    
    header("Content-Type: application/pdf");
    header('Content-Disposition: attachment; filename="'.$map_row['name'].'-'.date('Y-m-d_H_i_s').'.pdf"');
    
    echo readFile($output_file);
    @unlink($output_file);
?>



