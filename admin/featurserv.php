<?php
    session_start();

    require_once('class/database.php');
    require_once('class/featurserv.php');
		require_once('class/basemaps.php');
    require_once('class/user.php');
		require_once('class/access_groups.php');

		$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
		$dbconn = $database->getConn();

    $feat_obj = new featurserv_Class($dbconn);
    $rows = $feat_obj->getRows();

    $obj = new user_Class($dbconn);
    $users_obj = $obj->getRows();
    $users = [];
    while($us = pg_fetch_object($users_obj)) {
        $users[$us->id] = $us->name;
    }

		$obj = new basemap_Class($dbconn);
    $bm_rows = $obj->getRows();

		$bms = array();
		while($row = pg_fetch_object($bm_rows)){
			$bms[$row->basemap_url] = $row->basemap_name;
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
                                    url: 'action/featurserv.php',
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
                                    url: 'action/featurserv.php',
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

        <?php define('MENU_SEL', 'featurserv.php');
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
                        <h1 class="mb-0 fw-bold">pg_featurserv</h1>
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
							<th data-name="name">Name</th>
                                                        <th data-name="description">Description</th>
							<th data-name="url">Url</th>
							<th data-name="collection">Collection</th>
							<th data-name="cql">Limit and CQL</th>
							<th data-name="lat">Lat</th>
							<th data-name="lon">Lon</th>
							<th data-name="zoom">Zoom</th>
							<th data-type="select" data-name="basemap">Basemap</th>
	            <th data-name="metric">Metric</th>
	            <th data-name="accgrps" data-type="select">Access Groups</th>
							<th data-editable='false' data-action='true'>Actions</th>
						</tr>
					</thead>

					<tbody> <?php while($row = pg_fetch_object($rows)): ?> <tr data-id="<?=$row->id?>" align="left">
							<td><?=$row->id?></td>
							<td><?=$row->name?></td>
							<td><?=$row->description?></td>
							<td><?=$row->url?></td>
							<td><?=$row->collection?></td>
							<td><?=$row->cql?></td>
							<td><?=$row->lat?></td>
							<td><?=$row->lon?></td>
							<td><?=$row->zoom?></td>
							<td data-type="select" data-value="<?=$row->basemap?>"><?=$bms[$row->basemap]?></td>
              <td><?=$row->metric?></td>
							<?php
								$wms_acc_grps = $feat_obj->getGrpAccessGroups($row->id);
								$wms_acc_ids  = implode(',', array_keys($wms_acc_grps));
								$wms_acc_names = implode(',', array_values($wms_acc_grps));
							?>
							<td data-type="select" data-value="<?=$wms_acc_ids?>"><?=$wms_acc_names?></td>
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


                    <div class="col-6">
						<p>&nbsp;</p>
						<div id = "repThumbnail" class = "alert alert-success">
   <a href = "#" class = "close" data-dismiss = "alert">&times;</a>
   <strong>Note:</strong> You can set the thumbnail for a report by adding reportid.png to the assets/maps folder.
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
