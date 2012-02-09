<?php
namespace org\opencomb\coresystem\system\patch ;

use org\opencomb\coresystem\mvc\controller\ControlPanel;
use org\jecat\framework\message\Message ;

class CreatePatch extends ControlPanel
{
	public function createBeanConfig(){
		return array(
			'view:view' => array(
				'template' => 'system/CreatePatch.html' ,
			) ,
		) ;
	}
	
	public function process(){
		$this->doActions() ;
		
		$arrItemName = array(
			Patch::ITEM_Platform ,
			Patch::ITEM_Framework ,
		);
		
		$arrItemList = array();
		
		foreach($arrItemName as $sName){
			$arrItemList[$sName] = new Patch( $sName );
		}
		
		$this->view->variables()->set('arrItemList',$arrItemList);
	}
	
	public function actionCreate(){
		// input 
		$sName = $this->params['name'];
		$sFrom = $this->params['from'];
		$sTo = $this->params['to'];
		
		$aPatch = new Patch($sName);
		$sFilePath = $aPatch->create($sFrom,$sTo);
		
		$this->view->createMessage(Message::success,'创建补丁成功：%s',$sFilePath);
	}
}
