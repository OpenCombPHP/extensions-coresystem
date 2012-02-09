<?php
namespace org\opencomb\coresystem\system\patch ;

use org\opencomb\platform\ext\Extension ;
use org\jecat\framework\fs\FileSystem ;
use org\jecat\framework\lang\Exception;

class Patch{
	// 项目
	const ITEM_Platform = 'platform' ;
	const ITEM_Framework = 'framework' ;
	
	// 项目目录
	static private $arrPath = array(
		self::ITEM_Platform => '',
		self::ITEM_Framework => 'framework',
	);
	
	static public function itemPath($sItem){
		if(isset(self::$arrPatch[$sItem])){
			return self::$arrPatch[$sItem] ;
		}else{
			return false;
		}
	}
	
	public function __construct($sItem){
		$this->sItem = $sItem ;
	}
	
	public function item(){
		return $this->sItem ;
	}
	
	public function path(){
		return self::$arrPath[$this->sItem];
	}
	
	public function tagList(){
		if( null === $this->arrTagList ){
			$this->arrTagList = self::getTagList($this->path());
		}
		return $this->arrTagList ;
	}
	
	/**
	 * @retval sFilePath if succeed
	 * @retval false if fail
	 */
	public function create($sFrom,$sTo){
		$aXML = new \SimpleXMLElement('<patch></patch>');
		$aXML->itemName = $this->sItem ;
		$aXML->from = $sFrom ;
		$aXML->to = $sTo ;
		
		$sXML = $aXML->asXML();
		
		$sPath = $this->path();
		$sDiff = `cd $sPath && git diff $sFrom $sTo `;
		
		
		$aFolder = Extension::flyweight('coresystem')->publicFolder()->findFolder('patch',FileSystem::FIND_AUTO_CREATE);
		$sFileName = 'patch_'.$this->sItem.'_'.$sFrom.'_'.$sTo.'.zip';
		$aFile = $aFolder->findFile($sFileName,FileSystem::FIND_AUTO_CREATE_OBJECT);
		if($aFile->exists()){
			$aFile->delete();
		}
		
		$sFilePath = $aFile->url(false);
		
		$aZip = new \ZipArchive();
		if( TRUE !== $aZip->open($sFilePath,\ZIPARCHIVE::CREATE) ){
			throw new Exception(
				"无法打开zip文件 %s ",
				array(
					$sFilePath,
				)
			);
			return false;
		}
		
		if( TRUE !== $aZip->addFromString( 'metainfo.xml' , $sXML ) ){
			throw new Exception(
				"无法将内容写入zip文件 %s %s ",
				array(
					$sFilePath,
					'metainfo.xml',
				)
			);
			return false;
		}
		
		if( TRUE !== $aZip->addFromString( 'patch.diff' , $sDiff ) ){
			throw new Exception(
				"无法将内容写入zip文件 %s %s ",
				array(
					$sFilePath,
					'patch.diff',
				)
			);
			return false;
		}
		
		if( TRUE !== $aZip->close() ){
			throw new Exception(
				'保存zip文件失败 ： %s',
				array(
					$sFilePath,
				)
			);
		}
		
		return $sFilePath ;
	}
	
	static private function getTagList($sPath){
		$str = `cd $sPath && git tag`;
		$arr = explode("\n",$str);
		
		$arrRtn = array();
		foreach($arr as $a){
			if(!empty($a)){
				$arrRtn [] = $a;
			}
		}
		
		return $arrRtn;
	}
	
	private $sItem = null ;
	private $arrTagList = null ;
}
