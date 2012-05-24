<?php
namespace org\opencomb\coresystem\system ;

use org\opencomb\coresystem\mvc\controller\ControlPanel;
use org\opencomb\coresystem\auth\Id;
use org\opencomb\platform\ext\Extension;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\util\Version;
use org\opencomb\platform\Platform;
use org\jecat\framework\fs\Folder;
use org\jecat\framework\message\Message;

class SystemUpgrade extends ControlPanel{
	public function createBeanConfig(){
		return array(
			'title'=>'系统重建',
			// 配置许可
			'perms' => array(
				// 权限类型的许可
				'perm.purview'=>array(
					'name' => Id::PLATFORM_ADMIN		// 要求管理员权限
				) ,
			) ,
			
			'view' => array(
				'template' => 'coresystem:system/SystemUpgrade.html',
			) ,
		) ;
	}
	
	public function process(){
		$this->checkPermissions() ;
		
		$this->doActions();
		
		$aSetting = Extension::flyweight('coresystem')->setting();
		$sXmlUrl = $aSetting->item('/systemupgrade','xmlUrl','http://release.opencomb.com/releases.html');
		
		$sContent = file_get_contents($sXmlUrl);
		$aXmlObj = simplexml_load_string( $sContent );
		$arrRelease = array();
		foreach($aXmlObj->xpath('/list/package') as $aXmlPkgObj){
			$aRelease = array(
				'title' => (string)$aXmlPkgObj->title,
				'version'=>array(
					'platform' => Version::fromString((string)$aXmlPkgObj->version->platform),
					'framework' => Version::fromString((string)$aXmlPkgObj->version->framework),
				),
				'status' => (string)$aXmlPkgObj->status,
				'repos' => (string)$aXmlPkgObj->repos,
				'url' => (string)$aXmlPkgObj->url,
			);
			
			$aRelease['url'] = str_replace('${title}',$aRelease['title'],$aRelease['url'] );
			$aRelease['url'] = str_replace('${status}',$aRelease['status'],$aRelease['url'] );
			
			$arrRelease [] = $aRelease ;
		}
		
		$this->view->variables()->set('arrRelease',$arrRelease) ;
		
		$aPlatformVersion = Platform::singleton()->version();
		$this->view->variables()->set('aPlatformVersion',$aPlatformVersion);
		$aFrameworkVersion = Version::fromString( \org\jecat\framework\VERSION );
		$this->view->variables()->set('aFrameworkVersion',$aFrameworkVersion);
	}
	
	public function actionDownload(){
		$sUrl = $this->params['url'];
		
		$sDownloadFolder = Extension::flyweight('coresystem')->dataFolder();
		$sFileName = array_pop( explode('/',$sUrl) );
		
		$sDownloadFilePath = $sDownloadFolder->path().'/'.$sFileName;
		
		try{
			$this->downloadFile($sUrl,$sDownloadFilePath);
			$this->installFile($sDownloadFilePath);
			$this->createMessage(
				Message::success,
				'安装`%s`成功',
				$sUrl
			) ;
		}catch(Exception $e){
			$this->createMessage(Message::error,$e->getMessage(),$e->messageArgvs()) ;
		}
	}
	
	private function downloadFile($sUrl,$sSaveFilePath){
		$aRemoteFile = fopen($sUrl,'rb');
		if(!$aRemoteFile){
			throw new Exception(
				'open remote file failed:`%s`',
				$sUrl
			);
			return false;
		}
		
		$aLocalFile = fopen($sSaveFilePath,'wb');
		if(!$aLocalFile){
			throw new Exception(
				'open local file failed:`%s`',
				$sSaveFilePath
			);
			return false;
		}
		
		while( ! feof($aRemoteFile) ){
			$aBuffer = fread($aRemoteFile,1024*16);
			fwrite($aLocalFile,$aBuffer);
		}
		
		fclose($aRemoteFile);
		fclose($aLocalFile);
		return true;
	}
	
	private function installFile($sFilePath){
		$aZipObj = new \ZipArchive();
		
		$res = $aZipObj->open($sFilePath) ;
		if( TRUE === $res){
			$sExtPath = \org\opencomb\platform\ROOT;
			$aZipObj->extractTo( $sExtPath );
			return TRUE;
		}else{
			throw new Exception(
				"打开zip文件失败：`%s`",
				$sFilePath
			) ;
			return FALSE;
		}
	}
}
