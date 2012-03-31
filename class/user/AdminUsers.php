<?php
namespace org\opencomb\coresystem\user ;

use org\jecat\framework\auth\IdManager;
use org\jecat\framework\db\sql\StatementState;
use org\jecat\framework\mvc\model\db\Model;
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
			$aCriteria = Model::buildCriteria($this->users->prototype()) ;
			$aCriteria->where()->like('username', "{$this->params['username']}%") ;			
			$this->users->load($aCriteria) ;
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
		$this->users->load($this->params->get('uid'));
		
		if(!$this->users){
			return;
		}
		$aId = new Id($this->users);
		IdManager::singleton()->setCurrentId($aId);
		$this->location('/');
	}
}