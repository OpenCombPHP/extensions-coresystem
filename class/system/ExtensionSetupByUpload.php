<?php
namespace org\opencomb\coresystem\system ;

use org\opencomb\coresystem\mvc\controller\ControlPanel ;
use org\jecat\framework\message\Message ;
use org\jecat\framework\util\Version ;

class ExtensionSetupByUpload extends ControlPanel
{
	public function createBeanConfig()
	{
		return array(
			'view:view' => array(
				'template' => 'system/ExtensionSetupByUpload.html' ,
				'class' => 'form' ,
			) ,
		) ;
	}
	
	public function process()
	{
		if( $this->view->isSubmit() )
		{
			do{
				// error
				$nError = $this->params['file']->error() ;
				if( UPLOAD_ERR_OK !==  $nError ){
					$sErrorMessage = $this->params['file']->errorMessage() ;
					$this->view->createMessage(Message::error,'上传时发生错误:`%d`:`%s`',array($nError,$sErrorMessage));
					break;
				}
				// file 
				$sFileName = $this->params['file']->name();
				$sTmpFilePath = $this->params['file']->localPath();
				// debug message
				$this->view->createMessage(Message::error,'调试信息: filename:`%s` path:`%s`',array($sFileName,$sTmpFilePath));
				// ExtensionSetupFunctions
				$aExtensionSetupFunctions = new ExtensionSetupFunctions($this->view->messageQueue());
				// moveUploadFile
				$aUploadFile = $aExtensionSetupFunctions->moveUploadFile($sTmpFilePath,$sFileName);
				if( null === $aUploadFile ){
					break;
				}
				// xml
				$aXML = $aExtensionSetupFunctions->getXML( $aUploadFile );
				if( null === $aXML ){
					break;
				}
				// unpackage
				$aUnpackageFolder = $aExtensionSetupFunctions->unpackage($aUploadFile,$aXML);
				if( null === $aUnpackageFolder ){
					break;
				}
				// remove upload file
				$aExtensionSetupFunctions->removeUploadFile( $aUploadFile );
				// clearRestoreCache
				$aExtensionSetupFunctions ->clearRestoreCache();
				// install
				$aExtMeta = $aExtensionSetupFunctions->installPackage($aUnpackageFolder) ;
				if( null === $aExtMeta ){
					break;
				}
				// enable
				$aExtensionSetupFunctions->enablePackage($aExtMeta);
			}while(false);
		}
	}
}
