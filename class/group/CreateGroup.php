<?php
namespace org\opencomb\coresystem\group ;

use org\jecat\framework\message\Message;

use org\jecat\framework\mvc\model\db\Category;
use org\jecat\framework\mvc\view\DataExchanger;
use org\opencomb\coresystem\mvc\controller\ControlPanel;

class CreateGroup extends ControlPanel
{
	public function createBeanConfig()
	{		
		return array(
				
				'model:groupTree' => array( 'config' => 'model/group' ) ,
				'model:newGroup' => array( 'config' => 'model/group' ) ,
				
				'view:groupForm' => array(
					'class' => 'form' ,
					'template' => 'GroupForm.html' ,
					'model' => 'newGroup' ,
						
					'widget:groupName' => array( 'config'=>'widget/groupName' ) ,
					'widget:parentGroup' => array(
						'class' => 'select' ,
						'title' => '所属分组' ,
						'options' => array(
							array('顶级分组',0)
						)
					) ,
				) ,
		) ;
	}
	
	public function process()
	{		
		// 权限认证
		$aId = $this->requireLogined() ;
		
		
		// 保存新分组
		if( $this->viewGroupForm->isSubmit($this->params) )
		{do{
			$this->viewGroupForm->loadWidgets($this->params) ;
			
			if( !$this->viewGroupForm->verifyWidgets() )
			{
				break ;
			}
			
			$this->viewGroupForm->exchangeData(DataExchanger::WIDGET_TO_MODEL) ;
			
			$this->modelNewGroup->save() ;
			
			if($nGrpRgtFoot=$this->viewGroupForm->parentGroup->value())
			{
				$this->modelNewGroup->insertCategoryToPoint($nGrpRgtFoot) ;
			}
			else
			{
				$this->modelNewGroup->insertBefore(Category::end) ;
			}
			
			$this->viewGroupForm->createMessage(Message::success,"分组%s已经保存",$this->modelNewGroup->name) ;
			
			$this->viewGroupForm->hideForm() ;
			
		} while(0) ;}

		
		// 加载已有分组菜单
		$aGroupIter = Category::loadTotalCategory($this->modelNewGroup->prototype()) ;
		Category::buildTree($aGroupIter) ;
		foreach($aGroupIter as $aCategory)
		{
			$sText = str_repeat('--',$aCategory->depth()) . $aCategory->name ;
			$this->viewGroupForm->parentGroup->addOption($sText,$aCategory->rgt) ;
		}
	}
}

?>