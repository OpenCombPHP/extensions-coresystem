<?php
namespace org\opencomb\coresystem\system\patch ;

use org\opencomb\coresystem\mvc\controller\ControlPanel;
use org\jecat\framework\message\Message ;
use org\jecat\framework\lang\Exception;
use org\opencomb\platform\ext\Extension ;
use org\jecat\framework\fs\Folder ;
use org\jecat\framework\util\Version;
use org\opencomb\platform\Platform;

class InstallPatch extends ControlPanel{
	public function createBeanConfig(){
		return array(
			'view:view' => array(
				'template' => 'system/InstallPatch.html' ,
			) ,
		) ;
	}
	
	public function process(){
		$this->doActions() ;
		
		$arrItemName = array(
			Patch::ITEM_Platform ,
			Patch::ITEM_Framework ,
		);
		
		$this->view->variables()->set('arrItemName',$arrItemName);
	}
	
	public function actionInstall(){
		// input
		$sName = $this->params['name'];
		$sFileName = $this->params['filename'];
		$aFile = $this->params['file'];
		
		// sName
		// aCurrentVersion
		// extract folder
		switch($sName){
		case 'framework':
			$aCurrentVersion = Version::FromString ( \org\jecat\framework\VERSION );
			$aExtractFolder = Folder::singleton()->findFolder('framework');
			break;
		case 'platform':
			$aCurrentVersion = Platform::singleton ()->version ();
			$aExtractFolder = Folder::singleton()->findFolder('');
			break;
		default:
			$this->view->createMessage(Message::error,'param name error ：%s',$sName);
			break;
		}
		
		// error
		$nError = $aFile->error() ;
		if( UPLOAD_ERR_OK !==  $nError ){
			$sErrorMessage = $aFile->errorMessage() ;
			$this->view->createMessage(Message::error,'上传时发生错误:`%d`:`%s`',array($nError,$sErrorMessage));
			return false;
		}
		// file 
		$sFileName = $aFile->name();
		$sFilePath = $aFile->localPath();
		
		// move file
		$aUploadFolder = Extension::flyweight('coresystem')->publicFolder()->findFolder('upload' , Folder::FIND_AUTO_CREATE) ;
		$aUploadFile = $aUploadFolder->findFile($sFileName , Folder::FIND_AUTO_CREATE_OBJECT) ;
		$sUploadFilePath = $aUploadFile->path() ;
		$resmove = move_uploaded_file($sFilePath,$sUploadFilePath);
		if( TRUE !== $resmove){
			$this->view->createMessage(
					Message::error,
					 "转移上传文件失败"
			) ;
			return false;
		}
		
		// zip object
		$aZip = new \ZipArchive();
		if( TRUE !== $aZip->open($sUploadFilePath) ){
			$this->view->createMessage(
				Message::error,
				"无法打开zip文件 %s ",
				array(
					$sFilePath,
				)
			);
			return false;
		}
		
		// read xml
		$sComment = $aZip->getFromName('metainfo.xml');
		$aXML = simplexml_load_string($sComment);
		
		if( (string)$aXML->itemName !== $sName ){
			$this->view->createMessage(
				Message::error,
				"上传文件内容错误：这不是 %s 的升级程序，而是： %s",
				array(
					$sName,
					$aXML->itemName,
				)
			);
			return false;
		}
		
		$aFromVersion = Version::fromString($aXML->from);
		if( $aFromVersion->to32Integer() !== $aCurrentVersion->to32Integer() ){
			$this->view->createMessage(
				Message::error,
				"上传文件内容错误：这是从版本 %s 升级的程序，而当前版本是： %s",
				array(
					$aFromVersion->toString(true),
					$aCurrentVersion->toString(true),
				)
			);
			return false;
		}
		
		foreach($aXML->file as $aFileXML){
			$aAttributes = $aFileXML->attributes();
			$sPath = $aAttributes['path'] ;
			$sType = $aAttributes['type'] ;
			
			$aExtractFile = $aExtractFolder->findFile($sPath,Folder::FIND_AUTO_CREATE_OBJECT);
			switch($sType){
			case 'create':
				$sFileContent = $aZip->getFromName('src/'.$sPath);
				$aWriter = $aExtractFile->openWriter();
				$aWriter->write($sFileContent);
				
				$this->view->createMessage(
					Message::success,
					'创建文件 `%s` 成功',
					array(
						$aExtractFile->path(),
					)
				);
				break;
			case 'delete':
				if(!$aExtractFile->delete()){
					$this->view->createMessage(
						Message::error,
						'删除文件失败 ： %s',
						array(
							$aExtractFile->path(),
						)
					);
					return false;
				}
				
				$this->view->createMessage(
					Message::success,
					'删除文件 `%s` 成功',
					array(
						$aExtractFile->path(),
					)
				);
				break;
			case 'update':
				if(!$aExtractFile->delete()){
					$this->view->createMessage(
						Message::error,
						'修改文件失败 ： %s',
						array(
							$aExtractFile->path(),
						)
					);
					return false;
				}
				
				if(!$aExtractFile->create()){
					$this->view->createMessage(
						Message::error,
						'修改文件失败 ： %s',
						array(
							$aExtractFile->path(),
						)
					);
					return false;
				}
				
				$sFileContent = $aZip->getFromName('src/'.$sPath);
				$aWriter = $aExtractFile->openWriter();
				$aWriter->write($sFileContent);
				
				$this->view->createMessage(
					Message::success,
					'修改文件 `%s` 成功',
					array(
						$aExtractFile->path(),
					)
				);
				break;
			}
		}
		
		$this->view->createMessage(
			Message::success,
			"安装成功"
		);
		return true;
	}
}
