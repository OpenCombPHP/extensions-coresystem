<?php
namespace org\opencomb\coresystem\group ;

use jc\mvc\view\DataExchanger;

use org\opencomb\coresystem\mvc\controller\Controller;

class CreateGroup extends Controller
{
	public function createBeanConfig()
	{
		return array(
				
				'model:group' => array(
					'class' => 'category' ,
					'orm' => array(
						'table' => 'group' ,
					) ,		
				) ,

				'view:groupForm' => array(
					'class' => 'form' ,
					'template' => 'GroupForm.html' ,
					'model' => 'group' ,
					'widget:groupName' => array(
						'class' => 'text' ,
						'title' => '名称' ,
						'verifier:length' => array('min'=>3,'max'=>60) ,	
						'exchange' => 'name' ,
					)
				) ,
		) ;
	}
	
	public function process()
	{
		if( $this->viewGroupForm->isSubmit($this->aParams) )
		{do{
			$this->viewGroupForm->loadWidgets($this->aParams) ;
			
			if( !$this->viewGroupForm->verifyWidgets() )
			{
				break ;
			}
			
			$this->viewGroupForm->exchangeData(DataExchanger::WIDGET_TO_MODEL) ;
			
			$this->modelGroup->insertCategory() ;
			
		} while(0) ;}		
	}
}

?>