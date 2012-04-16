<?php
namespace org\opencomb\coresystem\system ;

use org\opencomb\platform\service\Service;

use org\opencomb\platform\ext\ExtensionDataClearer;

use org\opencomb\coresystem\auth\Id;

use org\opencomb\coresystem\mvc\controller\ControlPanel;
use org\opencomb\platform\ext\ExtensionManager as ExtensionManagerOperator ;
use org\jecat\framework\message\Message ;
use org\opencomb\platform\ext\ExtensionSetup ;
use org\jecat\framework\lang\Exception;
use org\opencomb\platform\service\ServiceSerializer;
use org\opencomb\platform\ext\dependence\RequireItem ;

class ExtensionManagerController extends ControlPanel
{
	public function createBeanConfig()
	{
		return array(
			'title'=>'扩展管理',
			'view:view' => array(
				'template' => 'system/ExtensionManager.html' ,
			) ,
			'perms' => array(
				// 权限类型的许可
				'perm.purview'=>array(
					'namespace'=>'coresystem',
					'name' => Id::PLATFORM_ADMIN,
				) ,
			) ,
			
			'controller:rebuild' => array(
				'class' => 'org\\opencomb\\coresystem\\system\\RebuildPlatform',
				'param.exclude' => 'act',
			),
		) ;
	}
	
	public function process()
	{
		$this->checkPermissions('您没有使用这个功能的权限,无法继续浏览',array()) ;
		
		$this->doActions() ;
		
		$aExtMgr = ExtensionManagerOperator::singleton() ;
		
		// 已启用的扩展
		$arrEnabledExtensions = array() ;
		$arrPriority = array();
		foreach($aExtMgr->iterator() as $aExtension)
		{
			$arrEnabledExtensions[$aExtension->metainfo()->name()] = $aExtension ;
			$arrPriority[$aExtension->runtimePriority()][] = $aExtension->metainfo()->name();
			
			$sExtName = $aExtension->metainfo()->name() ;
			$arrEnableState[$sExtName] = true;
		}
		
		// 禁用的扩展
		$arrDisabledExtensionMetainfos = array() ;
		foreach($aExtMgr->metainfoIterator() as $aExtensionMetainfo)
		{
			if(!isset($arrEnabledExtensions[$aExtensionMetainfo->name()]))
			{
				$sExtName = $aExtensionMetainfo->name() ;
				$arrDisabledExtensionMetainfos[] = $aExtensionMetainfo ;
				
				$arrEnableState[$sExtName] = false;
			}
		}
		
		// dependence
		$arrDependence = $this->getDependence();
		
		$arrDependenceBy = $this->getDependenceBy() ;
		$arrDependenceByRecursively = array() ;
		foreach($arrDependenceBy as $sExtName => $v){
			$arrDependenceByRecursively[$sExtName] = $this->getDependenceByRecursively($sExtName );
		}
		
		$this->view->variables()->set('arrPriority',$arrPriority);
		$this->view->variables()->set('arrEnabledExtensions',$arrEnabledExtensions) ;
		$this->view->variables()->set('arrDisabledExtensionMetainfos',$arrDisabledExtensionMetainfos) ;
		$this->view->variables()->set('arrDependence',$arrDependence);
		$this->view->variables()->set('arrDependenceBy',$arrDependenceBy);
		$this->view->variables()->set('arrEnableState',$arrEnableState);
		
		if( '1' !== $this->view->variables()->get('rebuild') ){
			$this->rebuild->disable() ;
		}
	}
	
	public function actionDisable(){
		$sExtName = $this->params['name'];
		
		try{
			$this->recursivelyDisable($sExtName);
		}catch(Exception $e){
			$this->view->createMessage(Message::error,$e->getMessage(),$e->messageArgvs()) ;
		}
		ServiceSerializer::singleton()->clearRestoreCache();
		\org\opencomb\platform\system\OcSession::singleton()->updateSignature() ;
		$this->view->variables()->set('rebuild','1');
	}
	
