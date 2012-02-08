<?php
namespace org\opencomb\coresystem\system ;

use org\opencomb\coresystem\mvc\controller\ControlPanel;
use org\opencomb\platform\ext\ExtensionManager as ExtensionManagerOperator ;
use org\jecat\framework\message\Message ;
use org\opencomb\platform\ext\ExtensionSetup ;
use org\jecat\framework\lang\Exception;
use org\opencomb\platform\system\PlatformSerializer;
use org\opencomb\platform\Platform;
use org\opencomb\platform\ext\dependence\RequireItem ;

class ExtensionManager extends ControlPanel
{
	public function createBeanConfig()
	{
		return array(
			'view:view' => array(
				'template' => 'system/ExtensionManager.html' ,
			) ,
		) ;
	}

	public function process()
	{
		$this->doActions() ;
		
		$aExtMgr = ExtensionManagerOperator::singleton() ;
		
		// 已启用的扩展
		$arrEnabledExtensions = array() ;
		$arrPriority = array();
		foreach($aExtMgr->iterator() as $aExtension)
		{
			$arrEnabledExtensions[$aExtension->metainfo()->name()] = $aExtension ;
			$arrPriority[$aExtension->metainfo()->priority()][] = $aExtension->metainfo()->name();
		}
		
		// 禁用的扩展
		$arrDisabledExtensionMetainfos = array() ;
		foreach($aExtMgr->metainfoIterator() as $aExtensionMetainfo)
		{
			if(!isset($arrEnabledExtensions[$aExtensionMetainfo->name()]))
			{
				$arrDisabledExtensionMetainfos[] = $aExtensionMetainfo ;
			}
		}
		
		// dependence
		$arrDependenceBy = $this->getDependenceBy() ;
		$arrDependenceByRecursively = array() ;
		foreach($arrDependenceBy as $sExtName => $v){
			$arrDependenceByRecursively[$sExtName] = $this->getDependenceByRecursively($sExtName );
		}
		
		$this->view->variables()->set('arrPriority',$arrPriority);
		$this->view->variables()->set('arrEnabledExtensions',$arrEnabledExtensions) ;
		$this->view->variables()->set('arrDisabledExtensionMetainfos',$arrDisabledExtensionMetainfos) ;
		$this->view->variables()->set('arrDependenceBy',$arrDependenceByRecursively);
	}
	
	public function actionDisable(){
		$sExtName = $this->params['name'];
		
		try{
			$this->recursivelyDisable($sExtName);
			PlatformSerializer::singleton()->clearRestoreCache();
		}catch(Exception $e){
			$this->view->createMessage(Message::error,$e->getMessage(),$e->messageArgvs()) ;
		}
		$this->location('/?c=org.opencomb.coresystem.system.ExtensionManager',3);
	}
	
	public function actionUninstall(){
		$sExtName = $this->params['name'];
		$sCode = $this->params['code'];
		$sData = $this->params['data'];
		
		try{
			$this->recursivelyUninstall($sExtName , $sCode ,$sData);
		}catch(Exception $e){
			$this->view->createMessage(Message::error,$e->getMessage(),$e->messageArgvs()) ;
		}
		PlatformSerializer::singleton()->clearRestoreCache();
		$this->location('/?c=org.opencomb.coresystem.system.ExtensionManager',3);
	}
	
	public function actionChangePriority(){
		$sExtName = $this->params['name'];
		$nNewPriority = $this->params['priority'];
		
		$this->view->createMessage(Message::notice, '更改扩展优先级 ： %s , %d',array($sExtName,$nNewPriority));
		$aExtensionSetup = ExtensionSetup::singleton();
		try{
			$aExtensionSetup->changePriority($sExtName,$nNewPriority);
		}catch(Exception $e){
			$this->view->createMessage(Message::error,$e->getMessage(),$e->messageArgvs()) ;
		}
	}
	
	public function actionEnable(){
		$sExtName = $this->params['name'];
		try{
			$this->recursivelyEnable($sExtName);
			PlatformSerializer::singleton()->clearRestoreCache();
		}catch(Exception $e){
			$this->view->createMessage(Message::error,$e->getMessage(),$e->messageArgvs()) ;
		}
		$this->location('/?c=org.opencomb.coresystem.system.ExtensionManager',3);
	}
	
	private function getDependenceBy(){
		if( null === $this->arrDependenceBy ){
			$aExtMgr = ExtensionManagerOperator::singleton() ;
			$this->arrDependenceBy = array();
			foreach($aExtMgr->iterator() as $aExtension){
				$sExtName = $aExtension->metainfo()->name();
				foreach($aExtension->metainfo()->dependence()->iterator() as $aRequireItem){
					if( $aRequireItem->type() === RequireItem::TYPE_EXTENSION ){
						$sItemName = $aRequireItem->itemName();
						if( !isset($this->arrDependenceBy[$sItemName] ) ){
							$this->arrDependenceBy[$sItemName] = array();
						}
						$this->arrDependenceBy[$sItemName] [] = $sExtName ;
					}
				}
			}
		}
		return $this->arrDependenceBy ;
	}
	
