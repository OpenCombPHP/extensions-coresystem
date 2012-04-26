<?php
namespace org\opencomb\coresystem\lib ;

use org\jecat\framework\ui\ObjectContainer;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\Type;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\xhtml\compiler\NodeCompiler;

class LibCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Type::check ( "org\\jecat\\framework\\ui\\xhtml\\Node", $aObject );
	
		if( !$sLibName = $aObject->attributes()->string('name') )
		{
			throw new Exception("lib 标签缺少 name 树形") ;
		}
		
		if( $aObject->attributes()->has('version') ){
			$sLibVersion = $aObject->attributes()->string('version') ;
		}else{
			$sLibVersion = '*';
		}
		
		foreach(LibManager::singleton()->libraryFileIterator('js',$sLibName,$sLibVersion) as $sFile)
		{
			$sFile = addslashes($sFile) ;
			$aDev->preprocessStream()->write("jc\\resrc\\HtmlResourcePool::singleton()->addRequire(\"{$sFile}\",jc\\resrc\\HtmlResourcePool::RESRC_JS) ;") ;
		}
		foreach(LibManager::singleton()->libraryFileIterator('css',$sLibName,$sLibVersion) as $sFile)
		{
			$sFile = addslashes($sFile) ;
			$aDev->preprocessStream()->write("jc\\resrc\\HtmlResourcePool::singleton()->addRequire(\"{$sFile}\",jc\\resrc\\HtmlResourcePool::RESRC_CSS) ;") ;
		}
		
	}
}
