<?php
namespace org\opencomb\coresystem\system ;

use org\jecat\framework\fs\File;
use org\jecat\framework\message\Message;
use org\jecat\framework\fs\Folder;
use org\opencomb\platform\ext\Extension;
use org\opencomb\coresystem\mvc\controller\ControlPanel;

class ExtensionDownloadSetup extends ControlPanel
{
	public function process()
	{
		if( empty($this->params['pkgUrl']) )
		{
			$this->createMessage(Message::error, "missing parameter pkgUrl") ;
			$this->response()->putReturnVariable('result',false) ;
			return ;
		}

		$sContents = file_get_contents($this->params['pkgUrl']) ;
		$sPackageFilename = md5($this->params['pkgUrl']) ;
		$aTmpFile = Extension::flyweight("coresystem")->tmpFolder()->findFile( $sPackageFilename.".zip", Folder::FIND_AUTO_CREATE_OBJECT ) ;
		if( !$aWriter = $aTmpFile->openWriter() )
		{
			$this->createMessage(Message::error, "can not open tmp file.",'error') ;
			$this->response()->putReturnVariable('result',false) ;
			return ;
		}
		$aWriter->write( $sContents ) ;
		$aWriter->close() ;
		

		$aExtensionSetupFunctions = new ExtensionSetupFunctions($this->messageQueue());
		if( !$aFolder=$aExtensionSetupFunctions->unpackage($aTmpFile) )
		{
			$this->response()->putReturnVariable('result',false) ;
			$aTmpFile->delete() ;
			return ;
		}

		$aTmpFile->delete() ;
		
		if( !$aExtensionSetupFunctions->installPackage($aFolder) )
		{
			$this->response()->putReturnVariable('result',false) ;
			return ;
		}
		

		$this->response()->putReturnVariable('result',true) ;
	}

}

?>