<?php
    session_start();
		require_once('class/user.php');
		require_once('class/access_groups.php');

    if(!isset($_SESSION['user']) || $_SESSION['user']->accesslevel != 'Admin') {
        header('Location: ../login.php');
    }

    $host = "localhost";
    $port = "5432";
    $dbname = "exhibit1836_users";
    $user = "exhibit1836";
    $password = "Tristan1902";
    $connection_string = "host={$host} port={$port} dbname={$dbname} user={$user} password={$password} ";
    $dbconn = pg_connect($connection_string);

		$acc_ojb = new access_group_Class($dbconn);
    $acc_grp = $acc_ojb->getAccessGroupsArr();

    if(isset($_POST['submit'])&&!empty($_POST['submit'])){
			$usr_ojb = new user_Class($dbconn);
			$ret = $usr_ojb->create($_POST);
			if($ret > 0){
				header("Location: users.php");
			}else{
				echo "Soething Went Wrong";
			}
    }

?>
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
	<?php include("incl/meta.php"); ?>
	<link href="dist/css/table.css" rel="stylesheet">
		<script type="text/javascript">
			$(document).ready(function() {
						$('[data-toggle="tooltip"]').tooltip();
						var actions = $("table td:last-child").html();
						// Append table with add row form on add new button click
						$(".add-new").click(function() {
						    //var actions = $("table td:last-child").html();
							$(this).attr("disabled", "disabled");
							var index = $("table tbody tr:last-child").index();

							var row = '<tr>';

							$("table thead tr th").each(function(k, v) {
							    if($(this).attr('data-editable') == 'false') {

							        if($(this).attr('data-action') == 'true') { // last child or actions cell
							            row += '<td>'+actions+'</td>';
							        }
							        else {
							            row += '<td></td>';
							        }
							    }
							    else {
							        row += ' <td> <input type = "text" class = "form-control" name="'+$(this).attr('data-name')+'"> </td>';
							    }
							});

							row += '</tr>';


							/*var row = ' < tr > ' +
							' <td> <input type = "text" class = "form-control" name = "name" id = "name" > </td>' +
							' <td> <input type = "text" class = "form-control" name = "department" id = "department" > </td>' +
							' <td> <input type = "text" class = "form-control" name = "phone" id = "phone" > </td>' +
							' <td> <input type = "text" class = "form-control" name = "phone" id = "phone" > </td>' +
							' <td> <input type = "text" class = "form-control" name = "phone" id = "phone" > </td>' +
							' <td> <input type = "text" class = "form-control" name = "phone" id = "phone" > </td>' +
							' <td> <input type = "text" class = "form-control" name = "phone" id = "phone" > </td>' +
							' <td> <input type = "text" class = "form-control" name = "phone" id = "phone" > </td>' +
							' <td> <input type = "text" class = "form-control" name = "phone" id = "phone" > </td>' +
							' <td> <input type = "text" class = "form-control" name = "phone" id = "phone" > </td>' +
							' <td> <input type = "text" class = "form-control" name = "phone" id = "phone" > </td>' +
							' <td> <input type = "text" class = "form-control" name = "phone" id = "phone" > </td>' +
							' <td> <input type = "text" class = "form-control" name = "phone" id = "phone" > </td>' +
							' < td > ' + actions + ' </td>' +
							' < /tr>';*/

							$("table").append(row);
							$("table tbody tr").eq(index + 1).find(".add, .edit").toggle();
							$('[data-toggle="tooltip"]').tooltip();
						});



						// Add row on add button click
						$(document).on("click", ".add", function() {
						    var obj = $(this);
							var empty = false;
							var input = $(this).parents("tr").find('input[type="text"]');
							input.each(function() {
								if (!$(this).val()) {
									$(this).addClass("error");
									empty = true;
								} else {
									$(this).removeClass("error");
								}
							});

							$(this).parents("tr").find(".error").first().focus();
							if (!empty) {
								var data = {};
								data['save'] = 1;
								data['id'] = $(this).closest('tr').attr('data-id');

								input.each(function() {
									$(this).parent("td").html($(this).val());
									data[$(this).attr('name')] = $(this).val();
								});

								$.ajax({
                                    type: "POST",
                                    url: 'action/user.php',
                                    data: data,
                                    dataType:"json",
                                    success: function(response){
                                        if(response.id) { // means, new record is added
                                            obj.closest('table').find('tr:last-child').attr('data-id', response.id);
                                            obj.closest('table').find('tr:last-child td:first-child').text(response.id)
                                        }
                                        alert(response.message)
                                    }
                                });

								$(this).parents("tr").find(".add, .edit").toggle();
								$(".add-new").removeAttr("disabled");
							}
						});



						// Edit row on edit button click
						$(document).on("click", ".edit", function() {
    								$(this).parents("tr").find("td:not([data-editable=false])").each(function(k, v) {

    								    if($(this).closest('table').find('thead tr th').eq(k).attr('data-editable') != 'false') {
        								    var name = $(this).closest('table').find('thead tr th').eq(k).attr('data-name');
        									$(this).html(' <input type = "text" name="'+ name +'" class = "form-control" value = "' + $(this).text() + '" > ');
    								    }


									});

									$(this).parents("tr").find(".add, .edit").toggle(); $(".add-new").attr("disabled", "disabled");
								});



							// Delete row on delete button click
							$(document).on("click", ".delete", function() {
							    var obj = $(this);
							    var data = {'delete': true, 'id': obj.parents("tr").attr('data-id')}

							    $.ajax({
                                    type: "POST",
                                    url: 'action/ajax.php',
                                    data: data,
                                    dataType:"json",
                                    success: function(response){
                                        if(response.success) { // means, new record is added
                                            obj.parents("tr").remove();
                                        }

                                        $(".add-new").removeAttr("disabled");
                                        alert(response.message);
                                    }
                                });

							});
						});
		</script>

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

        <?php define('MENU_SEL', 'registration.php');
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
                        <h1 class="mb-0 fw-bold">Register New User</h1>
                    </div>
                    <div class="col-6">
                        <div class="text-end upgrade-btn">
                            <!--<a href="https://www.wrappixel.com/templates/flexy-bootstrap-admin-template/" class="btn btn-primary text-white"
                                target="_blank">Add New</a>-->




                        </div>
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

				<table class="table table-bordered">


					<tbody>

