<?php

error_reporting(E_ALL);
ini_set('display_errors',1);

define('FIELDS', array('VrtFileName', 'TableName', 'PenWidth', 'LineWidth'));
define('COLORS', array('pen', 'brush', 'line'));

$form_error = '';

# process form submit
if(isset($_POST['generate'])){

	$req_keys = array_merge(FIELDS, COLORS);
	foreach($req_keys as $k){
		if(!isset($_POST[$k]) || strlen($_POST[$k]) == 0){
			$form_error = ucfirst($k).' is not set!';
			break;
		}
	}
}?>
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
	<?php include("incl/meta.php"); ?>
	<link href="dist/css/table.css" rel="stylesheet">
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

        <?php define('MENU_SEL', 'vrt.php');
					include("incl/topbar.php");
					include("incl/sidebar.php");
				?>
        <!-- ============================================================== -->
        <!-- Page wrapper  -->
        <!-- ============================================================== -->
        <div class="page-wrapper" style="padding-bottom: 0px!important; ">
            <!-- ============================================================== -->
            <!-- Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <div class="page-breadcrumb" style="padding-left:30px; padding-right: 30px; padding-top:0px; padding-bottom: 0px">
                <div class="row align-items-center">
                    <div class="col-6">

                        <h1 class="mb-0 fw-bold">Vrt Generator</h1>
                    </div>
                    <div class="col-6">
                        <!--<a href="postgis.php" class="btn btn-success"><span class="mdi mdi-arrow-left"></span>&nbsp;Return to PostGIS</a>-->

                    </div>
                </div>
            </div>
            <!-- ============================================================== -->
            <!-- End Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- Container fluid  -->
            <!-- ============================================================== -->
            <div class="container-fluid" style="padding-right: 50%; padding-bottom:0%!important; ">





<?php
	if(isset($_POST['generate']) && (strlen($form_error) == 0)){
			$data = "<OGRVRTDataSource>
	<OGRVRTLayer name=\"".$_POST['TableName']."\">
		<SrcDataSource>PG:host=dbhost user=dbuser dbname=dbname password=dbpassword</SrcDataSource>
		<SrcSQL>SELECT *,'BRUSH(fc:".$_POST['brush'].");PEN(c:".$_POST['pen'].",w:".$_POST['PenWidth']."px);LINE(c:".$_POST['line'].",w:".$_POST['LineWidth'].")' AS OGR_STYLE FROM ".$_POST['TableName']."</SrcSQL>
	</OGRVRTLayer>
</OGRVRTDataSource>";

		$data = htmlspecialchars($data);
		#$data = nl2br($data);
	?>
	<pre><code class="language-xml"><div id="output"><?=$data?></div></code></pre>


<button class="btn" onclick="copyContent()"><i class="fas fa-clipboard" style="color: #949ead;"></i>&nbsp;Copy to Clipboard</button>

<script>
  let text = document.getElementById('output').innerHTML.replace( /&lt;/g, "<" ).replace( /&gt;/g, ">" ).replace( /&quot;/g, "\"" );
  const copyContent = async () => {
    try {
      await navigator.clipboard.writeText(text);
      console.log('Content copied to clipboard');
    } catch (err) {
      console.error('Failed to copy: ', err);
    }
  }
</script>

<p>&nbsp;</p><p>&nbsp;</p>
<h1 class="display-4 fs-1">
		<a href="vrt.php" class="btn btn-success"><span class="mdi mdi-arrow-left"></span>&nbsp;Return to Generator</a><br><br>
		<a href="postgis.php" class="btn btn-success"><span class="mdi mdi-arrow-left-bold"></span><span class="mdi mdi-arrow-left-bold"></span>&nbsp;Return to PostGIS</a>


	</h1>




<?php	}else{ ?>

	<!--<div class="container">-->
	<form class="border shadow p-3 rounded" action="vrt.php" method="post">
	<h1 class="text-center p-3">Vrt File Generator</h1>

	<?php if (strlen($form_error) > 0) { ?>
		<div class="alert alert-danger" role="alert">
			<p><?=$form_error?></p>
		</div>
	<?php } ?>


    <!--<div class="container">-->
      <div class="row">

          <div class="col">
        	<?php
        	foreach(FIELDS as $k){?>
        		<div class="mb-3">
        			<label for="<?=$k?>" class="form-label"><?php echo (ucfirst($k)); ?></label>
        			<input type="text" class="form-control" name="<?=$k?>" id="<?=$k?>"
        			value="<?php echo (isset($_POST[$k]) ? $_POST[$k] : ''); ?>" />
        		</div>

        	<?php	} ?>

          </div>

          <div class="col">
            <?PHP
        	foreach(COLORS as $k){?>
        		<div class="mb-3">
        			<label for="<?=$k?>" class="form-label"><?php echo (ucfirst($k)); ?></label>
        			<input type="color" class="form-control" name="<?=$k?>" id="<?=$k?>"
        			value="<?php echo (isset($_POST[$k]) ? $_POST[$k] : ''); ?>" />
        		</div>
        	<?php } ?>

        	<div class="mb-3" style="text-align: right;">
    			<label class="form-label">&nbsp; </label><br><br>
    			<button type="submit" class="btn btn-primary" name='generate'>Generate</button>
    		</div>


          </div>

      </div>


    </div>


</form>
<div align="left"><a href="postgis.php" class="btn btn-success"><span class="mdi mdi-arrow-left-bold"></span>&nbsp;Return to PostGIS</a></div>
</div>
<?php } ?>




          <!-- 7. end advertise card -->





                <!-- ============================================================== -->
                <!-- Start Page Content -->
                <!-- ============================================================== -->
                <div class="row">
                    <div class="col-12">

                    </div>
                    <div class="col-12">

                    </div>
                    <div class="col-12">

                    </div>
                    <div class="col-12">

                    </div>
                    <div class="col-12">

                    </div>
                    <div class="col-12">

                    </div>
                </div>
                <!-- ============================================================== -->
                <!-- End PAge Content -->
                <!-- ============================================================== -->
                <!-- ============================================================== -->
                <!-- Right sidebar -->
                <!-- ============================================================== -->
                <!-- .right-sidebar -->
                <!-- ============================================================== -->
                <!-- End Right sidebar -->
                <!-- ============================================================== -->
            </div>
            <!-- ============================================================== -->
            <!-- End Container fluid  -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- footer -->
            <!-- ============================================================== -->
            <footer class="footer text-center">

            </footer>
            <!-- ============================================================== -->
            <!-- End footer -->
            <!-- ============================================================== -->
        </div>
        <!-- ============================================================== -->
        <!-- End Page wrapper  -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Wrapper -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- All Jquery -->
    <!-- ============================================================== -->
    <!--<script src="assets/libs/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap tether Core JavaScript -->
    <!--<script src="assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="dist/js/app-style-switcher.js"></script>
    <!--Wave Effects -->
    <!--<script src="dist/js/waves.js"></script>
    <!--Menu sidebar -->
    <script src="dist/js/sidebarmenu.js"></script>
    <!--Custom JavaScript -->
    <script src="dist/js/custom.js"></script>
</body>

</html>
