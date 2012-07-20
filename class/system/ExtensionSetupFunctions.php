<?php
namespace org\opencomb\coresystem\system ;

use org\opencomb\platform\service\Service;
use org\jecat\framework\message\MessageQueue;
use org\jecat\framework\message\Message;
use org\opencomb\platform\ext\Extension;
use org\opencomb\platform\ext\ExtensionMetainfo;
use org\jecat\framework\fs\Folder;
use org\jecat\framework\fs\File;
use org\opencomb\platform\ext\ExtensionSetup;
use org\jecat\framework\lang\Exception;
use org\opencomb\platform as oc;

/**
 * 期待的改进：
 * 1.检查文件中是否有多余的use
 * 2.在扩展的任何一个函数中可以得到此扩展的 Extension 对象
 */
class ExtensionSetupFunctions
{
	public function __construct (MessageQueue $aMessageQueue){
		$this->aMessageQueue = $aMessageQueue ;
	}
	
	/**
	 * @return File
	 */
	public function moveUploadFile( $sLocalFilePath , $sFileName ){
		// upload temp file
		$aTmpFile = Extension::flyweight('coresystem')->filesFolder()->findFile($sFileName , Folder::FIND_AUTO_CREATE_OBJECT) ;
		$sTmpFilePath = $aTmpFile->path() ;
		$resmove = move_uploaded_file($sLocalFilePath,$sTmpFilePath);
		if( TRUE !== $resmove){
			$this->aMessageQueue->create(
					Message::error
					, "转移上传文件失败"
			) ;
			return FALSE;
		}
		return $aTmpFile ;
	}
	
	public function removeUploadFile( File $aFile ){
		$aFile->delete();
	}
	
	public function getXML( File $aFile ){
		/*
		 * http://php.net/manual/en/function.ziparchive-open.php
		 * it is not a good idea to store file for unzipping in folder defined by sys_get_temp_dir().
		 */
		$aZip = new \ZipArchive();
		$res = $aZip->open($aFile->path()) ;
		if( TRUE === $res){
			$sComment = $aZip->getFromName('metainfo.xml');
			$aXML = simplexml_load_string($sComment);
			return $aXML ;
		}else{
			$this->aMessageQueue->create(
					Message::error
					, "读取metainfo.xml失败"
			) ;
			return FALSE;
		}
	}
	
	public function checkBeforeUnpackage(\SimpleXMLElement $aXML){
		$aExtMeta = ExtensionMetainfo::loadFromXML($aXML);
		$aExtSetup = ExtensionSetup::singleton() ;
		$aExtSetup->checkDependence($aExtMeta,false);
	}
	
	private function getUnpackageFolder(ExtensionMetainfo $aExtMeta){
		$sExtName = $aExtMeta->name();
		$aVersion = $aExtMeta->version();
		$aToFolder = new Folder(oc\EXTENSIONS_FOLDER.'/'.$sExtName.'/'.$aVersion);
		
		return $aToFolder ;
	}
	
	public function unpackage(File $aZipFile , \SimpleXMLElement $aXML=null )
	{
		if(!$aXML)
		{
			if( !$aXML=$this->getXML($aZipFile) )
			{
				return false ;
			}
		}
		
		try{
			$this->checkBeforeUnpackage($aXML) ;
		}catch(Exception $e){
			$this->aMessageQueue->create(
					Message::error
					, $e->message()
			) ;
			return FALSE;
		}
		
		$aExtMeta = ExtensionMetainfo::loadFromXML($aXML);
		$aToFolder = $this->getUnpackageFolder($aExtMeta);
		$aZip = new \ZipArchive;
		$resOpen = $aZip->open($aZipFile->path()) ;
		if( TRUE !==  $resOpen ){
			$this->aMessageQueue->create(
					Message::error
					, "打开压缩文件失败"
			) ;
			return FALSE;
		}
		if( !$aToFolder->exists() ){
			$resExtract = $aZip->extractTo($aToFolder->path());
			if( TRUE !== $resExtract ){
				$this->aMessageQueue->create(
						Message::error
						, "解压缩文件失败"
				) ;
				return FALSE;
			}
		}
		$aZip->close();
		return $aToFolder ;
	}
	
	public function installPackage(Folder $aExtFolder){
		// 安装
		try{
			$aExtMeta = ExtensionSetup::singleton()->install($aExtFolder , $this->aMessageQueue ) ;
		
			$this->aMessageQueue->create(
					Message::success
					, "扩展% s(%s:%s) 已经成功安装到平台中。"
					, array( $aExtMeta->title(), $aExtMeta->name(), $aExtMeta->version() )
			) ;
		
			return $aExtMeta ;
		}catch(Exception $e){
			$this->aMessageQueue->create(
					Message::error
					, "安装失败 : %s"
					, $e->message()
			) ;
		}
		return FALSE ;
	}
	
	public function enablePackage(ExtensionMetainfo $aExtMeta){
		$sName = $aExtMeta->name() ;
		// 激活
		try{
			ExtensionSetup::singleton()->enable($sName) ;
			
			$this->aMessageQueue->create(
					Message::success
					, "扩展 %s(%s:%s) 已经激活使用。"
					, array( $aExtMeta->title(), $aExtMeta->name(), $aExtMeta->version() )
			) ;
			return true;
		}catch(Exception $e){
			$this->aMessageQueue->create(
					Message::error
					, "激活失败 : %s"
					, $e->message()
			) ;
			return false;
		}
	}
	
	public function installAndEnableExtension(Folder $aExtFolder){
		try{
			// 安装
			$aExtMeta = ExtensionSetup::singleton()->install($aExtFolder , $this->aMessageQueue ) ;
			
			$this->aMessageQueue->create(
					Message::success
					, "扩展% s(%s:%s) 已经成功安装到平台中。"
					, array( $aExtMeta->title(), $aExtMeta->name(), $aExtMeta->version() )
			) ;

			// 激活
			ExtensionSetup::singleton()->enable($aExtMeta->name()) ;
			
			$this->aMessageQueue->create(
					Message::success
					, "扩展 %s(%s:%s) 已经激活使用。"
					, array( $aExtMeta->title(), $aExtMeta->name(), $aExtMeta->version() )
			) ;
			
			$this->clearSystem() ;
		}catch(Exception $e){
			$this->aMessageQueue->create(Message::error,$e->getMessage(),$e->messageArgvs()) ;
		}
	}
	
	public function clearSystem(){
		// 禁止写入缓存
		\org\opencomb\platform\service\ServiceSerializer::singleton()->clearSystemObjects() ;
		// 清空缓存
		\org\opencomb\platform\service\ServiceSerializer::singleton()->clearRestoreCache();
		$aUI = \org\jecat\framework\ui\xhtml\UIFactory::singleton()->create();
		$sCompiledFolderPath = $aUI->sourceFileManager()->compiledFolderPath();
		$aFolder = new Folder( $sCompiledFolderPath );
		$aFolder->delete(true) ;
		\org\opencomb\platform\system\OcSession::singleton()->updateSignature() ;
	}
	
	private $aMessageQueue = null;
}

