<?php
    session_start();

    require_once('class/database.php');
    require_once('class/postgis.php');
    require_once('class/user.php');
		require_once('class/basemaps.php');
		require_once('class/access_groups.php');

		$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
		$dbconn = $database->getConn();

    $pg_obj = new postgis_class($dbconn);
    $pg_rows = $pg_obj->getRows();

		$obj = new basemap_Class($dbconn);
    $bm_rows = $obj->getRows();

		$bms = array();
		while($row = pg_fetch_object($bm_rows)){
			$bms[$row->basemap_url] = $row->basemap_name;
		}

    $obj = new user_Class($dbconn);
    $users_obj = $obj->getRows();
    $users = [];
    while($us = pg_fetch_object($users_obj)) {
        $users[$us->id] = $us->name;
    }

		$acc_obj = new access_group_Class($dbconn);
		$acc_grps = $acc_obj->getAccessGroupsArr();

    if(!isset($_SESSION['user']) || $_SESSION['user']->accesslevel != 'Admin') {
        header('Location: ../login.php');
        exit;
    }
?>

<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
	<?php include("incl/meta.php"); ?>
	<link href="dist/css/table.css" rel="stylesheet">
	<style type="text/css">
td {
    max-width: 100px !important;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>
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

							        if($(this).attr('data-type') == 'select') {
												if($(this).attr('data-name') == 'accgrps') {
							            row += `
							                <td data-type="select" data-value="0">
							                    <select name="`+$(this).attr('data-name')+`" multiple>
							                        <?PHP foreach($acc_grps as $k => $v) { ?>
							                        <option value="<?=$k?>"><?=$v?></option>
							                        <?PHP } ?>
							                    </select>
							                </td>
							            `;
												}else if($(this).attr('data-name') == 'basemap') {
							            row += `
							                <td data-type="select" data-value="0">
							                    <select name="`+$(this).attr('data-name')+`">
							                        <?PHP foreach($bms as $k => $v) { ?>
							                        <option value="<?=$k?>"><?=$v?></option>
							                        <?PHP } ?>
							                    </select>
							                </td>
							            `;
												}
							        }
							        else {
							            row += ' <td data-required="'+ ( $(this).attr('data-required') != 'false' ? 'true' : 'false' ) +'"> <input type = "text" class = "form-control" name="'+$(this).attr('data-name')+'"> </td>';
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
							    var td = $(this).closest('td');

								if (!$(this).val() && td.attr('data-required') != 'false') {
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
                                    url: 'action/postgis.php',
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
														 if(name == 'accgrps') {
	        									    $(this).html(`
	            									    <select name="`+name+`" multiple>
	    							                        <?PHP foreach($acc_grps as $k => $v) { ?>
	    							                        <option value="<?=$k?>"><?=$v?></option>
	    							                        <?PHP } ?>
	    							                    </select>
								                    `);
															}else if(name == 'basemap') {
																$(this).html(`
	            									    <select name="`+name+`">
	    							                        <?PHP foreach($bms as $k => $v) { ?>
	    							                        <option value="<?=$k?>"><?=$v?></option>
	    							                        <?PHP } ?>
	    							                    </select>
								                    `);
															}

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
                                    url: 'action/postgis.php',
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
    <div id="main-wrapper" data-layout="vertical" data-navbarbg="skin5" data-sidebartype="full"
        data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">

        <?php define('MENU_SEL', 'postgis.php');
					include("incl/topbar.php");
					include("incl/sidebar.php");
				?>
        <div class="page-wrapper">
            <div class="page-breadcrumb" style="padding-left:30px; padding-right: 30px; padding-top:0px; padding-bottom: 0px">
                <div class="row align-items-center">
                    <div class="col-6">
                        <nav aria-label="breadcrumb">

                          </nav>
                        <h1 class="mb-0 fw-bold">PostGIS</h1>
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
            <div class="container-fluid">
				<div class="table-responsive">

				<table class="table table-bordered">
					<thead>
						<tr>
							<th data-name="id" data-editable='false'>ID</th>
							<th data-name="name">Name</th>
							<th data-name="description">Description</th>
                                                        <th data-name="host">Host</th>
							<th data-name="database">Database</th>
							<th data-name="schema">Schema</th>
							<th data-name="username">Username</th>
							<th data-name="password">Password</th>
							<th data-name="geom">Geom</th>
							<th data-name="metric">Metric</th>
							<th data-name="tbl">Table</th>
							<th data-name="basemap" data-type="select">Basemap</th>
							<th data-name="lat">Lat</th>
							<th data-name="lon">Lon</th>
							<th data-name="zoom">Zoom</th>
						    <th data-name="pgtileurl" data-required="false">pgtileurl</th>
							<th data-name="accgrps" data-type="select">Access Groups</th>
							<th data-editable='false' data-action='true'>Actions</th>
						</tr>
					</thead>

					<tbody> <?php while($row = pg_fetch_object($pg_rows)): ?> <tr data-id="<?=$row->id?>" align="left">
							<td><?=$row->id?></td>
							<td><?=$row->name?></td>
							<td><?=$row->description?></td>
							<td><?=$row->host?></td>
							<td><?=$row->database?></td>
							<td><?=$row->schema?></td>
							<td><?=$row->username?></td>
							<td input type='password' value="<?=$row->password?>" style="word-break:break-all;"><?=$row->password?></td>
							<td><?=$row->geom?></td>
							<td><?=$row->metric?></td>
							<td><?=$row->tbl?></td>
							<td data-type="select" data-value="<?=$row->basemap?>"><?=$bms[$row->basemap]?></td>
							<td><?=$row->lat?></td>
							<td><?=$row->lon?></td>
							<td><?=$row->zoom?></td>
							<td style="word-break:break-all;" data-required="false"><?=$row->pgtileurl?></td>
							<?php
								$pg_acc_grps = $pg_obj->getGrpAccessGroups($row->id);
								$pg_acc_ids  = implode(',', array_keys($pg_acc_grps));
								$pg_acc_names = implode(',', array_values($pg_acc_grps));
							?>
							<td data-type="select" data-value="<?=$pg_acc_ids?>"><?=$pg_acc_names?></td>
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
		</div>



                <!-- ============================================================== -->
                <!-- Start Page Content -->
                <!-- ============================================================== -->
                <div class="row">

                  <p>&nbsp;</p>
                    <div class="col-6" style="width: 50%!important">
						<p>&nbsp;</p>
						<div id = "repThumbnail" class = "alert alert-success">
   <a href = "#" class = "close" data-dismiss = "alert">&times;</a>
   <strong>Note:</strong> You can set the thumbnail for a report by adding reportid.png to the assets/maps folder.
</div>




<div class="col-8" style="width: 90%!important">
						<p>&nbsp;</p>
						<div id = "repVrt" class = "alert alert-info">
   <a href = "#" class = "close" data-dismiss = "alert">&times;</a>
   <strong>Note:</strong> You can include a custom vrt file by placing it in the /vrt folder as reportid.vrt.  <br><strong>Note:</strong> You can include custom commands by placing it in the /vrt folder as reportid.txt.
</div>

 <div class="col-6">
                        <a href="vrt.php" class="btn btn-info"><span class="mdi mdi-file-pdf"></span>
&nbsp;Generate vrt Template</a>

                    </div>



<script type = "text/javascript">
   $(function(){
      $(".close").click(function(){
         $("#repThumbnail").alert();
      });
   });
</script>

<script type = "text/javascript">
   $(function(){
      $(".close").click(function(){
         $("#repVrt").alert();
      });
   });
</script>


</div>

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
