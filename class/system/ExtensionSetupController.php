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
	protected $arrConfig = array(
			'title'=>'扩展安装',
			'view' => array(
				'template' => 'system/ExtensionSetup.html' ,
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
	
	public function process()
	{
		$this->checkPermissions('您没有使用这个功能的权限,无法继续浏览',array()) ;
		
		$this->doActions();
		
		$this->view->variables()->set(
				'arrPlatformExtensions'
				, $this->scanPlatformExtensions(Platform::singleton())
		) ;
	}
	
	protected function form()
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
		
		$aExtSetupFun = new ExtensionSetupFunctions($this->messageQueue() );
		$aExtSetupFun->installAndEnableExtension( $aExtFolder );
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
				$arrExtMetas[$aExtMeta->title().'('.$aExtMeta->name().')'][] = $aExtMeta ;
				$aExtMeta->properties()->set('path',Folder::relativePath(
						$aPlatform->installFolder(true), $aExtMeta->installPath()
				)) ;
				
				if( !$aInstallExt=$aExtensionManager->extensionMetainfo($aExtMeta->name()) or $aInstallExt->version()->compare($aExtMeta->version())!==0 )
				{
					$aExtMeta->properties()->set('installed',false);
				}else{
					$aExtMeta->properties()->set('installed',true);
				}
			}
		}
		
		return $arrExtMetas ;
	}
}

