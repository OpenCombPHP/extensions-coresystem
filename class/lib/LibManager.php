<?php
namespace org\opencomb\coresystem\lib ;

use org\jecat\framework\resrc\HtmlResourcePool;
use org\jecat\framework\pattern\iterate\ArrayIterator;
use org\jecat\framework\lang\Object;

class LibManager extends Object
{
	public function registerLibrary($sName,$sVersion,$jsFiles=array(),$cssFiles=array(),$requires=array(),$bDefaultVersion=false)
	{
		$arrJsFiles = $jsFiles? (array)$jsFiles: array() ;
		$arrCssFiles = $cssFiles? (array)$cssFiles: array() ;
		$arrRequires = $requires? (array)$requires: array() ;
		
		$sName = strtolower($sName) ;
		$sVersion = strtolower($sVersion) ;
		$this->arrLibraries[$sName][$sVersion] = array(
				'js' => &$arrJsFiles ,
				'css' => &$arrCssFiles ,
				'require' => &$arrRequires ,
		) ;
		
		if($bDefaultVersion)
		{
			$this->arrLibraries[$sName]['*'] =& $this->arrLibraries[$sName][$sVersion] ;
		}
	}
	
	public function libraryFileIterator($sFileType,$sName,$sVersion='*')
	{
		if( !isset($this->arrLibraries[$sName]['*']) )
		{
			return new \EmptyIterator() ;
		}
		
		$aFileIter = new \AppendIterator() ;
		
		// for requires
		foreach($this->arrLibraries[$sName]['*']['require'] as $sRequireLib)
		{
			@list($sReqLibName,$sReqLibVersion) = explode(':',$sRequireLib) ;
			if(!$sReqLibVersion)
			{
				$sReqLibVersion = '*' ;
			}
			
			$aFileIter->append($this->libraryFileIterator($sFileType,$sReqLibName,$sReqLibVersion)) ;
		}
		
		// for self
		$aFileIter->append(new \ArrayIterator($this->arrLibraries[$sName]['*'][$sFileType])) ;
		
		return $aFileIter ;
	}
	
	public function loadLibrary($sName,$sVersion='*',HtmlResourcePool $aHtmlResrcPool=null)
	{
		if(!$aHtmlResrcPool)
		{
			$aHtmlResrcPool = HtmlResourcePool::singleton() ;
		}
		
		// 载入 js 文件
		foreach($this->libraryFileIterator('js',$sName,$sVersion) as $sFile)
		{
			$aHtmlResrcPool->addRequire($sFile,\org\jecat\framework\resrc\HtmlResourcePool::RESRC_JS) ;
		}
		// 载入 css 文件
		foreach($this->libraryFileIterator('css',$sName,$sVersion) as $sFile)
		{
			$aHtmlResrcPool->addRequire($sFile,\org\jecat\framework\resrc\HtmlResourcePool::RESRC_CSS) ;
		}
	}
	
	private $arrLibraries = array() ;
}
