<?php
namespace org\opencomb\coresystem\group ;

use jc\message\Message;

use jc\system\Application;

use jc\mvc\controller\Relocater;

use jc\mvc\model\db\Category;
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
				// 解除用户关系
				// todo ...
				
				$aDelCategory->delete() ;
			
				$this->location(
						Application::singleton()->request()->uri('delete_category'), "用户组%s 已经删除", $aDelCategory->name
				) ;
			}
		}
		
		Category::loadTotalCategory($this->groupTree->prototype(),true,false,$this->groupTree) ;
	}
}

?>