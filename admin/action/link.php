<?php
    session_start();

    require_once('../class/database.php');
    require_once('../class/link.php');
		$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
    $obj = new link_Class($database->getConn());

    $result = ['success' => false, 'message' => 'Error while processing your request!'];
    $users = $obj->getRows();


    if(isset($_SESSION['user']) && $_SESSION['user']->accesslevel == 'Admin') {
        if(isset($_POST['save'])) {
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            $newId = 0;

            if($id) { // update
                $obj->update($_POST);
            }
            else { // insert
                $newId = $obj->create($_POST);
            }

            $result = ['success' => true, 'message' => 'Data Successfully Saved!', 'id' => $newId];
        }

        else if(isset($_POST['delete'])) {
            $ret_val = $obj->delete(intval($_POST['id']));
            $result = ['success' => true, 'message' => 'Data Successfully Deleted!'];
        }
    }

    echo json_encode($result);
?>
