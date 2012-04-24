<?php
namespace org\opencomb\coresystem\system ;

use org\opencomb\platform\ext\ExtensionManager;
use org\opencomb\platform\ext\ExtensionMetainfo;
use org\opencomb\platform\Platform;
use org\opencomb\platform\service\Service;
use org\opencomb\coresystem\auth\Id;
use org\opencomb\platform\service\ServiceSerializer;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\message\Message;
use org\jecat\framework\fs\Folder;
use org\opencomb\coresystem\mvc\controller\ControlPanel;
use org\opencomb\platform\ext\ExtensionSetup;
use org\opencomb\platform\system\OcSession;
use org\opencomb\platform as oc;

class ExtensionSetupController extends ControlPanel 
{
	public function createBeanConfig()
	{
		return array(
			'title'=>'扩展安装',
			'view:view' => array(
				'template' => 'system/ExtensionSetup.html' ,
				'class' => 'form' ,
				'widget:extensionPath' => array(
						'class' => 'text' ,
						'title'=>'扩展目录路径' ,
						'value' => 'extensions/...' ,
						'verifier:notempty' => array() ,
				)
			) ,
			'controller:byUpload' => array(
					'class' => 'org\\opencomb\\coresystem\\system\\ExtensionSetupByUpload' ,
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

		$this->doActions() ;
		
		$this->view->variables()->set(
				'arrPlatformExtensions'
				, $this->scanPlatformExtensions(Platform::singleton())
		) ;
		
		if( $this->view->isSubmit() )
		{do{
			
		} while(0); }
	}
	
	protected function actionSubmit()
	{
		if( !$this->view->loadWidgets() )
		{
			return ;
		}
		
		$sPath = trim($this->view->extensionPath->value()) ;
		if( !$aExtFolder = Platform::singleton()->installFolder()->findFolder($sPath) )
		{
			$this->view->createMessage(Message::error,'输入的路径不存在:%s',$sPath) ;
			return ;
		}
		
		try{
			// 清理缓存
			ServiceSerializer::singleton()->clearRestoreCache(Service::singleton());
			
			// 安装
			$aExtMeta = ExtensionSetup::singleton()->install($aExtFolder , $this->view->messageQueue() ) ;
			
			$this->view->createMessage(
					Message::success
					, "扩展% s(%s:%s) 已经成功安装到平台中。"
					, array( $aExtMeta->title(), $aExtMeta->name(), $aExtMeta->version() )
			) ;

			// 激活
			ExtensionSetup::singleton()->enable($aExtMeta->name()) ;
			
			$this->view->createMessage(
					Message::success
					, "扩展 %s(%s:%s) 已经激活使用。"
					, array( $aExtMeta->title(), $aExtMeta->name(), $aExtMeta->version() )
			) ;
		}catch(Exception $e){
			$this->view->createMessage(Message::error,$e->getMessage(),$e->messageArgvs()) ;
		}
		OcSession::singleton()->updateSignature() ;
	}
	
	protected function scanPlatformExtensions(Platform $aPlatform)
	{
		$arrExtMetas = array() ;
		$aExtensionManager = ExtensionManager::singleton() ;
		
		$hExtensions = opendir(oc\EXTENSIONS_FOLDER) ;
		while( $sExtName=readdir($hExtensions) )
		{
			if( $sExtName==='.' or $sExtName==='..' or !is_dir(oc\EXTENSIONS_FOLDER.'/'.$sExtName) )
			{
				continue ;
			}
			$hOneExt = opendir(oc\EXTENSIONS_FOLDER.'/'.$sExtName) ;

			while( $sExtVer=readdir($hOneExt) )
			{
				$sExtFolder = oc\EXTENSIONS_FOLDER.'/'.$sExtName.'/'.$sExtVer ;
				if( $sExtVer==='.' or $sExtVer==='..' or !is_dir($sExtFolder) )
				{
					continue ;
				}
				
				$sExtMetaPath = $sExtFolder.'/metainfo.xml' ;
				if( !is_file($sExtMetaPath) )
				{
					continue ;
				}
				
				$aExtMeta = ExtensionMetainfo::load($sExtFolder,oc\EXTENSIONS_URL.'/'.$sExtName.'/'.$sExtVer) ;
				
				if( !$aInstallExt=$aExtensionManager->extensionMetainfo($aExtMeta->name()) or $aInstallExt->version()->compare($aExtMeta->version())!==0 )
				{
					$arrExtMetas[$aExtMeta->title().'('.$aExtMeta->name().')'][] = $aExtMeta ;
					$aExtMeta->properties()->set('path',Folder::relativePath(
							$aPlatform->installFolder(true), $aExtMeta->installPath()
					)) ;
				}
			}
		}
		
		return $arrExtMetas ;
	}
}

