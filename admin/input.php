<?php
	#define('DB_HOST', 'geoexhibit.com');
	#define('DB_NAME', 'exhibit1836_jripub');
	#define('DB_USER', 'exhibit1836');
	#define('DB_PASS', 'Tristan1902');

	#$pg_err = '';
	#$dbconn = pg_connect('host='.DB_HOST.' dbname='.DB_NAME.' user='.DB_USER.' password='.DB_PASS);
	#if (!$dbconn) {
	#	$pg_err = pg_last_error($dbconn);
	#}
	require_once('class/database.php');
	require_once('class/report.php');
	require_once('class/input.php');

	$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
	$dbconn = $database->getConn();

	$pg_err = '';
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$inp_obj = new input_Class($dbconn);

		$data = array('name' 			=> $_POST['name'],
									'report_id' => $_POST['report_id'],
									'input' 		=> $_POST['editor']);
		$inp_id = $inp_obj->create($data);
		if($inp_id == 0) {
			$pg_err = pg_last_error($dbconn);
			die($pg_err);
		}
	}

	$db_obj = new Report_Class($dbconn);
	$reportRows = $db_obj->getRows($dbconn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include("incl/meta.php"); ?>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	<script src="inputjs/jquery.min.js"></script>
	<script src="https://cdn.ckeditor.com/4.11.2/standard/ckeditor.js"></script>
</head>
<body>
    <!-- ============================================================== -->
    <!-- Preloader - style you can find in spinners.css -->
    <!-- ============================================================== -->

    <!-- ============================================================== -->
    <!-- Main wrapper - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <div id="main-wrapper" data-layout="vertical" data-navbarbg="skin5" data-sidebartype="full"
        data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">

        <?php define('MENU_SEL', 'input.php');
					include("incl/topbar.php");
					include("incl/sidebar.php");
				?>
        <!-- ============================================================== -->
        <!-- Page wrapper  -->
        <!-- ============================================================== -->
        <div class="page-wrapper">
            <!-- ============================================================== -->
            <!-- Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <div class="page-breadcrumb" style="padding-left:30px; padding-right: 30px; padding-top:0px; padding-bottom: 0px">
                <div class="row align-items-center">
                    <div class="col-6">
                        <nav aria-label="breadcrumb">

                          </nav>
                        <h1 class="mb-0 fw-bold">Context Panel</h1>
                    </div>



                </div>
            </div>
            <!-- ============================================================== -->
            <!-- End Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- Container fluid  -->
            <!-- ============================================================== -->
            <div class="container-fluid">



						<?php if(strlen($pg_err) > 0){?>
							<div><?=$pg_err?></div>
						<?php } ?>



							<form method="post" action="input.php" style="max-width: 750px;">
							<div class="form-group">
								<label for="name" style="color: #777e89; font-weight: 500;">Context Name </label>
								<input type="text" name="name" id="name" style="border: 1px solid #ccc; border-radius: 4px;"/>
							 </div>
							<div class="form-group">
								<label for="report_id" style="color: #777e89; font-weight: 500;">Select Report:  </label>
								<select name="report_id" id="report_id" style="border: 1px solid #ccc; border-radius: 4px;">
								    <?php while($row = pg_fetch_object($reportRows)) { ?>
								    <option value="<?=$row->id?>"><?=$row->name?></option>
								    <?php } ?>
								</select>
 							</div>
                                                        <div class="form-group">

								<textarea name="editor" id="editor" rows="10" cols="80">
									This is my textarea to be replaced with HTML editor.
								</textarea>
                                                                <p>&nbsp;</p>

                                                                 <input type="submit" name="submit" class="btn btn-primary" value="Submit">
							</form>
				</div>






<script>
	$(window).scroll(function() {
		if ( $(document).scrollTop() > $(".hdr_top").outerHeight() ) {
			$('.header_outer').addClass('shrink');
		} else {
			$('.header_outer').removeClass('shrink');
		}
	});
</script>

<script>
	CKEDITOR.replace( 'editor' );
</script>

</body>
</html>
