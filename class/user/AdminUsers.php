<?php
namespace org\opencomb\coresystem\user ;

use org\opencomb\coresystem\mvc\controller\ControlPanel;

class AdminUsers extends ControlPanel
{

	public function createBeanConfig()
	{
		return array(

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
		$aId = $this->requireLogined() ;
		
		$this->users->load() ;
	}
}

?>