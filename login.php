<?php
    session_start();
    if(isset($_SESSION['user'])) {
        header("Location: index.php");
    }
    
    $host = "localhost";
    $port = "5432";
    $dbname = "exhibit1836_users";
    $user = "exhibit1836";
    $password = "Tristan1902"; 
    $connection_string = "host={$host} port={$port} dbname={$dbname} user={$user} password={$password} ";
    $dbconn = pg_connect($connection_string);
    
    if(isset($_POST['submit'])&&!empty($_POST['submit'])){
        
        $hashpassword = md5($_POST['pwd']);
        $sql ="select * from public.user where email = '".pg_escape_string($dbconn, $_POST['email'])."' and password ='".$hashpassword."'";
        $data = pg_query($dbconn,$sql); 
        
        $row = pg_fetch_object($data);
        $login_check = pg_num_rows($data);
        
        if($login_check > 0 && $row){ 
            $_SESSION['user'] = $row;
            header("Location: index.php");
        }else{
            
            echo "Invalid Details";
        }
    }


?>
<html>
<head>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
	<style>
.footer {
  position: fixed!important;
  left: 0!important;
  bottom: 0!important;
  width: 100%!important;
}
</style>
</head>
<body>
<section class="vh-100" style="height: 92.5vh!important;">
  <div class="container-fluid h-custom">
    <div class="row d-flex justify-content-center align-items-center h-100">
      <div class="col-md-9 col-lg-6 col-xl-5">
        <img src="assets/maps/login.png"
          class="img-fluid" alt="JRI Viewer Login">
      </div>
      <div class="col-md-8 col-lg-6 col-xl-4 offset-xl-1">
        <form method="post">
          

          <!-- Email input -->
          <div class="form-outline mb-4">
            <input type="email" class="form-control form-control-lg" id="email"   placeholder="Enter a valid email address" name="email"/>


            <label class="form-label" for="form3Example3">Email address</label>
          </div>

          <!-- Password input -->
          <div class="form-outline mb-3">
            <input type="password" class="form-control" id="pwd" placeholder="Enter password" name="pwd">
             
            <label class="form-label" for="form3Example4">Password</label>
          </div>

          <div class="d-flex justify-content-between align-items-center">
            <!-- Checkbox -->
            
          </div>

          <div class="text-center text-lg-start mt-4 pt-2">
            <input type="submit" class="btn btn-primary btn-lg" style="padding-left: 2.5rem; padding-right: 2.5rem;" name="submit" class="btn btn-primary" value="Submit"></button>            
          </div>
        </form>
      </div>
    </div>
  </div>
	   
  </div>
</section>
<footer>
  <div class="d-flex flex-column flex-md-row text-center text-md-start justify-content-between py-4 px-4 px-xl-5 bg-primary">
    <!-- Copyright -->
    <div class="text-white mb-3 mb-md-0">
      Cited, Inc. &copy; 2023. All rights reserved.
    </div> </footer>

</body>
</html>