<?php
    session_start();

    require_once('class/database.php');
    require_once('class/report.php');
    require_once('class/user.php');
		require_once('class/access_groups.php');

		$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
		$dbconn = $database->getConn();

    $db_obj = new Report_Class($dbconn);
    $rows = $db_obj->getRows();

    $obj = new user_Class($dbconn);
    $users_obj = $obj->getRows();
    $users = [];
    while($us = pg_fetch_object($users_obj)) {
        $users[$us->id] = $us->name;
    }

		$acc_obj = new access_group_Class($dbconn);
		$acc_grps = $acc_obj->getAccessGroupsArr();

    $yesNo = [0 => 'No', 1 => 'Yes'];
		define('REPFORMATS', array('html', 'pdf', 'yes'));


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
	        var deleted_ids = [];
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
							            }
							            else if($(this).attr('data-name') == 'is_grouped') {
    							            row += `
    							                <td data-type="select" data-value="0">
    							                    <select name="`+$(this).attr('data-name')+`">
    							                        <?PHP foreach($yesNo as $k => $v) { ?>
    							                        `+
    							                            `<option value="<?=$k?>"><?=$v?></option>`
    							                        +`
    							                        <?PHP } ?>
    							                    </select>
    							                </td>
    							            `;
							            }else if($(this).attr('data-name') == 'download_only') {
    							            row += `
    							                <td data-type="select" data-value="0">
    							                    <select name="`+$(this).attr('data-name')+`">
    							                        <?PHP foreach(REPFORMATS as $v) { ?>
    							                        `+
    							                            `<option value="<?=$v?>"><?=$v?></option>`
    							                        +`
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
                                    url: 'action/ajax.php',
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
    								        var id = $(this).closest('tr').attr('data-id');

        									if($(this).closest('table').find('thead tr th').eq(k).attr('data-type') == 'select') {
        									    if(name == 'accgrps') {

            									    $(this).html(`
                									    <select name="`+name+`" multiple>
        							                        <?PHP foreach($acc_grps as $k => $v) { ?>
        							                        <option value="<?=$k?>"><?=$v?></option>
        							                        <?PHP } ?>
        							                    </select>
    							                    `);

        									    }
        									    else if(name == 'is_grouped') {
        									        $(this).html(`
                									    <select name="`+name+`">
        							                        <?PHP foreach($yesNo as $k => $v) { ?>
        							                        `+
        							                            `<option value="<?=$k?>"><?=$v?></option>`
        							                        +`
        							                        <?PHP } ?>
        							                    </select>
    							                    `);
        									    }else if(name == 'download_only') {
        									        $(this).html(`
                									    <select name="`+name+`">
        							                        <?PHP foreach(REPFORMATS as $v) { ?>
        							                        `+
        							                            `<option value="<?=$v?>"><?=$v?></option>`
        							                        +`
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
							    var id = obj.parents("tr").attr('data-id');
							    var data = {'delete': true, 'id': id}

							    $.ajax({
                                    type: "POST",
                                    url: 'action/ajax.php',
                                    data: data,
                                    dataType:"json",
                                    success: function(response){
                                        if(response.success) { // means, new record is added
                                            obj.parents("tr").remove();

                                            deleted_ids.push(id);
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
        <!-- ============================================================== -->
        <!-- Topbar header - style you can find in pages.scss -->
        <!-- ============================================================== -->
        <header class="topbar" data-navbarbg="skin6">
            <nav class="navbar top-navbar navbar-expand-md navbar-light">
                <div class="navbar-header" data-logobg="skin6">
                    <!-- ============================================================== -->
                    <!-- Logo -->
                    <!-- ============================================================== -->
                    <a class="navbar-brand" href="index.php">
                        <!-- Logo icon -->
                        <b class="logo-icon">
                            <!--You can put here icon as well // <i class="wi wi-sunset"></i> //-->
                            <!-- Dark Logo icon -->
                            <img src="assets/images/cited-logo.png" alt="homepage" class="dark-logo" />
                            <!-- Light Logo icon -->
                            <img src="assets/images/cited-logo.png" alt="homepage" class="light-logo" />
                        </b>
                        <!--End Logo icon -->
                        <!-- Logo text -->
                        <span class="logo-text">
                            <!-- dark Logo text -->

                        </span>
                    </a>
                    <!-- ============================================================== -->
                    <!-- End Logo -->
                    <!-- ============================================================== -->
                    <!-- This is for the sidebar toggle which is visible on mobile only -->
                    <a class="nav-toggler waves-effect waves-light d-block d-md-none" href="javascript:void(0)"><i
                            class="ti-menu ti-close"></i></a>
                </div>
                <!-- ============================================================== -->
                <!-- End Logo -->
                <!-- ============================================================== -->

            </nav>
        </header>
        <!-- ============================================================== -->
        <!-- End Topbar header -->
        <!-- ============================================================== -->
        <?php define('MENU_SEL', 'reports.php'); include("incl/sidebar.php"); ?>
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
                        <h1 class="mb-0 fw-bold">Reports</h1>
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
							<th data-name="url">url</th>
							<th data-name="repname">Report</th>
							<th data-name="datasource">Data Souce</th>
							<th data-name="download_only" data-type="select">Format</th>
							<th data-name="outname">File Name</th>
							<th data-name="name">Name</th>
							<th data-name="description">Description</th>
							<th data-name="accgrps" data-type="select">Access groups</th>
							<th data-name="is_grouped" data-type="select">Grouped Report</th>
							<th data-editable='false' data-action='true'>Actions</th>
						</tr>
					</thead>

					<tbody> <?php while($row = pg_fetch_object($rows)): ?> <tr data-id="<?=$row->id?>" align="left">
							<td><?=$row->id?></td>
							<td><?= $row->url?></td>
							<td><?= $row->repname?></td>
							<td><?= $row->datasource?></td>
							<td data-type="select" data-value="<?=$row->download_only?>"><?=$row->download_only?></td>
							<td><?= $row->outname?></td>
							<td><?= $row->name?></td>
							<td><?= $row->description?></td>
							<?php
								$rep_acc_grps = $db_obj->getRepAccessGroups($row->id);
								$rep_acc_ids  = implode(',', array_keys($rep_acc_grps));
								$rep_acc_names = implode(',', array_values($rep_acc_grps));
							?>
							<td data-type="select" data-value="<?=$rep_acc_ids?>"><?=$rep_acc_names?></td>
							<td data-type="select" data-value="<?=intval($row->is_grouped)?>"><?=$yesNo[intval($row->is_grouped)]?></td>


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



<div class="row">
            <div class="col-md-12">
              <div class="card">
                <div class="card-body">
                  <h4>Introduction</h4>
                  <hr />
                  <p>
                    Flexy Admin Bootstrap Dashboard admin is a popular open
                    source WebApp template for admin dashboards and control
                    panels. Flexy is fully responsive HTML template, which is
                    based on the CSS framework
                    <span class="text-danger">Bootstrap 5</span>. It utilizes
                    all of the Bootstrap components in its design and re-styles
                    many commonly used plugins to create a consistent design
                    that can be used as a user interface for backend
                    applications. Flexy is based on a modular design, which
                    allows it to be easily customized and built upon. This
                    documentation will guide you through installing the template
                    and exploring the various components that are bundled with
                    the template.
                  </p>
                  <p>
                    We put a lot of love and effort to make Flexy Admin
                    Bootstrap Dashboard admin a useful template for everyone and
                    now It comes with 9 unique demos. We are keen to release
                    continuous long term updates and lots of new features will
                    be coming soon in the future releases. Once you purchased
                    Flexy Admin Bootstrap Dashboard admin, you will be entitled
                    to free download of all future updates for the same license.
                  </p>
                  <div class="p-4 border shadow-sm rounded">
                    <h4>Support</h4>
                    <hr />
                    <div class="row">
                      <div class="col-md-6 border-right">
                        <ul class="list-style-none">
                          <li class="my-2 border-bottom pb-3">
                            <span class="font-weight-medium text-dark"
                              ><i class="icon-note me-2 text-success"></i>
                              Includes:</span
                            >
                          </li>
                          <li class="my-3">
                            <span
                              ><i
                                class="ri-edit-line fs-6 me-2 text-success"
                              ></i>
                              Answering your questions or problems regarding the
                              template.</span
                            >
                          </li>
                          <li class="my-3">
                            <span
                              ><i
                                class="ri-edit-line fs-6 me-2 text-success"
                              ></i>
                              Giving solution to the Bugs reported.</span
                            >
                          </li>
                        </ul>
                      </div>
                      <div class="col-md-6">
                        <ul class="list-style-none">
                          <li class="my-2 border-bottom pb-3">
                            <span class="font-weight-medium text-dark"
                              ><i class="icon-note me-2 text-danger"></i> Does
                              Not Includes:</span
                            >
                          </li>
                          <li class="my-3">
                            <span
                              ><i
                                class="ri-edit-line fs-6 me-2 text-danger"
                              ></i>
                              Custmaization Work</span
                            >
                          </li>
                          <li class="my-3">
                            <span
                              ><i
                                class="ri-edit-line fs-6 me-2 text-danger"
                              ></i>
                              Any Installation Work</span
                            >
                          </li>
                          <li class="my-3">
                            <span
                              ><i
                                class="ri-edit-line fs-6 me-2 text-danger"
                              ></i>
                              Support for any Third Party Plugins /
                              Software</span
                            >
                          </li>
                          <li class="my-3">
                            <span
                              ><i
                                class="ri-edit-line fs-6 me-2 text-danger"
                              ></i>
                              Support or Guide for How to integrate with any
                              technologies (like, PHP, .net, Java etc)</span
                            >
                          </li>
                          <li class="my-3">
                            <span
                              ><i
                                class="ri-edit-line fs-6 me-2 text-danger"
                              ></i>
                              Solve bug in your implemented template</span
                            >
                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>
                  <h4 class="card-title mt-5">Structure</h4>
                  <hr />
                  <p>
                    After purchasing our template extract the zip file and you
                    will see this structure.
                  </p>
                  <div class="bg-light p-3 rounded">
                    <ul class="list-style-none">
                      <li>
                        <h4 class="fw-bold">
                          Flexy Admin Bootstrap Dashboard admin
                        </h4>
                        <ul class="pl-3 pl-md-5">
                          <li class="py-2">
                            <h6 class="fw-bold">
                              <i
                                class="
                                  fas
                                  fa-folder
                                  me-2
                                  text-warning
                                  font-bold
                                "
                              ></i
                              >docs
                            </h6>
                          </li>
                          <li class="py-2">
                            <h6 class="fw-bold">
                              <i
                                class="
                                  fas
                                  fa-folder
                                  me-2
                                  text-warning
                                  font-bold
                                "
                              ></i
                              >landingpage
                            </h6>
                          </li>
                          <li class="py-2">
                            <h6 class="fw-bold">
                              <i
                                class="
                                  fas
                                  fa-folder
                                  me-2
                                  text-warning
                                  font-bold
                                "
                              ></i
                              >package
                            </h6>
                            <ul>
                              <li class="py-2">
                                <span class="font-weight-bold"
                                  ><i
                                    class="
                                      fas
                                      fa-folder
                                      me-2
                                      text-warning
                                      font-bold
                                    "
                                  ></i
                                  >dist</span
                                >
                                <ul>
                                  <li class="py-2">
                                    <span class="font-weight-bold"
                                      ><i
                                        class="
                                          fas
                                          fa-folder
                                          me-2
                                          text-warning
                                          font-bold
                                        "
                                      ></i
                                      >css</span
                                    >
                                    <ul>
                                      <li class="py-2">
                                        <span
                                          >all the compiled css is here</span
                                        >
                                      </li>
                                    </ul>
                                  </li>
                                  <li class="py-2">
                                    <span class="font-weight-bold"
                                      ><i
                                        class="
                                          fas
                                          fa-folder
                                          me-2
                                          text-warning
                                          font-bold
                                        "
                                      ></i
                                      >js</span
                                    >
                                    <ul>
                                      <li class="py-2">
                                        <span>all the js is here</span>
                                      </li>
                                    </ul>
                                  </li>
                                </ul>
                              </li>
                              <li class="py-2">
                                <span class="font-weight-bold"
                                  ><i
                                    class="
                                      fas
                                      fa-folder
                                      me-2
                                      text-warning
                                      font-bold
                                    "
                                  ></i
                                  >assets</span
                                >
                                <ul>
                                  <li class="py-2">
                                    <span class="font-weight-bold"
                                      ><i
                                        class="
                                          fas
                                          fa-folder
                                          me-2
                                          text-warning
                                          font-bold
                                        "
                                      ></i
                                      >extra-libs</span
                                    >
                                    <ul>
                                      <li class="py-2">
                                        <span
                                          >libs which are not available in
                                          npm</span
                                        >
                                      </li>
                                    </ul>
                                  </li>
                                  <li class="py-2">
                                    <span class="font-weight-bold"
                                      ><i
                                        class="
                                          fas
                                          fa-folder
                                          me-2
                                          text-warning
                                          font-bold
                                        "
                                      ></i
                                      >images</span
                                    >
                                    <ul>
                                      <li class="py-2">
                                        <span>used images</span>
                                      </li>
                                    </ul>
                                  </li>
                                  <li class="py-2">
                                    <span class="font-weight-bold"
                                      ><i
                                        class="
                                          fas
                                          fa-folder
                                          me-2
                                          text-warning
                                          font-bold
                                        "
                                      ></i
                                      >libs</span
                                    >
                                    <ul>
                                      <li class="py-2">
                                        <span
                                          >libs which are available in
                                          npm</span
                                        >
                                      </li>
                                    </ul>
                                  </li>
                                </ul>
                              </li>
                              <li class="py-2">
                                <span class="font-weight-bold">
                                  <i
                                    class="
                                      fas
                                      fa-folder
                                      me-2
                                      text-warning
                                      font-bold
                                    "
                                  ></i
                                  >scss</span
                                >
                              </li>
                              <li class="py-2">
                                <span>
                                  <i
                                    class="
                                      fas
                                      fa-folder
                                      me-2
                                      text-warning
                                      font-bold
                                    "
                                  ></i
                                  >html</span
                                >
                                <ul>
                                  <li class="py-2">
                                    <span class="fw-bold"
                                      ><i
                                        class="
                                          fas
                                          fa-folder
                                          me-2
                                          text-warning
                                          font-bold
                                        "
                                      ></i
                                      >dark</span
                                    >
                                    <ul>
                                      <li class="py-2">
                                        <span>.html files</span>
                                      </li>
                                    </ul>
                                  </li>
                                  <li class="py-2">
                                    <span class="fw-bold"
                                      ><i
                                        class="
                                          fas
                                          fa-folder
                                          me-2
                                          text-warning
                                          font-bold
                                        "
                                      ></i
                                      >horizontal</span
                                    >
                                    <ul>
                                      <li class="py-2">
                                        <span>.html files</span>
                                      </li>
                                    </ul>
                                  </li>
                                  <li class="py-2">
                                    <span class="fw-bold"
                                      ><i
                                        class="
                                          fas
                                          fa-folder
                                          me-2
                                          text-warning
                                          font-bold
                                        "
                                      ></i
                                      >horizontal-rtl</span
                                    >
                                    <ul>
                                      <li class="py-2">
                                        <span>.html files</span>
                                      </li>
                                    </ul>
                                  </li>
                                  <li class="py-2">
                                    <span class="fw-bold"
                                      ><i
                                        class="
                                          fas
                                          fa-folder
                                          me-2
                                          text-warning
                                          font-bold
                                        "
                                      ></i
                                      >main</span
                                    >
                                    <ul>
                                      <li class="py-2">
                                        <span>.html files</span>
                                      </li>
                                    </ul>
                                  </li>
                                  <li class="py-2">
                                    <span class="fw-bold">
                                      <i
                                        class="
                                          fas
                                          fa-folder
                                          me-2
                                          text-warning
                                          font-bold
                                        "
                                      ></i
                                      >rtl</span
                                    >
                                    <ul>
                                      <li class="py-2">
                                        <span>.html files</span>
                                      </li>
                                    </ul>
                                  </li>
                                  <li class="py-2">
                                    <span class="fw-bold"
                                      ><i
                                        class="
                                          fas
                                          fa-folder
                                          me-2
                                          text-warning
                                          font-bold
                                        "
                                      ></i
                                      >minisidebar</span
                                    >
                                    <ul>
                                      <li class="py-2">
                                        <span>.html files</span>
                                      </li>
                                    </ul>
                                  </li>
                                  <li class="py-2">
                                    <span class="fw-bold"
                                      ><i
                                        class="
                                          fas
                                          fa-folder
                                          me-2
                                          text-warning
                                          font-bold
                                        "
                                      ></i
                                      >overlay</span
                                    >
                                    <ul>
                                      <li class="py-2">
                                        <span>.html files</span>
                                      </li>
                                    </ul>
                                  </li>
                                  <li class="py-2">
                                    <span class="fw-bold"
                                      ><i
                                        class="
                                          fas
                                          fa-folder
                                          me-2
                                          text-warning
                                          font-bold
                                        "
                                      ></i
                                      >stylish</span
                                    >
                                    <ul>
                                      <li class="py-2">
                                        <span>.html files</span>
                                      </li>
                                    </ul>
                                  </li>
                                </ul>
                              </li>
                              <li class="py-2">
                                <span class="fw-bold"
                                  ><i
                                    class="
                                      fas
                                      fa-dot-circle
                                      me-2
                                      text-warning
                                      font-bold
                                      fs-2
                                    "
                                  ></i
                                  >gulpfile.js</span
                                >
                              </li>
                              <li class="py-2">
                                <span class="fw-bold"
                                  ><i
                                    class="
                                      fas
                                      fa-dot-circle
                                      me-2
                                      text-warning
                                      font-bold
                                      fs-2
                                    "
                                  ></i
                                  >package.json</span
                                >
                              </li>
                              <li class="py-2">
                                <span class="fw-bold"
                                  ><i
                                    class="
                                      fas
                                      fa-dot-circle
                                      me-2
                                      text-warning
                                      font-bold
                                      fs-2
                                    "
                                  ></i
                                  >package-lock.json</span
                                >
                              </li>
                            </ul>
                          </li>
                        </ul>
                      </li>
                    </ul>
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
