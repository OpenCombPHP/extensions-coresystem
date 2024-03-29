<?php
namespace org\opencomb\coresystem\group ;

use org\opencomb\coresystem\auth\Id;
use org\jecat\framework\mvc\controller\Request;
use org\jecat\framework\message\Message;
use org\jecat\framework\mvc\model\db\Category;
use org\opencomb\coresystem\mvc\controller\ControlPanel;

class AdminGroups extends ControlPanel
{
	public function createBeanConfig()
	{		
		return array(
				'title'=>'用户组设置',
				'model:groupTree' => array( 'config' => 'model/group', 'list'=>true ) ,
				
				'view:groupList' => array(
					'class' => 'form' ,
					'template' => 'AdminGroups.html' ,
					'model' => 'groupTree' ,
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
		$this->checkPermissions('您没有使用这个功能的权限,无法继续浏览',array()) ;
		if(!empty($this->params['delete_category']))
		{
			$aDelModel = $this->groupTree->prototype()->createModel() ;
			if( !$aDelModel->load($this->params['delete_category']) )
			{
				$this->groupList->createMessage(Message::error,"id为 %d 的用户组不存在",$this->params['delete_category']) ;
			}
			else
			{
				$aDelCategory = new Category($aDelModel) ;
				
				// 清理权限
				// todo ...
				
				// 解除用户关系
				// todo ...
				
				$aDelCategory->delete() ;
			
				$this->location(
						Request::singleton()->uri('delete_category'), "用户组%s 已经删除", $aDelModel->name
				) ;
			}
		}

		$this->groupTree->load() ;
		Category::buildTree($this->groupTree) ;
	}
}
