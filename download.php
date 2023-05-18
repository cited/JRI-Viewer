<?PHP
    session_start();
    require_once 'admin/class/database.php';

    $database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $row = $database->get('jasper', $id);
    $paramrow = $database->get('parameters', 'reportid = '.$id);


    function _get($name) {
        return isset($_GET[$name]) ? $_GET[$name] : '';
    }

    function myFilter($var){
        return ($var !== NULL && $var !== FALSE && $var !== "");
    }

    if($paramrow && $paramrow['ptype'] == 'dropdown') {
        $params = [];
        if($paramrow) {
            $params = explode(',', $paramrow['pvalues']);

            if( !in_array(_get($paramrow['pname']),  $params) ) {
                $_GET[$paramrow['pname']] = $params[0];
            }

        }
    }
    else if($paramrow && $paramrow['ptype'] == 'query') {
        $DB_pvalues = explode(',', $paramrow['pvalues']);
        $pParameters = [];

        foreach($DB_pvalues as $v) {
            if($v) {
                $pParameters[$v] =  isset($_GET[$v]) ? $_GET[$v] : '';
            }
        }
    }

    if(!isset($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }


    if(!$row || !in_array(_get('type'), ['csv', 'html', 'html2', 'docx', 'jxl', 'pdf', 'pptx', 'rtf', 'xlsx'])) die('Invalid Request');

    if(!$row) {
        header('Location: index.php');
    }
    else {
        header("Content-disposition: attachment; filename=\"".$row['outname']."-".date('Y-m-d-H:i:s')."."._get('type')."\"");

        $query = array_filter($_GET, "myFilter");
        unset($query['id']);
        $query = http_build_query($query);

        $url = str_replace(['http://', 'https://'], '', $row['url']);
        echo @file_get_contents('https://'.$url.'/JasperReportsIntegration/report?_repName='.$row['repname'].'&_repFormat='._get('type').'&_dataSource='.$row['datasource'].'&'.$query);
    }
