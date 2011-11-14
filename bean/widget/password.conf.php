<?php 
return array(
	'id' => 'password' ,
	'class' => 'text' ,
	'title' => '密码' ,
	'type' => 'password' ,

	'verifier:length' => array(
			'min'=>6 ,
			'max'=>30 ,
			'message'=>'用户名必须不小于6字节，不大于30' ,
	) ,
) ;