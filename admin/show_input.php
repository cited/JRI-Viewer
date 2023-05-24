<?php
    session_start();

    require_once('class/database.php');
    require_once('class/input.php');

		$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
		$dbconn = $database->getConn();
		
    $obj = new input_Class($dbconn);
    $inputs = $obj->getRows();

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
	<script src="inputjs/jquery.min.js"></script>
	<script src="https://cdn.ckeditor.com/4.11.2/standard/ckeditor.js"></script>

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

								 }else{
							   	row += ' <td> <input type = "text" class = "form-control" name="'+$(this).attr('data-name')+'"> </td>';
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
							var input = $(this).parents("tr").find('input[type="text"], textarea');
							/*input.each(function() {
								if (($(this).attr('data-type') == 'textarea') && !$(this).html()) {
									$(this).addClass("error");
									empty = true;
								}else if(!$(this).val()){
									$(this).addClass("error");
									empty = true;
								} else {
									$(this).removeClass("error");
								}
							});*/

							$(this).parents("tr").find(".error").first().focus();
							if (!empty) {
								var data = {};
								data['save'] = 1;
								data['id'] = $(this).closest('tr').attr('data-id');

								input.each(function() {

									if($(this).closest('td').attr('data-type') == 'editor') {
										var val = $(this).html();
										$(this).parent("td").attr('data-value', val);
										$(this).parent("td").html($(this).val());
										data['input'] = $(this).find('[id="input"]').html();
									}else{
										$(this).parent("td").html($(this).val());
										data[$(this).attr('name')] = $(this).val();
									}
								});

								$.ajax({
                                    type: "POST",
                                    url: 'action/input.php',
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

													if($(this).closest('table').find('thead tr th').eq(k).attr('data-type') == 'editor') {

															$(this).html('<textarea name="input" id="input" rows="10" cols="80">' + $(this).html() + '</textarea>');
															CKEDITOR.replace( 'input' );

													}else {
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
                                    url: 'action/input.php',
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
        
        <header class="topbar" data-navbarbg="skin6">
            <nav class="navbar top-navbar navbar-expand-md navbar-light">
                <div class="navbar-header" data-logobg="skin6">
                    
                    <a class="navbar-brand" href="index.php">
                        <!-- Logo icon -->
                        <b class="logo-icon">
                            <!--You can put here icon as well // <i class="wi wi-sunset"></i> //-->
                            <!-- Dark Logo icon -->
                            <img src="assets/images/cited-logo.png" alt="homepage" class="dark-logo" />
                            <!-- Light Logo icon -->
                            <img src="assets/images/cited-logo.png" alt="homepage" class="light-logo" />
                        </b>
                       
                        <span class="logo-text">
                      

                        </span>
                    </a>
                   
                    <a class="nav-toggler waves-effect waves-light d-block d-md-none" href="javascript:void(0)"><i
                            class="ti-menu ti-close"></i></a>
                </div>
                

            </nav>
        </header>
       
        <?php define('MENU_SEL', 'show_input.php'); include("incl/sidebar.php"); ?>
       
        <div class="page-wrapper">
           
            <div class="page-breadcrumb" style="padding-left:30px; padding-right: 30px; padding-top:0px; padding-bottom: 0px">
                <div class="row align-items-center">
                    <div class="col-6">
                        <nav aria-label="breadcrumb">

                          </nav>
                        <h1 class="mb-0 fw-bold">Contexts</h1>
                    </div>
                    <div class="col-6">
                        <div class="text-end upgrade-btn">
                            <!--<a href="https://www.wrappixel.com/templates/flexy-bootstrap-admin-template/" class="btn btn-primary text-white"
                                target="_blank">Add New</a>-->

<a href="input.php" class="btn btn-info btn-md active" role="button" aria-pressed="true">Add Context</a>


                        </div>
                    </div>
                </div>
            </div>
           
            <div class="container-fluid">

				<table class="table table-bordered">
					<thead>
						<tr>
							<th data-name="id" data-editable='false'>ID</th>
							<th data-name="name">name</th>
							<th data-name="input" data-type="editor">input</th>
              <th data-name="report_id">report_id</th>
							<th data-editable='false' data-action='true'>Actions</th>


						</tr>
					</thead>

					<tbody> <?php while($user = pg_fetch_object($inputs)): ?> <tr data-id="<?=$user->id?>" align="left">
							<td><?=$user->id?> </td>
							<td><?=$user->name?> </td>
							<td data-type="editor" data-value="hohoboho"><?=$user->input?></td>
							<td><?=$user->report_id?> </td>

							<td>
								<a class="add" title="Add" data-toggle="tooltip">
									<i class="material-icons">&#xE03B;</i>
								</a>
								<a class="edit" title="Edit" href="edit_input.php?id=<?=$user->id?>">
									<i class="material-icons">&#xE254;</i>
								</a>
								<a class="delete" title="Delete" data-toggle="tooltip">
									<i class="material-icons">&#xE872;</i>
								</a>
							</td>
						</tr> <?php endwhile; ?> </tr>
					</tbody>
				</table>






               
                <div class="row">
                    <div class="col-12">

                    </div>
                    <div class="col-12">

                    </div>
                    <div class="col-12">

                    </div>
                    <div class="col-12">

                    </div>
                    <div class="col-12">

                    </div>
                    <div class="col-12">

                    </div>
                </div>
               
            </div>
           
            <footer class="footer text-center">

            </footer>
            
        </div>
        
    </div>
   
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
