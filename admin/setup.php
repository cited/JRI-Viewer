<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);


if(file_exists('db_conn.php')){
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
		copy("db.conn.config.php","db_conn.php");
		$file="db_conn.php";
		file_put_contents($file,str_replace("db_host",$host,file_get_contents($file)));
		file_put_contents($file,str_replace("db_port",$port,file_get_contents($file)));
		file_put_contents($file,str_replace("db_username",$dbuname,file_get_contents($file)));
		file_put_contents($file,str_replace("db_password",$dbpwd,file_get_contents($file)));
		file_put_contents($file,str_replace("db_name",$dbname,file_get_contents($file)));
		file_put_contents($file,str_replace("db_name",$dbname,file_get_contents($file)));

		$sql="CREATE TABLE users (
            id SERIAL PRIMARY KEY,
            role VARCHAR(10) NOT NULL,
            username VARCHAR(255) NOT NULL,
            password VARCHAR(255) NOT NULL,
            name VARCHAR(255) NOT NULL
        );";
		pg_query($con,$sql);

		$sql="CREATE TABLE hosts (
			id SERIAL PRIMARY KEY,
			host VARCHAR(255) NOT NULL,
			username VARCHAR(255) NOT NULL,
			password VARCHAR(255) NOT NULL,
			ssh_port INTEGER NOT NULL
		);";
		pg_query($con,$sql);

		$sql="INSERT INTO users (role, username, password, name) VALUES
			('admin', 'admin1', '81dc9bdb52d04dc20036dbd8313ed055', 'J. Admin'),
			('user', 'user1', '81dc9bdb52d04dc20036dbd8313ed055', 'J. User'),
			('devel', 'developer1', '81dc9bdb52d04dc20036dbd8313ed055', 'J. Developer');";
		pg_query($con,$sql);

		# list of access groups
		$sql = "CREATE TABLE access_groups (
		  id 				SERIAL PRIMARY KEY,
			name 			VARCHAR(255) NOT NULL
		);";
		pg_query($con,$sql);

		# maps groups to reports
		$sql = "CREATE TABLE report_access (
		  id 			SERIAL PRIMARY KEY,
			access_group_id	integer NOT NULL,
			report_id		integer NOT NULL,
			CONSTRAINT UC_access UNIQUE (access_group_id,report_id),
			FOREIGN KEY (access_group_id) REFERENCES public.access_groups(id),
			FOREIGN KEY (report_id) 			REFERENCES public.jasper(id)
		);";
		pg_query($con,$sql);

		# maps groups to reports
		$sql = "CREATE TABLE group_access (
		  id 			SERIAL PRIMARY KEY,
			access_group_id	integer NOT NULL,
			report_group_id	integer NOT NULL,
			CONSTRAINT UC_gaccess UNIQUE (access_group_id,report_group_id),
			FOREIGN KEY (access_group_id) REFERENCES public.access_groups(id),
			FOREIGN KEY (report_group_id)	REFERENCES public.groups(id)
		);";
		pg_query($con,$sql);

		# maps access groups to featursrv
		$sql = "CREATE TABLE wms_access (
			id 			SERIAL PRIMARY KEY,
			access_group_id	integer NOT NULL,
			wms_id					integer NOT NULL,
			CONSTRAINT UC_waccess UNIQUE (access_group_id,wms_id),
			FOREIGN KEY (access_group_id) REFERENCES public.access_groups(id),
			FOREIGN KEY (wms_id)	REFERENCES public.wms(id)
		);";
		pg_query($con,$sql);

		# maps access groups to postgis
		$sql = "CREATE TABLE pguser_access (
			id 			SERIAL PRIMARY KEY,
			access_group_id	integer NOT NULL,
			pguser_id				integer NOT NULL,
			CONSTRAINT UC_pgaccess UNIQUE (access_group_id,pguser_id),
			FOREIGN KEY (access_group_id) REFERENCES public.access_groups(id),
			FOREIGN KEY (pguser_id)	REFERENCES public.pguser(id)
		);";
		pg_query($con,$sql);

		# maps users to access groups
		$sql = "CREATE TABLE user_access (
		  id 			SERIAL PRIMARY KEY,
			user_id					integer NOT NULL,
			access_group_id	integer NOT NULL,
			CONSTRAINT UC_groups UNIQUE (user_id,access_group_id),
			FOREIGN KEY (user_id) REFERENCES public.user(id),
			FOREIGN KEY (access_group_id) REFERENCES public.access_groups(id)
		)";
		pg_query($con,$sql);

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

	  <div align="center"><p>&nbsp;</p><img src="img/jri-admin-logo.png"></div>

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
			  <th scope="row">SSH2 Installed</th>
			  <td>
				<?php
				$ssh2_connect=function_exists('ssh2_connect');
				if($ssh2_connect){
					echo "<span class='success'>Yes</span>";
				}else{
					echo "<span class='error'>Not Detected</span>";
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
