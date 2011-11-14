<?php 
return array(
	'id' => 'username' ,
	'class' => 'text' ,
	'title' => '用户名' ,
	'exchange' => 'username' ,
	
	'verifier:length' => array(
			'min'=>4 ,
			'max'=>60 ,
			'message'=>'用户名必须不小于5字节，不大于60' ,
	) ,
) ;