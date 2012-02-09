<?php 
return array(
	'class' => 'model' ,
	'orm' => array(
		'table' => 'user' ,
		'hasOne:info' => array(
			'table' => 'userinfo' ,
		) ,
	) ,
) ;