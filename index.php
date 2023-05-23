<?php
    session_start();
		require_once('admin/class/database.php');
    require_once('admin/class/report.php');
		require_once('admin/class/user.php');
		require_once('admin/class/access_groups.php');

		$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
    $obj			= new Report_Class($database->getConn());
		$usr_obj	= new user_Class($database->getConn());
		$acc_obj	= new access_group_Class($database->getConn());

		$users = $obj->getRows();

    if(!isset($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }

    if(isset($_POST['logout'])) {
        unset($_SESSION['user']);
        header('Location: login.php');
        exit;
    }

    $user = $_SESSION['user'];
		$usr_grps = $usr_obj->getUserAccessGroups($user->id);

		# reports from access groups
		$group_rows = array();
		if(count($usr_grps)){
			# get report IDs from access groups
			$usr_reps = $acc_obj->getGroupReports(array_keys($usr_grps));
			if(count($usr_reps)){
				$group_rows = $database->getAll('jasper', "id IN (".implode(',', array_keys($usr_reps)).") AND is_grouped = 0", 'id');
			}

			# reports from report groups we own
			$usr_rep_grps = $acc_obj->getGroupReportGroups(array_keys($usr_grps));
			$usr_rep_grp_ids = array_keys($usr_rep_grps);
	    $rows1 = $database->getAll('groups', "id IN (".implode(',', $usr_rep_grp_ids).")", 'id');
		}

    $rows2 = $database->getAll('wms', "wms.owner = {$user->id}", 'id');
    $rowspg = $database->getAll('pguser', "pguser.owner = {$user->id}", 'id');
    //$rowstiles = $database->getAll('tiles', "tiles.owner = {$user->id}", 'id');
    //$welcome = ($_SESSION['jasper.owner']);
    //$_SESSION['user'] = $row['name'];
?>


<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Hugo 0.84.0">
    <title>JRI Map Viewer</title>



    <!-- Bootstrap core CSS -->
    <link href="assets/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
      .bd-placeholder-img {
        font-size: 1.125rem;
        text-anchor: middle;
        -webkit-user-select: none;
        -moz-user-select: none;
        user-select: none;
      }

      @media (min-width: 768px) {
        .bd-placeholder-img-lg {
          font-size: 3.5rem;
        }
      }

      .card {
          box-shadow: 0 0.15rem 0.55rem rgba(0, 0, 0, 0.1);
          transition: box-shadow 0.3s ease-in-out;
        }

        .card:hover {
          box-shadow: 0 0.35rem 0.85rem rgba(0, 0, 0, 0.3);
        }
        .col {
            padding-right: calc(var(--bs-gutter-x) * .75);
            padding-left: calc(var(--bs-gutter-x) * .75);
        }
    </style>

  </head>
  <body>

<header>

  <div class="navbar navbar-dark bg-dark shadow-sm" style="background-color:var(--neutral-secondary-color,#666)!important">
    <div class="container">
      <a href="#" class="navbar-brand d-flex align-items-center">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-text" viewBox="0 0 16 16">
  <path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1h-5zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5z"/>
  <path d="M9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5L9.5 0zm0 1v2A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/>
</svg>        <strong> &nbsp;JRI Report Viewer</strong>
      </a>

<?php
if($user->accesslevel == 'Admin') {
  echo '<a href="admin/index.php" style="text-decoration:none; color: #fff!important; font-size: 1.25rem; font-weight: 300;">Administration</a>';
}
?>


      <a href="logout.php" style="text-decoration:none; color: #fff!important; font-size: 1.25rem; font-weight: 300;">Log Out</a>


    </div>
  </div>
</header>


<main style="background-color:#edf0f2">

  <section class="py-5 text-left container" style="padding-bottom: 0rem!important;">
    <div class="row py-lg-5">
      <div class="col-lg-6 col-md-8 mx-auto" style="margin-left: 5px!important;">
        <h1 class="fw-light"><?php echo $_SESSION['user']->name;?> Reports

  </h1>
        <p class="lead text-muted">Reports</p>
      </div>
    </div>
  </section>
  <div class="album py-5 bg-light">
    <div class="container">







        <div class="row row-cols-1 row-cols-md-4 g-4">

					<?PHP foreach($group_rows as $row) { ?>
						<?PHP
								$image = file_exists("assets/maps/{$row['id']}.png") ? "assets/maps/{$row['id']}.png" : "assets/maps/default.png";

								if(strtolower($row['download_only']) === 'yes') {
									$url = 'download.php?type=pdf&view=yes&id='.$row['id'];
								}else{
									$url = 'view.php?id='.$row['id'];
								}
						?>

					<div class="col">
						<a href="<?=$url?>" style="text-decoration:none; color: #6c757d!important; font-size: 1.25rem; font-weight: 300;">
								<div class="card">
									<div class="card-body">
										<h5 class="card-title" style="font-size: 15px; font-weight: 800;"><?=$row['name']?></h5>
									</div>
									<div class="px-3">
										<div style="height: 150px; width: 100%; background: url('<?=$image?>') no-repeat; background-size: cover; background-position: center center;"></div>
									</div>
									<?PHP if($row['description']) { ?>
											<div class="card-body">
												<p class="card-text" style="color: #6c757d!important; font-size: 15px; font-weight: 600;"> <?=$row['description']?> </p>
											</div>
									<?PHP } ?>
								</div>
						</a>
					</div>

					<?PHP } ?>



          <?PHP foreach($rows1 as $row) { ?>
            <?PHP
                $image = file_exists("assets/maps/{$row['id']}.png") ? "assets/maps/{$row['id']}.png" : "assets/maps/default.png";
            ?>

          <div class="col">
            <a href="view.php?group_id=<?=$row['id']?>" style="text-decoration:none; color: #6c757d!important; font-size: 1.15rem; font-weight: 300;">
                <div class="card">
                  <div class="card-body">
                    <h5 class="card-title" style="font-size: 15px; font-weight: 800;"><?=$row['name']?></h5>
                  </div>
                  <div class="px-3">
                    <div style="height: 150px; width: 100%; background: url('<?=$image?>') no-repeat; background-size: cover; background-position: center center;"></div>
                  </div>
                  <?PHP if($row['description']) { ?>
                      <div class="card-body">
                        <p class="card-text" style="color: #6c757d!important; font-size: 15px; font-weight: 600;"> <?=$row['description']?> </p>
                      </div>
                  <?PHP } ?>
                </div>
            </a>
          </div>
          <?PHP } ?>





    <!-- David Add -->

 <?PHP foreach($rowspg as $row) { ?>
            <?PHP
                $image = file_exists("pg/assets/maps/{$row['id']}.png") ? "pg/assets/maps/{$row['id']}.png" : "pg/assets/maps/default.png";
                $url = $row['pgtileurl'] ? "pg/tilemap.php?id=".$row['id'] : "pg/map.php?id=".$row['id'] ;
                // if pgtileurl is not set then itsgo with PG otherwise Tile
            ?>

          <div class="col">
            <a href="<?=$url?>" style="text-decoration:none; color: #6c757d!important; font-size: 1.25rem; font-weight: 300;">
                <div class="card">
                  <div class="card-body">
                    <h5 class="card-title" style="font-size: 15px; font-weight: 800;"><?=$row['name']?></h5>
                  </div>
                  <div class="px-3">
                    <div style="height: 150px; width: 100%; background: url('<?=$image?>') no-repeat; background-size: cover; background-position: center center;"></div>
                  </div>
                  <?PHP if($row['description']) { ?>
                      <div class="card-body">
                        <p class="card-text" style="color: #6c757d!important; font-size: 15px; font-weight: 600;"> <?=$row['description']?> </p>
                      </div>
                  <?PHP } ?>
                </div>
            </a>
          </div>

          <?PHP } ?>




  <?PHP foreach($rows2 as $row) { ?>
             <?PHP
                $image = file_exists("gs/assets/maps/{$row['id']}.png") ? "gs/assets/maps/{$row['id']}.png" : "gs/assets/maps/default.png";
            ?>

          <div class="col">
	<a href="gs/map.php?id=<?=$row['id']?>" style="text-decoration:none; color: #6c757d!important; font-size: 1.25rem; font-weight: 300;">
                <div class="card">
                  <div class="card-body">
                    <h5 class="card-title" style="font-size: 15px; font-weight: 800;"><?=$row['name']?></h5>
                  </div>
                  <div class="px-3">
                     <div style="height: 150px; width: 100%; background: url('<?=$image?>') no-repeat; background-size: cover; background-position: center center;"></div>
                  </div>
                  <?PHP if($row['collection']) { ?>
                      <div class="card-body">
                        <p class="card-text" style="color: #6c757d!important; font-size: 15px; font-weight: 600;"> <?=$row['name']?></p>
                      </div>
                  <?PHP } ?>
                </div>
            </a>
          </div>

          <?PHP } ?>















</div>




    </div>
  </div>

</main>

<footer class="text-muted py-5">
  <div class="container">
    <p class="float-end mb-1">
<a href="#" style="text-decoration:none; color: #6c757d!important; font-size: 1.25rem; font-weight: 300;">Back to top</a>    </p>
  </div>
</footer>
    <script src="assets/dist/js/bootstrap.bundle.min.js"></script>

  </body>
</html>