<form method="post">

    <div class="form-group">
      <label for="name">Name:</label>
      <input type="text" class="form-control" id="name" placeholder="Enter name" name="name" requuired>
    </div>

    <div class="form-group">
      <label for="email">Email:</label>
      <input type="email" class="form-control" id="email" placeholder="Enter email" name="email">
    </div>

    <div class="form-group">
      <label for="accesslevel">Access Level:</label>
      <select name="accesslevel" id="accesslevel">
	<option value="User">User</option>
	<option value="Admin">Admin</option>
</select>


      <!--<input type="text" class="form-control" maxlength="10" id="mobileno" placeholder="User Access Level" name="accesslevel">-->
    </div>


		<div class="form-group">
			<fieldset>
			<legend>Access Groups:</legend>
			<?php
				foreach($acc_grp as $group_id => $name){ ?>
				<p>
					<input type="checkbox" name="groups[]" id="group_<?=$group_id?>" value="<?=$group_id?>"/>
					<label for="group_<?=$group_id?>" class="form-label"><?=$name?></label>
				</p>
			<?php } ?>
			</fieldset>
    </div>



    <div class="form-group">
      <label for="pwd">Password:</label>
      <input type="password" class="form-control" id="password" placeholder="Enter password" name="password">
    </div>

    <input type="submit" name="submit" class="btn btn-primary" value="Submit">
  </form>



					</tbody>
				</table>







                <!-- ============================================================== -->
                <!-- Start Page Content -->
                <!-- ============================================================== -->
                <div class="row">


                    <div class="col-6">
						<p>&nbsp;</p>
						<div id = "repThumbnail" class = "alert alert-danger">
   <a href = "#" class = "close" data-dismiss = "alert">&times;</a>
   <strong>Note:</strong> Be sure to set the Access Level for the user.
</div>



<script type = "text/javascript">
   $(function(){
      $(".close").click(function(){
         $("#repThumbnail").alert();
      });
   });
</script>
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
