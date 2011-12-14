<?php
namespace org\opencomb\coresystem\user ;

use org\opencomb\coresystem\auth\Id;

use org\opencomb\coresystem\mvc\controller\ControlPanel;

class AdminUsers extends ControlPanel
{

	public function createBeanConfig()
	{
		return array(
				
			'title' => '用户管理' ,
			'keywords' => '用户' ,
			'description' => '用户' ,
			
			// 模型
			'model:users' => array(
				'conf' => 'model/user' ,
				'list' => true ,
			) ,
			
			// 视图
			'view:adminUsers' => array(
				'template' => 'AdminUsers.html' ,
				'class' => 'form' ,
				'model' => 'users' ,
			) ,
		) ;
	}
	
	public function process()
	{		
		// 权限检查
		$this->requirePurview(Id::PLATFORM_ADMIN,'coresystem') ;
		
		$this->users->load() ;
	}
}

?>