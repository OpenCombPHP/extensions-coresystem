<?php
namespace org\opencomb\coresystem\system ;

use org\opencomb\platform\system\PlatformSerializer;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\message\Message;
use org\jecat\framework\fs\FileSystem;
use org\opencomb\coresystem\mvc\controller\ControlPanel;
use org\opencomb\platform\ext\ExtensionSetup as ExtensionSetupOperator ;
use org\opencomb\platform\Platform ;
use org\opencomb\platform\system\PlatformFactory ;

class ExtensionSetup extends ControlPanel 
{
	public function createBeanConfig()
	{
		return array(
			'view:view' => array(
				'template' => 'system/ExtensionSetup.html' ,
				'class' => 'form' ,
				'widget:path' => array(
						'class' => 'text' ,
						'title'=>'扩展目录路径' ,
						'value' => '/extensions/...' ,
						'verifier:notempty' => array() ,
				) 
			) ,
		) ;
	}
	
	public function process()
	{
		if( $this->view->isSubmit() )
		{do{
			$this->view->loadWidgets($this->params()) ;
			
			if( !$this->view->verifyWidgets() )
			{
				break ;
			}
			
			$sPath = trim($this->view->path->value()) ;
			if( !$aExtFolder = FileSystem::singleton()->findFolder($sPath) )
			{
				$this->view->createMessage(Message::error,'输入的路径不存在:%s',$sPath) ;
				break ;
			}
			
			try{
				// 清理缓存
				PlatformSerializer::singleton()->clearRestoreCache(Platform::singleton());
				
				// 安装
				$aExtMeta = ExtensionSetupOperator::singleton()->install($aExtFolder , $this->view->messageQueue() ) ;
				
				$this->view->createMessage(
						Message::success
						, "扩展% s(%s:%s) 已经成功安装到平台中。"
						, array( $aExtMeta->title(), $aExtMeta->name(), $aExtMeta->version() )
				) ;

				// 激活
				ExtensionSetupOperator::singleton()->enable($aExtMeta->name()) ;
				
				$this->view->createMessage(
						Message::success
						, "扩展 %s(%s:%s) 已经激活使用。"
						, array( $aExtMeta->title(), $aExtMeta->name(), $aExtMeta->version() )
				) ;
			}catch(Exception $e){
				$this->view->createMessage(Message::error,$e->getMessage(),$e->messageArgvs()) ;
			}

		} while(0); }
	}
}

?>
