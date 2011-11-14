<?php 
return array(
	'id' => 'username' ,
	'class' => 'text' ,
	'title' => '用户名' ,
	
	'verifier:length' => array(
			'min'=>5 ,
			'max'=>60 ,
			'message'=>'用户名必须不小于5字节，不大于60' ,
	) ,
) ;