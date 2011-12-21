<?php
namespace org\opencomb\coresystem\system ;

use org\opencomb\coresystem\mvc\controller\ControlPanel;
use org\opencomb\platform\ext\ExtensionManager as ExtensionManagerOperator ;

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
		$aExtMgr = ExtensionManagerOperator::singleton() ;
		
		// 已启用的扩展
		$arrEnabledExtensions = array() ;
		foreach($aExtMgr->iterator() as $aExtension)
		{
			$arrEnabledExtensions[] = $aExtension ;
		}
	
		// 禁用的扩展
		$arrDisabledExtensionMetainfos = array() ;
		foreach($aExtMgr->metainfoIterator() as $aExtensionMetainfo)
		{
			if(!$aExtMgr->extension($aExtensionMetainfo->name()))
			{
				$arrDisabledExtensionMetainfos[] = $aExtensionMetainfo ;
			}
		}
		
		$this->view->variables()->set('arrEnabledExtensions',$arrEnabledExtensions) ;
		$this->view->variables()->set('arrDisabledExtensionMetainfos',$arrDisabledExtensionMetainfos) ;
		
		
	}
}


?>