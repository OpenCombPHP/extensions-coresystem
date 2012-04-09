<?php
namespace org\opencomb\coresystem\system\patch ;

use org\opencomb\platform\ext\Extension ;
use org\jecat\framework\fs\Folder ;
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
		$sPath = Folder::singleton()->findFolder($this->path())->path();
		$aTmpFolder = Extension::flyweight('coresystem')->filesFolder()->findFolder('tmp',Folder::FIND_AUTO_CREATE);
		$aErrFile = $aTmpFolder->findFile('patch.err',Folder::FIND_AUTO_CREATE_OBJECT);
		$sErrFileName = $aErrFile->path();
		
		$sSummary = `cd $sPath && git diff --numstat --summary $sFrom..$sTo 2>$sErrFileName `;
		
		$sErrFileContent = file_get_contents($sErrFileName);
		unlink($sErrFileName );
		
		if(!empty($sErrFileContent)){
			throw new Exception('执行git出错 : %s ',$sErrFileContent);
			return false;
		}
		
		$arrSummary = explode("\n",$sSummary);
		$arrFileList = array();
		
		$arrPreg = array(
			'delete' => '`^ delete mode \d{6} (.*)$`',
			'create' => '`^ create mode \d{6} (.*)$`',
			'update' => '`^\d+	\d+	(.*)$`',
		);
		
		foreach($arrSummary as $sLine){
			foreach($arrPreg as $sName => $sPreg){
				if(preg_match($sPreg,$sLine,$arrMatch)){
					$sFile = $arrMatch[1];
					$arrFileList[$sFile] = $sName;
					break;
				}
			}
		}
		
		$aXML = new \SimpleXMLElement('<patch></patch>');
		$aXML->itemName = $this->sItem ;
		$aXML->from = $sFrom ;
		$aXML->to = $sTo ;
		
		foreach($arrFileList as $sFilePath => $sType){
			$aChildXML = $aXML->addChild('file');
			$aChildXML->addAttribute('path',$sFilePath);
			$aChildXML->addAttribute('type',$sType);
		}
		
		$sXML = $aXML->asXML();
		
		$aZipFolder = Extension::flyweight('coresystem')->filesFolder()->findFolder('patch',Folder::FIND_AUTO_CREATE);
		$sZipFileName = 'patch_'.$this->sItem.'_'.$sFrom.'_'.$sTo.'.zip';
		$aZipFile = $aZipFolder->findFile($sZipFileName,Folder::FIND_AUTO_CREATE_OBJECT);
		if($aZipFile->exists()){
			$aZipFile->delete();
		}
		
		$sZipFilePath = $aZipFile->path();
		
		$aZip = new \ZipArchive();
		if( TRUE !== $aZip->open($sZipFilePath,\ZIPARCHIVE::CREATE) ){
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
		
		if( TRUE !== $aZip->addFromString( 'summary.txt' , $sSummary ) ){
			throw new Exception(
				"无法将内容写入zip文件 %s %s ",
				array(
					$sPath,
					'summary.txt',
				)
			);
			return false;
		}
		
		foreach($arrFileList as $sFilePath => $sType){
			if( 'delete' !== $sType ){
				$sErrFileName = $aErrFile->path();
				
				$sHistoryContent = `cd $sPath && git show $sTo:$sFilePath 2>$sErrFileName `;
				
				$sErrFileContent = file_get_contents($sErrFileName);
				unlink($sErrFileName );
				
				if(!empty($sErrFileContent)){
					throw new Exception('执行git出错 : %s ',$sErrFileContent);
					return false;
				}
				
				if( TRUE !== $aZip->addFromString( 'src/'.$sFilePath , $sHistoryContent ) ){
					throw new Exception(
						"无法将内容写入zip文件 %s %s ",
						array(
							$sPath,
							$sFilePath,
						)
					);
					return false;
				}
			}
		}
		
		if( TRUE !== $aZip->close() ){
			throw new Exception(
				'保存zip文件失败 ： %s',
				array(
					$sFilePath,
				)
			);
		}
		
		return $aZipFile->path() ;
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
