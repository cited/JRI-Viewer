<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);


if(file_exists('class/database.php')){
	header('location:index.php');
	die();
}
$msg="";
$host="";
$port="5432";
$dbuname="";
$dbpwd="";
$dbname="";
if(isset($_POST['submit'])){
	$host=$_POST['host'];
	$port=$_POST['port'];
	$dbuname=$_POST['dbuname'];
	$dbpwd=$_POST['dbpwd'];
	$dbname=$_POST['dbname'];

	$con = pg_connect("dbname=$dbname user=$dbuname password=$dbpwd host=$host port=$port");
	if(!$con){
		$msg= pg_last_error($con);
	}else{
		$file_data = file_get_contents('class/database.config.php');

		$file_data = str_replace('"db_host"',			$host, 		$file_data);
		$file_data = str_replace('"db_port"',			$port, 		$file_data);
		$file_data = str_replace('"db_username"',	$dbuname,	$file_data);
		$file_data = str_replace('"db_password"',	$dbpwd,		$file_data);
		$file_data = str_replace('"db_name"',			$dbname, 	$file_data);

		file_put_contents('class/database.php', $file_data);

		$sql = file_get_contents('setup.sql');
		$res = pg_query($con, $sql);
		if(!$res){
			echo pg_last_error($dbconn);
			die();
		}

		header('location:index.php');
	}
}
?>

<!doctype html>
<html lang="en">
   <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <title>PHP Installer</title>
      <link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">
      <style>
		table{width:30% !important; text-align:center; margin:auto; margin-top:70px;}
		.success{color:green;}
		.error{color:red;}
		.frm{width:70% !important; margin:auto; margin-top:100px;}
	  </style>
   </head>
   <body>

      <main role="main" class="container">
         <?php
			if((isset($_GET['step'])) && $_GET['step']==2){
				?>
				<div align="center"><p>&nbsp;</p><img src="img/jri-admin-logo.png"></div>

				<form class="frm" method="post">
				  <div class="form-group">
					<input type="text" class="form-control" placeholder="Host" required name="host" value="<?php echo $host?>">
			  </div>
			  <div class="form-group">
					<input type="number" class="form-control" placeholder="Port Number" required name="port" value="<?php echo $port?>">
			  </div>
			  <div class="form-group">
				<input type="text" class="form-control" placeholder="Database User Name" required name="dbuname" value="<?php echo $dbuname?>">
			  </div>
			  <div class="form-group">
				<input type="text" class="form-control" placeholder="Database Password" name="dbpwd" value="<?php echo $dbpwd?>">
			  </div>
			  <div class="form-group">
				<input type="text" class="form-control" placeholder="Database Name" required name="dbname" value="<?php echo $dbname?>">
			  </div>
			  <button type="submit" name="submit" class="btn btn-primary">Submit</button>
			  <span class="error"><?php echo $msg?></span>
			</form>

			<?php
		}else{
		?>

	  <div align="center"><p>&nbsp;</p>JRI Viewer Installer</div>

         <table class="table">
		  <thead>
			<tr>
			  <th scope="col">Requirement</th>
			  <th scope="col">Status</th>
			</tr>
		  </thead>
		  <tbody>
			<tr>
			  <th scope="row">PHP Version</th>
			  <td>
				<?php
					$is_error="";
					$php_version=phpversion();
					if($php_version>5){
						echo "<span class='success'>".$php_version."</span>";
					}else{
						echo "<span class='error'>".$php_version."</span>";
						$is_error='yes';
					}
				?>
			  </td>
			</tr>

			
			<tr>
			  <th scope="row">Session Working</th>
			  <td>
				<?php
				$_SESSION['IS_WORKING']=1;
				if(!empty($_SESSION['IS_WORKING'])){
					echo "<span class='success'>Yes</span>";
				}else{
					echo "<span class='error'>No</span>";
					$is_error='yes';
				}
				?>
			  </td>
			</tr>

			<tr>
			  <td colspan="2">
				<?php
				if($is_error==''){
					?>
					<a href="?step=2"><button type="button" class="btn btn-success">Next</button></a>
					<?php
				}else{
					?><button type="button" class="btn btn-danger">Errors</button><br><br>Please fix above error(s) and try again<?php
				}
				?>
			  </td>
			</tr>
		  </tbody>

		</table>
		<?php }?>

      </main>

      <script src="https://getbootstrap.com/docs/4.0/assets/js/vendor/popper.min.js"></script>
      <script src="https://getbootstrap.com/docs/4.0/dist/js/bootstrap.min.js"></script>
   </body>
</html>
