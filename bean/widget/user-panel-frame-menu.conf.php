<?php 
return array(
	'class' => 'menu' ,
	'id' => 'mainMenu' ,
	'direction' => 'h' ,
	'items' => array(
		'system' => array(
			'title' => '系统' ,
			'link' => '?c=org.opencomb.coresystem.system.DebugStatSetting' ,
			'query' => 'c=org.opencomb.coresystem.system.DebugStatSetting' ,
			'menu' => array(
				'items' => array(
					'platform-manage' => array(
						'title'=>'平台维护' ,
						'link' => '?c=org.opencomb.coresystem.system.ExtensionSetup' ,
						'menu' => array(
							'items' => array(
									'extension-setup' => array(
											'title'=>'安装扩展' ,
											'link' => '?c=org.opencomb.coresystem.system.ExtensionSetup' ,
											'query' => 'c=org.opencomb.coresystem.system.ExtensionSetup' ,
									) ,
									'extension-manage' => array(
											'title'=>'扩展管理' ,
											'link' => '?c=org.opencomb.coresystem.system.ExtensionManager' ,
											'query' => 'c=org.opencomb.coresystem.system.ExtensionManager' ,
									) ,
									'platform-upgrade' => array(
											'title'=>'平台升级' ,
											'link' => '?c=org.opencomb.coresystem.system.PlatformUpgrade' ,
											'query' => 'c=org.opencomb.coresystem.system.PlatformUpgrade' ,
											'menu' => array(
												'items' => array(
													'createPatch' => array(
														'title' => '创建补丁',
														'link' => '?c=org.opencomb.coresystem.system.patch.CreatePatch',
														'query' => 'c=org.opencomb.coresystem.system.patch.CreatePatch',
													),
													'installPatch' => array(
														'title' => '安装补丁',
														'link' => '?c=org.opencomb.coresystem.system.patch.InstallPatch',
														'query' => 'c=org.opencomb.coresystem.system.patch.InstallPatch',
													),
												),
											),
									) ,
									'platform-rebuiild' => array(
											'title'=>'系统重建' ,
											'link' => '?c=org.opencomb.coresystem.system.RebuildPlatform' ,
											'query' => 'c=org.opencomb.coresystem.system.RebuildPlatform' ,
									) ,
									
									
									'debug-stat' => array(
											'title'=>'调式状态' ,
											'link' => '?c=org.opencomb.coresystem.system.DebugStatSetting' ,
											'query' => 'c=org.opencomb.coresystem.system.DebugStatSetting' ,									
									) ,
							) ,								
						)
							
					) ,
				) ,
			) ,
		) ,
		'user' => array(
			'title' => '用户' ,
			'link'=>'?c=org.opencomb.coresystem.user.AdminUsers' ,
			'menu' => array(
				'items' => array(
					'user-manager' => array(
						'title'=>'用户管理' ,
						'link'=>'?c=org.opencomb.coresystem.user.AdminUsers' ,
						'query' => array(
								'c=org.opencomb.coresystem.user.AdminUsers' ,
								'c=org.opencomb.coresystem.auth.PurviewSetting&type=user' ,
								'c=org.opencomb.coresystem.auth.PurviewTester' ,
								'c=org.opencomb.coresystem.user.UserGroupsSetting' ,
						) ,
					) ,
						
					'group' => array(
						'title'=>'用户组' ,
						'menu' => array(
							'items' => array(
								'create-group' => array(
										'title'=>'新建用户组'
										, 'link'=>'?c=org.opencomb.coresystem.group.CreateGroup'
										, 'query'=>'c=org.opencomb.coresystem.group.CreateGroup'
								) ,
								'group-manager' => array(
										'title'=>'管理用户组'
										,'link'=>'?c=org.opencomb.coresystem.group.AdminGroups'
										,'query'=> array(
												'c=org.opencomb.coresystem.group.AdminGroups' ,
												'c=org.opencomb.coresystem.auth.PurviewSetting&type=group' ,
										)
								) ,
							)
						)
					) ,
				)
			)
		) ,
	) ,
) ;
