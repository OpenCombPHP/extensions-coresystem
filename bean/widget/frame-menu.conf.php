<?php 
return array(
	'class' => 'menu' ,
	'id' => 'mainMenu' ,
	'direction' => 'h' ,
	'items' => array(
		array('title'=>'aa') ,
		array(
			'title'=>'bb' ,
			'menu' => array(
				'items' => array(array('title'=>'1111'),array('title'=>'2222')) ,
			)
		) ,
	) ,

) ;