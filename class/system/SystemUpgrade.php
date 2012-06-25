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
use org\opencomb\platform\service\Service;

class SystemUpgrade extends ControlPanel{
	protected $arrConfig = array(
			'title'=>'系统重建',
			// 配置许可
			'perms' => array(
				// 权限类型的许可
				'perm.purview'=>array(
					'name' => Id::PLATFORM_ADMIN		// 要求管理员权限
				) ,
			) ,
	);
	
	public function process(){
		$this->checkPermissions() ;
		
		$aSetting = Extension::flyweight('coresystem')->setting();
		$sXmlUrl = $aSetting->item('/systemupgrade','xmlUrl','http://release.opencomb.com/releases.xml');
		
		$sContent = @file_get_contents($sXmlUrl);
		
		if( false === $sContent){
			$this->createMessage(
				Message::error,
				'获取release文件失败：`%s`',
				$sXmlUrl
			);
		}else{
			$aXmlObj = simplexml_load_string( $sContent );
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
				
				$this->arrRelease [ $aRelease['title'] ] = $aRelease ;
			}
		}
		
		$this->doActions();
		
		$this->view->variables()->set('arrRelease',$this->arrRelease) ;
		
		$aPlatformVersion = Platform::singleton()->version();
		$this->view->variables()->set('aPlatformVersion',$aPlatformVersion);
		$aFrameworkVersion = Version::fromString( \org\jecat\framework\VERSION );
		$this->view->variables()->set('aFrameworkVersion',$aFrameworkVersion);
	}
	
	public function actionDownload(){
		$sTitle = $this->params['title'];
		$sUrl = $this->params['url'];
		
		$sDownloadFolder = Extension::flyweight('coresystem')->dataFolder();
		$sFileName = array_pop( explode('/',$sUrl) );
		
		$sDownloadFilePath = $sDownloadFolder->path().'/'.$sFileName;
		
		try{
			$this->createMessage(
				Message::notice,
				'framework:`%s`,platform:`%s`',
				array(
					$this->arrRelease[$sTitle]['version']['framework'],
					$this->arrRelease[$sTitle]['version']['platform'],
				)
			);
			$this->downloadFile($sUrl,$sDownloadFilePath);
			$this->installFile($sDownloadFilePath);
			$this->updateVersion(
				$this->arrRelease[$sTitle]['version']['framework'],
				$this->arrRelease[$sTitle]['version']['platform']
			);
			$this->createMessage(
				Message::success,
				'安装`%s`成功',
				$sTitle
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
	
	private function updateVersion(Version $aFV,Version $aPV){
		$sServiceSettingFile = \org\opencomb\platform\SERVICES_FOLDER.'/settings.inc.php' ;
		
		$arrServiceSettings = include $sServiceSettingFile ;
		
		$sServiceName = Service::singleton()->serviceName();
		
		$arrServiceSettings [ $sServiceName ] ['framework_version'] = $aFV->toString();
		$arrServiceSettings [ $sServiceName ] ['platform_version'] = $aPV->toString();
		
		if( !file_put_contents($sServiceSettingFile,'<?php return $arrServiceSettings = '.var_export($arrServiceSettings,true).';') )
		{
			throw new \Exception('can not write file: '.$sServiceSettingFile) ;
		}
		
		return true;
	}
	
	private $arrRelease = array();
}