	private function getDependenceByRecursively($sExtName , array $arrInArray = array()  ){
		$arrDepRec = array();
		$arrDependenceBy = $this->getDependenceBy() ;
		$arrInArray = array_merge($arrInArray,array($sExtName));
		if(isset($arrDependenceBy[$sExtName])){
			
			// 出现环
			$arrIntersect = array_intersect( $arrInArray , $arrDependenceBy[$sExtName] ) ;
			if(count($arrIntersect)>0){
				throw new Exception('扩展之间的依赖关系出现环 : 在扩展`%s`',$sExtName);
			}
			
			$arrDepRec = array_merge($arrDepRec,$arrDependenceBy[$sExtName]);
			foreach($arrDependenceBy[$sExtName] as $sByExtName){
				$arrInArray = array_merge($arrInArray,array($sByExtName));
				$arrD = $this->getDependenceByRecursively($sByExtName , $arrInArray) ;
				$arrDepRec = array_merge($arrDepRec,$arrD);
			}
		}
		return $arrDepRec ;
	}
	
	private function getDependence(){
		if( null === $this->arrDependence ){
			$aExtensionManager = ExtensionManagerOperator::singleton();
			$this->arrDependence = array();
			foreach($aExtensionManager->metainfoIterator() as $aExtensionMetainfo){
				$sExtName = $aExtensionMetainfo->name();
				$this->arrDependence[$sExtName] = array();
				foreach($aExtensionMetainfo->dependence()->iterator() as $aRequireItem){
					if( $aRequireItem->type() === RequireItem::TYPE_EXTENSION ){
						$sItemName = $aRequireItem->itemName();
						$this->arrDependence[$sExtName] [] = $sItemName ;
					}
				}
			}
		}
		return $this->arrDependence ;
	}
	
	private function recursivelyDisable($sExtName){
		$this->view->createMessage(Message::notice, '开始禁用扩展 ： %s',array($sExtName));
		$aExtensionSetup = ExtensionSetup::singleton();
		$arrDepBy = $this->getDependenceBy() ;
		if(isset($arrDepBy[$sExtName])){
			foreach($arrDepBy[$sExtName] as $sDepByExtName){
				$this->view->createMessage(Message::notice, '发现被扩展 `%s` 依赖',array($sDepByExtName));
				$this->recursivelyDisable($sDepByExtName);
			}
		}
		$aExtensionSetup->disable($sExtName);
		$this->view->createMessage(Message::success,'成功禁用扩展 ： %s',array($sExtName));
	}
	
	private function recursivelyEnable($sExtName){
		$this->view->createMessage(Message::notice, '开始启用扩展 ： %s',array($sExtName));
		$aExtensionSetup = ExtensionSetup::singleton();
		$arrDep = $this->getDependence() ;
		foreach($arrDep[$sExtName] as $sDepExtName){
			if(!$this->isExtensionEnabled($sDepExtName)){
				$this->view->createMessage(Message::notice, '发现依赖扩展 `%s`',array($sDepExtName));
				$this->recursivelyEnable($sDepExtName);
			}
		}
		$aExtensionSetup->enable($sExtName);
		$this->view->createMessage(Message::success,'成功启用扩展 ： %s',array($sExtName));
	}
	
	private function isExtensionEnabled($sExtName){
		$aExtMgr = ExtensionManagerOperator::singleton() ;
		
		foreach($aExtMgr->enableExtensionNameIterator() as $enableExtensionName){
			if($enableExtensionName == $sExtName){
				return true;
			}
		}
		return false;
	}
	
	private function recursivelyUninstall($sExtName,$sCode ,$sData){
		$this->view->createMessage(Message::notice, '卸载扩展 ： %s , 代码 ： %s , 数据 ： %s',array($sExtName,$sCode,$sData));
		$aExtensionSetup = ExtensionSetup::singleton();
		
		$arrDepBy = $this->getDependenceBy() ;
		if(isset($arrDepBy[$sExtName])){
			foreach($arrDepBy[$sExtName] as $sDepByExtName){
				$this->view->createMessage(Message::notice, '发现被扩展 `%s` 依赖',array($sDepByExtName));
				$this->recursivelyUninstall($sDepByExtName , $sCode ,$sData);
			}
		}
		
		$aExtensionSetup->uninstall($sExtName , $sCode ,$sData);
		$this->view->createMessage(Message::success,'卸载 `%s` 成功',array($sExtName)) ;
	}
	
	private $arrDependenceBy = null ;
	private $arrDependence = null ;
}
