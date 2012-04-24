<?php
namespace org\opencomb\coresystem\mvc\controller ;

class PermissionDenied extends Controller
{
	public function createBeanConfig()
	{
		return array(
			'view:main' => array(
				'template'=>'coresystem:PermissionDenied.html' ,
				'vars' => array( 
					'message' => '权限被拒绝！'
				) ,
			)
		) ;
	}
	
	public function process()
	{}
	
}
