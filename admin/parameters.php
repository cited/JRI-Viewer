<?php
    session_start();

    require_once('class/database.php');
    require_once('class/parameter.php');
    require_once('class/report.php');

		$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
		$dbconn = $database->getConn();

    $obj = new parameter_Class($dbconn);
    $rows = $obj->getRows();

    $obj = new Report_Class($dbconn);
    $users_obj = $obj->getRows();
    $users = [];
    while($us = pg_fetch_object($users_obj)) {
        $users[$us->id] = $us->repname;
    }

    if(!isset($_SESSION['user'])) {// || $_SESSION['user']->accesslevel != 'Admin') {
        header('Location: ../login.php');
        exit;
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
						var actions = `
							<a class="add" title="Add" data-toggle="tooltip">
								<i class="material-icons">&#xE03B;</i>
							</a>
							<a class="edit" title="Edit" data-toggle="tooltip">
								<i class="material-icons">&#xE254;</i>
							</a>
							<a class="delete" title="Delete" data-toggle="tooltip">
								<i class="material-icons">&#xE872;</i>
							</a>
						`;
						//$("table td:last-child").html();
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

							        if($(this).attr('data-type') == 'select') {
							            row += `
							                <td data-type="select" data-value="0">
							                    <select name="`+$(this).attr('data-name')+`">
							                        <?PHP foreach($users as $k => $v) { ?>
							                        <option value="<?=$k?>"><?=$v?></option>
							                        <?PHP } ?>
							                    </select>
							                </td>
							            `;
							        }
							        else {
							            row += ' <td> <input type = "text" class = "form-control" name="'+$(this).attr('data-name')+'"> </td>';
							        }
							    }
							});

							row += '</tr>';

							$("table").append(row);
							$("table tbody tr").eq(index + 1).find(".add, .edit").toggle();
							$('[data-toggle="tooltip"]').tooltip();
						});



						// Add row on add button click
						$(document).on("click", ".add", function() {
						    var obj = $(this);
							var empty = false;
							var input = $(this).parents("tr").find('input[type="text"], select');
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
								    if($(this).closest('td').attr('data-type') == 'select') {
								        var val = $(this).find('option:selected').text();
								        $(this).parent("td").attr('data-value', $(this).val());
								        $(this).parent("td").html(val);
								    }
								    else {
								        $(this).parent("td").html($(this).val());
								    }

									data[$(this).attr('name')] = $(this).val();
								});

								$.ajax({
                                    type: "POST",
                                    url: 'action/parameter.php',
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

        									if($(this).closest('table').find('thead tr th').eq(k).attr('data-type') == 'select') {
        									    $(this).html(`
            									    <select name="`+name+`">
    							                        <?PHP foreach($users as $k => $v) { ?>
    							                        <option value="<?=$k?>"><?=$v?></option>
    							                        <?PHP } ?>
    							                    </select>
							                    `);

							                    var val = $(this).attr('data-value');
							                    $(this).find('[name='+name+']').val(val);
        									}
        									else {
        									    $(this).html(' <input type = "text" name="'+ name +'" class = "form-control" value = "' + $(this).text() + '" > ');
        									}
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
                                    url: 'action/parameter.php',
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
    <!-- Main wrapper - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <div id="main-wrapper" data-layout="vertical" data-navbarbg="skin5" data-sidebartype="full"
        data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">

        <?php define('MENU_SEL', 'parameters.php');
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
                        <h1 class="mb-0 fw-bold">Parameters</h1>
                    </div>
                    <div class="col-6">
                        <div class="text-end upgrade-btn">
                            <!--<a href="https://www.wrappixel.com/templates/flexy-bootstrap-admin-template/" class="btn btn-primary text-white"
                                target="_blank">Add New</a>-->

							<button type="button" class="btn btn-primary text-white add-new">
								<i class="fa fa-plus"></i> Add New </button><br>


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
					<thead>
						<tr>
							<th data-name="id" data-editable='false'>ID</th>

							<th data-name="ptype">Paramater Type</th>
                                                        <th data-name="pname">Paramater Name</th>
							<th data-name="pvalues">Parameter Values</th>
							<th data-name="reportid" data-type="select">Report Name</th>
							<th data-editable='false' data-action='true'>Actions</th>
						</tr>
					</thead>

					<tbody> <?php while($row = pg_fetch_object($rows)): ?> <tr data-id="<?=$row->id?>" align="left">
							<td><?=$row->id?></td>

							<td><?= $row->ptype?></td>
							<td><?= $row->pname?></td>
							<td><?= $row->pvalues?></td>
							<td data-type="select" data-value="<?=$row->reportid?>"><?=$users[$row->reportid]?></td>


							<td>
								<a class="add" title="Add" data-toggle="tooltip">
									<i class="material-icons">&#xE03B;</i>
								</a>
								<a class="edit" title="Edit" data-toggle="tooltip">
									<i class="material-icons">&#xE254;</i>
								</a>
								<a class="delete" title="Delete" data-toggle="tooltip">
									<i class="material-icons">&#xE872;</i>
								</a>
							</td>
						</tr> <?php endwhile; ?> </tr>
					</tbody>
				</table>





                <!-- ============================================================== -->
                <!-- Start Page Content -->
                <!-- ============================================================== -->
                <div class="row">


                    <div class="col-12">

						<div class="col-6">
						<p>&nbsp;</p>
						<div id = "repThumbnail" class = "alert alert-warning">
   <a href = "#" class = "close" data-dismiss = "alert">&times;</a>
   <strong>Note:</strong> The Parameter Name must be the Jasper Parameter name.
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
