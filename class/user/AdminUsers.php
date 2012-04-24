<?php
namespace org\opencomb\coresystem\user ;

use org\jecat\framework\auth\IdManager;
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
			//用于切换用户
			'model:user' => array(
				'class' => 'model' ,
				'orm' => array(
					'table' => 'user' ,
					'hasOne:info' => array(
						'table' => 'userinfo' ,
					) ,
				) ,
			) ,
			
			// 视图
			'view:adminUsers' => array(
				'template' => 'AdminUsers.html' ,
				'class' => 'form' ,
				'model' => 'users' ,
				'widget:paginator' => array(    //分页器bean配置方法
					'class' => 'paginator' ,
					'count' => 30 ,
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
				
		if( $this->params->has('username') )
		{
			$this->users->loadSql('username like @1',$this->params['username'].'%') ;
		}
		else
		{
			$this->users->load() ;
		}
		
		$this->doActions();
	}
	
	protected function actionSwichUser()
	{
		if(!$this->params->has('uid')){
			return ;
		}
		$this->user->load($this->params->get('uid'));
		
		if(!$this->user){
			return;
		}
		$aId = new Id($this->user);
		IdManager::singleton()->setCurrentId($aId);
		$this->location('/');
	}
}
