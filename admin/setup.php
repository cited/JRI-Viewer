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
		copy("class/database.config.php","class/database.php");
		$file="db_conn.php";
		file_put_contents($file,str_replace("db_host",$host,file_get_contents($file)));
		file_put_contents($file,str_replace("db_port",$port,file_get_contents($file)));
		file_put_contents($file,str_replace("db_username",$dbuname,file_get_contents($file)));
		file_put_contents($file,str_replace("db_password",$dbpwd,file_get_contents($file)));
		file_put_contents($file,str_replace("db_name",$dbname,file_get_contents($file)));
		file_put_contents($file,str_replace("db_name",$dbname,file_get_contents($file)));

	


$sql="CREATE TYPE public.level AS ENUM (
    'User',
    'Admin'
;";
	pg_query($con,$sql);



$sql="CREATE TYPE public.userlevel AS ENUM (
    'Admin',
    'User'
);";
	pg_query($con,$sql);




$sql="CREATE TABLE public.access_groups (
    id integer NOT NULL,
    name character varying(255) NOT NULL
);";
	pg_query($con,$sql);



$sql="CREATE SEQUENCE public.access_groups_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;";
 pg_query($con,$sql);


$sql="ALTER SEQUENCE public.access_groups_id_seq OWNED BY public.access_groups.id;";
	pg_query($con,$sql);



$sql="CREATE SEQUENCE public.user_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;";

	pg_query($con,$sql);



$sql="CREATE TABLE public.basemaps (
    id integer DEFAULT nextval('public.user_seq'::regclass) NOT NULL,
    basemap_name character varying(255),
    basemap_url character varying(255)
);";
	pg_query($con,$sql);


$sql="CREATE TABLE public.group_access (
    id integer NOT NULL,
    access_group_id integer NOT NULL,
    report_group_id integer NOT NULL
);";
	pg_query($con,$sql);



$sql="CREATE SEQUENCE public.group_access_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;";
	pg_query($con,$sql);



$sql="ALTER SEQUENCE public.group_access_id_seq OWNED BY public.group_access.id;";
	pg_query($con,$sql);


$sql="CREATE TABLE public.groups (
    id integer NOT NULL,
    name character varying(255),
    reportids character varying(255),
    owner numeric,
    description character varying(255)
);";
	pg_query($con,$sql);


$sql="CREATE SEQUENCE public.groups_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;";
	pg_query($con,$sql);


$sql="ALTER SEQUENCE public.groups_id_seq OWNED BY public.groups.id;";
	pg_query($con,$sql);


$sql="CREATE TABLE public.inputs (
    id integer DEFAULT nextval('public.user_seq'::regclass) NOT NULL,
    input character varying(2550),
    name character varying(255),
    report_id numeric
);";
	pg_query($con,$sql);



$sql="CREATE TABLE public.jasper (
    id integer NOT NULL,
    url character varying(255),
    repname character varying(200),
    datasource character varying(200),
    download_only character varying(200),
    outname character varying(200),
    name character varying(200),
    owner numeric(10,0),
    is_grouped numeric(10,0) DEFAULT 0,
    description character varying(255)
);";
	pg_query($con,$sql);



$sql="CREATE SEQUENCE public.jesper_ id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;";
	pg_query($con,$sql);


$sql="ALTER SEQUENCE public.jesper_ id_seq OWNED BY public.jasper.id;";
	pg_query($con,$sql);


$sql="CREATE TABLE public.parameters (
    id integer DEFAULT nextval('public.user_seq'::regclass) NOT NULL,
    reportid numeric,
    ptype character varying(250),
    pvalues character varying(250),
    pname character varying(250)
);";
	pg_query($con,$sql);



$sql="CREATE TABLE public.report_access (
    id integer NOT NULL,
    access_group_id integer NOT NULL,
    report_id integer NOT NULL
);";
	pg_query($con,$sql);


$sql="CREATE SEQUENCE public.report_access_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;";
	pg_query($con,$sql);


$sql="ALTER SEQUENCE public.report_access_id_seq OWNED BY public.report_access.id;";
	pg_query($con,$sql);



$sql="CREATE TABLE public.user (
    id integer NOT NULL,
    name character varying(250),
    email character varying(250),
    password character varying(250),
    accesslevel character varying
);";
	pg_query($con,$sql);


$sql="CREATE TABLE public.user_access (
    id integer NOT NULL,
    user_id integer NOT NULL,
    access_group_id integer NOT NULL
);";
	pg_query($con,$sql);


$sql="CREATE SEQUENCE public.user_access_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;";

	pg_query($con,$sql);


$sql="ALTER SEQUENCE public.user_access_id_seq OWNED BY public.user_access.id;";
	pg_query($con,$sql);


$sql="CREATE SEQUENCE public.user_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;";
	pg_query($con,$sql);


$sql="ALTER SEQUENCE public.user_id_seq OWNED BY public.user.id;";
	pg_query($con,$sql);


$sql="ALTER TABLE ONLY public.access_groups ALTER COLUMN id SET DEFAULT nextval('public.access_groups_id_seq'::regclass);";
	pg_query($con,$sql);

$sql="ALTER TABLE ONLY public.group_access ALTER COLUMN id SET DEFAULT nextval('public.group_access_id_seq'::regclass);";
	pg_query($con,$sql);

$sql="ALTER TABLE ONLY public.groups ALTER COLUMN id SET DEFAULT nextval('public.groups_id_seq'::regclass);";
	pg_query($con,$sql);

$sql="ALTER TABLE ONLY public.jasper ALTER COLUMN id SET DEFAULT nextval('public.jesper_ id_seq'::regclass);";
	pg_query($con,$sql);


$sql="ALTER TABLE ONLY public.report_access ALTER COLUMN id SET DEFAULT nextval('public.report_access_id_seq'::regclass);";
	pg_query($con,$sql);

$sql="ALTER TABLE ONLY public.user ALTER COLUMN id SET DEFAULT nextval('public.user_id_seq'::regclass);";
	pg_query($con,$sql);

$sql="ALTER TABLE ONLY public.user_access ALTER COLUMN id SET DEFAULT nextval('public.user_access_id_seq'::regclass);";
	pg_query($con,$sql);


$sql="INSERT INTO public.user VALUES
(1, 'John Smith', 'admin@admin.com', '1234', 'Admin');";







		
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
