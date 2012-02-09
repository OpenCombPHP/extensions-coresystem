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
				'widget:paginator' => array(    //分页器bean配置方法
					'class' => 'paginator' ,
				) 
			) ,
			'perms' => array(
					// 权限类型的许可
					'perm.purview'=>array(
							'name' => Id::PLATFORM_ADMIN,
					) ,
			) ,
		) ;
	}
	
	public function process()
	{		
		// 权限检查
		$this->checkPermissions('您没有使用这个功能的权限,无法继续浏览',array()) ;
				
		$this->users->load() ;
	}
}

?>