	public function actionUninstall(){
		$sExtName = $this->params['name'];
		
		try{			
			$this->recursivelyUninstall($sExtName,$this->params->bool('retainData'));
		}catch(Exception $e){
			$this->view->createMessage(Message::error,$e->getMessage(),$e->messageArgvs()) ;
		}
		ServiceSerializer::singleton()->clearRestoreCache();
		\org\opencomb\platform\system\OcSession::singleton()->updateSignature() ;
		$this->view->variables()->set('rebuild','1');
	}
	
	public function actionChangePriority(){
		$sExtName = $this->params['name'];
		$nNewPriority = $this->params['priority'];
		
		$this->view->createMessage(Message::notice, '更改扩展优先级 ： %s , %d',array($sExtName,$nNewPriority));
		$aExtensionSetup = ExtensionSetup::singleton();
		try{
			$aExtensionSetup->changePriority($sExtName,$nNewPriority);
			ServiceSerializer::singleton()->clearRestoreCache();
			$this->view->createMessage(Message::success,'成功修改扩展 `%s` 的优先级为 `%d`',array($sExtName,$nNewPriority));
		}catch(Exception $e){
			$this->view->createMessage(Message::error,$e->getMessage(),$e->messageArgvs()) ;
		}
		$this->view->variables()->set('rebuild','1');
	}
	
	/**
	 * @param dire 方向，'up'或'down'
	 */
	public function actionChangeOrder(){
		$sExtName = $this->params['name'];
		$sDire = $this->params['dire'];
		
		$this->view->createMessage(Message::notice, '更改扩展顺序 ： %s , %s',array($sExtName,$sDire));
		$aExtSetup = ExtensionSetup::singleton();
		try{
			$aExtSetup->changeOrder($sExtName,$sDire);
			ServiceSerializer::singleton()->clearRestoreCache();
			$this->view->createMessage(Message::success, '成功更改扩展顺序 ： %s , %s',array($sExtName,$sDire));
		}catch(Exception $e){
			$this->view->createMessage(Message::error,$e->getMessage(),$e->messageArgvs()) ;
		}
		$this->view->variables()->set('rebuild','1');
	}
	
	public function actionEnable(){
		$sExtName = $this->params['name'];
		try{
			$this->recursivelyEnable($sExtName);
		}catch(Exception $e){
			$this->view->createMessage(Message::error,$e->getMessage(),$e->messageArgvs()) ;
		}
		ServiceSerializer::singleton()->clearRestoreCache();
		\org\opencomb\platform\system\OcSession::singleton()->updateSignature() ;
		$this->view->variables()->set('rebuild','1');
	}
	
	private function getDependenceBy(){
		if( null === $this->arrDependenceBy ){
			$aExtMgr = ExtensionManagerOperator::singleton() ;
			$this->arrDependenceBy = array();
			foreach($aExtMgr->metainfoIterator() as $aExtMetainfo){
				$sExtName = $aExtMetainfo->name();
				$this->arrDependenceBy[$sExtName] = array();
			}
			foreach($aExtMgr->metainfoIterator() as $aExtMetainfo){
				$sExtName = $aExtMetainfo->name();
				foreach($aExtMetainfo->dependence()->iterator() as $aRequireItem){
					if( $aRequireItem->type() === RequireItem::TYPE_EXTENSION ){
						$sItemName = $aRequireItem->itemName();
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
				if($this->isExtensionEnabled($sDepByExtName)){
					$this->view->createMessage(Message::notice, '发现被扩展 `%s` 依赖',array($sDepByExtName));
					$this->recursivelyDisable($sDepByExtName);
				}
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
	
	private function recursivelyUninstall($sExtName,$bRetainData){
		$aExtensionSetup = ExtensionSetup::singleton();
		
		$arrDepBy = $this->getDependenceBy() ;
		if(isset($arrDepBy[$sExtName])){
			foreach($arrDepBy[$sExtName] as $sDepByExtName){
				$this->view->createMessage(Message::notice, '发现被扩展 `%s` 依赖',array($sDepByExtName));
				$this->recursivelyUninstall($sDepByExtName,$bRetainData);
			}
		}
		
		// 卸载扩展
		$aExtensionSetup->uninstall($sExtName,$this->messageQueue(),$bRetainData);
	}
	
	private $arrDependenceBy = null ;
	private $arrDependence = null ;
}
