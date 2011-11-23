<?php 
return array(
	'class' => 'menu' ,
	'id' => 'mainMenu' ,
	'direction' => 'h' ,
	'items' => array(
		array(
			'title' => '系统' ,
		) ,
		array(
			'title' => '用户' ,
			'menu' => array(
				'independence' => true ,
				'items' => array(
					array(
						'title'=>'用户管理' ,
						'link'=>'' ,
					) ,
						
					array(
						'title'=>'用户组' ,
						'menu' => array(
							'independence' => true ,
							'items' => array(
								array('title'=>'新建用户组','link'=>'?c=org.opencomb.coresystem.group.CreateGroup') ,
								array('title'=>'管理用户组','link'=>'?c=org.opencomb.coresystem.group.AdminGroups') ,
							)
						)
					) ,
				)
			)
		) ,
	) ,
) ;