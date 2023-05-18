<?php
	define('MENU_ROWS',
		array(
			array('index.php', 					'view-dashboard',						'Dashboard'),
			array('reports.php', 				'file-chart',								'Reports'),
			#array('input.php', 					'comment-account-outline', 	'Report Context'),
			array('show_input.php', 		'comment-account-outline', 	'Report Contexts'),
			array('groups.php', 				'group',										'Report Groups'),
			array('parameters.php', 		'filter', 									'Parameters'),
			array('users.php', 					'account-settings-variant', 'Users'),
			array('access_groups.php',	'account-multiple', 				'User Groups'),
			#array('registration.php',		'account-plus',							'Add New User'),
			#array('basemaps.php',				'map', 											'Maps'),
			#array('tiles.php',					'map', 											'Tiles'),
			array('map_step_1.php',			'map', 											'Map Report'),
			#array('featurserv.php',			'map', 											'Feature Serve'),
			#array('postgis.php',				'map', 											'PostGIS'),
			#array('vrt.php',						'map', 											'VRT'),
			array('../index.php',				'exit-to-app',							'Front End'),
			array('../logout.php',			'logout',										'Lot Out')
		)
	);
?>

<aside class="left-sidebar" data-sidebarbg="skin6">
	<!-- Sidebar scroll-->
	<div class="scroll-sidebar">
		<!-- Sidebar navigation-->
		<nav class="sidebar-nav">
			<ul id="sidebarnav">
				<?php
					foreach(MENU_ROWS as $row){
				?>
					<li class="sidebar-item" <?php if(MENU_SEL == $row[0]){ ?> selected <?php } ?> >
					<a class="sidebar-link waves-effect waves-dark sidebar-link" href="<?=$row[0]?>" aria-expanded="false">
						<i class="mdi mdi-<?=$row[1]?>"></i><span	class="hide-menu"><?=$row[2]?></span>
					</a>
				</li>
				<?php	}
				?>
			</ul>
		</nav>
		<!-- End Sidebar navigation -->
	</div>
	<!-- End Sidebar scroll-->
</aside>
