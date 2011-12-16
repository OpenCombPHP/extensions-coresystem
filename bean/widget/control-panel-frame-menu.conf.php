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
					'debug-stat' => array(
						'title'=>'调式状态' ,
						'link' => '?c=org.opencomb.coresystem.system.DebugStatSetting' ,
						'query' => 'c=org.opencomb.coresystem.system.DebugStatSetting' ,
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