<?php
namespace org\opencomb\coresystem\system ;

use org\opencomb\coresystem\auth\Id;
use org\opencomb\coresystem\mvc\controller\ControlPanel;
use org\jecat\framework\message\Message;
use org\opencomb\platform\system\OcSession;
use org\opencomb\platform\service\Service;

class ExtensionSetupByUpload extends ControlPanel
{
	protected $arrConfig = array(
			'title'=>'上传并安装扩展',
			'view' => array(
				'template' => 'system/ExtensionSetupByUpload.html' ,
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
	}
	
	public function form(){
				// error
				$nError = $this->params['file']['error'] ;
				if( UPLOAD_ERR_OK !==  $nError ){
					$sErrorMessage = $this->params['file']['errorMessage'] ;
					$this->createMessage(Message::error,'上传时发生错误:`%d`:`%s`',array($nError,$sErrorMessage));
					return;
				}
				// file 
				$sFileName = $this->params['file']['name'];
				$sTmpFilePath = $this->params['file']['tmp_name'];
				// debug message
				if( Service::singleton()->isDebugging() ){
					$this->createMessage(Message::error,'调试信息: filename:`%s` path:`%s`',array($sFileName,$sTmpFilePath));
				}
				// ExtensionSetupFunctions
				$aExtensionSetupFunctions = new ExtensionSetupFunctions($this->messageQueue());
				// moveUploadFile
				$aUploadFile = $aExtensionSetupFunctions->moveUploadFile($sTmpFilePath,$sFileName);
				if( FALSE === $aUploadFile ){
					return;
				}
				// xml
				$aXML = $aExtensionSetupFunctions->getXML( $aUploadFile );
				if( FALSE === $aXML ){
					return;
				}
				// unpackage
				$aUnpackageFolder = $aExtensionSetupFunctions->unpackage($aUploadFile,$aXML);
				if( FALSE === $aUnpackageFolder ){
					return;
				}
				// remove upload file
				$aExtensionSetupFunctions->removeUploadFile( $aUploadFile );
				// clearRestoreCache
				$aExtensionSetupFunctions ->clearRestoreCache();
				// install
				$aExtMeta = $aExtensionSetupFunctions->installPackage($aUnpackageFolder) ;
				if( FALSE === $aExtMeta ){
					return;
				}
				// enable
				$aExtensionSetupFunctions->enablePackage($aExtMeta);
				// updateSignature
				OcSession::singleton()->updateSignature() ;
	}
}

