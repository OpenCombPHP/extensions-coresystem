<?php 
return array(
	'class' => 'menu' ,
	'id' => 'mainMenu' ,
	'direction' => 'h' ,
	
	// items
	'item:system'=>array(
		'title' => '系统',
		'link' => '?c=org.opencomb.coresystem.system.DebugStatSetting' ,
		'query' => 'c=org.opencomb.coresystem.system.DebugStatSetting' ,
		
		// items
		'menu' => 1,
		'item:platform-manage' => array(
			'title'=>'平台维护' ,
			'link' => '?c=org.opencomb.coresystem.system.ExtensionSetupController' ,
			
			// items 
			'menu' => 1,
			'item:extension-setup' => array(
				'title'=>'安装扩展' ,
				'link' => '?c=org.opencomb.coresystem.system.ExtensionSetupController' ,
				'query' => 'c=org.opencomb.coresystem.system.ExtensionSetupController' ,
			) ,
			'item:extension-manage' => array(
				'title'=>'扩展管理' ,
				'link' => '?c=org.opencomb.coresystem.system.ExtensionManagerController' ,
				'query' => 'c=org.opencomb.coresystem.system.ExtensionManagerController' ,
			) ,
			'item:platform-upgrade' => array(
				'title'=>'平台升级' ,
				'link' => '?c=org.opencomb.coresystem.system.PlatformUpgrade' ,
				'query' => 'c=org.opencomb.coresystem.system.PlatformUpgrade' ,
				
				// items 
				'menu' => 1,
				'item:createPatch' => array(
					'title' => '创建补丁',
					'link' => '?c=org.opencomb.coresystem.system.patch.CreatePatch',
					'query' => 'c=org.opencomb.coresystem.system.patch.CreatePatch',
				),
				'item:installPatch' => array(
					'title' => '安装补丁',
					'link' => '?c=org.opencomb.coresystem.system.patch.InstallPatch',
					'query' => 'c=org.opencomb.coresystem.system.patch.InstallPatch',
				),
			) ,
			'item:platform-rebuiild' => array(
				'title'=>'系统重建' ,
				'link' => '?c=org.opencomb.coresystem.system.RebuildPlatform' ,
				'query' => 'c=org.opencomb.coresystem.system.RebuildPlatform' ,
			) ,
			'item:debug-stat' => array(
				'title'=>'调式状态' ,
				'link' => '?c=org.opencomb.coresystem.system.DebugStatSetting' ,
				'query' => 'c=org.opencomb.coresystem.system.DebugStatSetting' ,
			) ,
		) ,
	) ,
	'item:user' => array(
		'title' => '用户' ,
		'link'=>'?c=org.opencomb.coresystem.user.AdminUsers' ,
		'query'=>'c=org.opencomb.coresystem.user.AdminUsers' ,
		
		// items 
		'menu' => 1,
		'item:user-manager' => array(
			'title'=>'用户管理' ,
			'link'=>'?c=org.opencomb.coresystem.user.AdminUsers' ,
			'query' => array(
				'c=org.opencomb.coresystem.user.AdminUsers' ,
				'c=org.opencomb.coresystem.auth.PurviewSetting&type=user' ,
				'c=org.opencomb.coresystem.auth.PurviewTester' ,
				'c=org.opencomb.coresystem.user.UserGroupsSetting' ,
			) ,
		) ,
		'item:group' => array(
			'title'=>'用户组' ,
			
			// items
			'menu' => 1,
			'item:create-group' => array(
				'title'=>'新建用户组' ,
				'link'=>'?c=org.opencomb.coresystem.group.CreateGroup' ,
				'query'=>'c=org.opencomb.coresystem.group.CreateGroup' ,
			) ,
			'item:group-manager' => array(
				'title'=>'管理用户组' ,
				'link'=>'?c=org.opencomb.coresystem.group.AdminGroups' ,
				'query'=> array(
					'c=org.opencomb.coresystem.group.AdminGroups' ,
					'c=org.opencomb.coresystem.auth.PurviewSetting&type=group' ,
				) ,
			) ,
		),
	) ,
) ;
