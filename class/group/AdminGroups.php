<?php
namespace org\opencomb\coresystem\group ;

use org\jecat\framework\system\Request;

use org\jecat\framework\message\Message;

use org\jecat\framework\system\Application;

use org\jecat\framework\mvc\controller\Relocater;

use org\jecat\framework\mvc\model\db\Category;
use org\opencomb\coresystem\mvc\controller\ControlPanel;

class AdminGroups extends ControlPanel
{
	public function createBeanConfig()
	{		
		return array(
				
				'model:groupTree' => array( 'config' => 'model/group' ) ,
				
				'view:groupList' => array(
					'class' => 'form' ,
					'template' => 'GroupList.html' ,
					'model' => 'groupTree' ,
				) ,
		) ;
	}
	
	
	public function process()
	{
		if(!empty($this->params['delete_category']))
		{
			$aDelCategory = $this->groupTree->prototype()->createModel() ;
			if( !$aDelCategory->load($this->params['delete_category']) )
			{
				$this->groupList->createMessage(Message::error,"id为 %d 的用户组不存在",$this->params['delete_category']) ;
			}
			else
			{
				// 清理权限
				// todo ...
				
				// 解除用户关系
				// todo ...
				
				$aDelCategory->delete() ;
			
				$this->location(
						Request::singleton()->uri('delete_category'), "用户组%s 已经删除", $aDelCategory->name
				) ;
			}
		}
		
		
		$aGroupIter = Category::loadTotalCategory($this->groupTree->prototype()) ;
		Category::buildTree($aGroupIter,$this->groupTree) ;
	}
}

?>