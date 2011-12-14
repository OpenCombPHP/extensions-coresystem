<?php 
return array(
	'class' => 'menu' ,
	'id' => 'mainMenu' ,
	'direction' => 'h' ,
	'items' => array(
		'system' => array(
			'title' => '系统' ,
		) ,
		'user' => array(
			'title' => '用户' ,
			'link'=>'?c=org.opencomb.coresystem.user.AdminUsers' ,
			'menu' => array(
				'items' => array(
					'user-manager' => array(
						'title'=>'用户管理' ,
						'link'=>'?c=org.opencomb.coresystem.user.AdminUsers' ,
						'quote' => array(
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
										, 'quote'=>'c=org.opencomb.coresystem.group.CreateGroup'
								) ,
								'group-manager' => array(
										'title'=>'管理用户组'
										,'link'=>'?c=org.opencomb.coresystem.group.AdminGroups'
										,'quote'=> array(
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