<?php
namespace org\opencomb\coresystem\system ;

use org\jecat\framework\message\MessageQueue ;
use org\jecat\framework\message\Message ;
use org\opencomb\platform\ext\Extension ;
use org\opencomb\platform\ext\ExtensionMetainfo ;
use org\jecat\framework\fs\FileSystem ;
use org\jecat\framework\fs\IFile ;
use org\jecat\framework\fs\IFolder ;
use org\opencomb\platform\ext\ExtensionSetup ;
use org\jecat\framework\lang\Exception ;
use org\opencomb\platform\system\PlatformSerializer ;
use org\opencomb\platform\Platform ;

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
	 * @return IFile
	 */
	public function moveUploadFile( $sLocalFilePath , $sFileName ){
		// upload temp file
		$aTmpFile = Extension::flyweight('coresystem')->publicFolder()->findFile($sFileName , FileSystem::FIND_AUTO_CREATE_OBJECT) ;
		$sTmpFilePath = $aTmpFile->url(false) ;
		$resmove = move_uploaded_file($sLocalFilePath,$sTmpFilePath);
		if( TRUE !== $resmove){
			$this->aMessageQueue->create(
					Message::error
					, "转移上传文件失败"
			) ;
			return null;
		}
		return $aTmpFile ;
	}
	
	public function removeUploadFile( IFile $aFile ){
		$aFile->delete();
	}
	
	public function getXML( IFile $aFile ){
		/*
		 * http://php.net/manual/en/function.ziparchive-open.php
		 * it is not a good idea to store file for unzipping in folder defined by sys_get_temp_dir().
		 */
		$aZip = new \ZipArchive();
		$res = $aZip->open($aFile->url(false)) ;
		if( TRUE === $res){
			$sComment = $aZip->getFromName('metainfo.xml');
			$aXML = simplexml_load_string($sComment);
			return $aXML ;
		}else{
			$this->aMessageQueue->create(
					Message::error
					, "读取metainfo.xml失败"
			) ;
			return null;
		}
	}
	
	public function unpackage(IFile $aZipFile , \SimpleXMLElement $aXML ){
		$sShortVersion = $aXML->version;
		$sExtName = $aXML->name;
		$aToFolder = FileSystem::singleton()->findFolder('/extensions/'.$sExtName.'/'.$sShortVersion , FileSystem::FIND_AUTO_CREATE);
		$aZip = new \ZipArchive;
		$resOpen = $aZip->open($aZipFile->url(false)) ;
		if( TRUE !==  $resOpen ){
			$this->aMessageQueue->create(
					Message::error
					, "打开压缩文件失败"
			) ;
			return null;
		}
		$aZip->extractTo($aToFolder->url(false));
		$aZip->close();
		return $aToFolder ;
	}
	
	public function clearRestoreCache(){
		PlatformSerializer::singleton()->clearRestoreCache(Platform::singleton());
	}
	
	public function installPackage(IFolder $aExtFolder){
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
		return null ;
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
		}catch(Exception $e){
			$this->aMessageQueue->create(
					Message::error
					, "激活失败 : %s"
					, $e->message()
			) ;
		}
	}
	
	private $aMessageQueue = null;
}