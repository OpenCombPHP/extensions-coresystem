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
use org\jecat\framework\setting\Setting;

class SystemUpgrade extends ControlPanel{
	protected $arrConfig = array(
			'title'=>'系统升级',
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
				$aRelease['url'] = str_replace('${baseurl}',dirname($sXmlUrl),$aRelease['url'] );
				
				$aRelease['version']['extension'] = array() ;
				foreach( $aXmlPkgObj->version->extension->children() as $key => $aXmlExtObj ){
					$aRelease['version']['extension'][ $key ] = Version::fromString((string)$aXmlExtObj);
				}
				
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
	
	public function form(){
		$sTitle = $this->params['title'];
		$sUrl = urldecode($this->params['url']);
		
		$sDownloadFolder = Extension::flyweight('coresystem')->dataFolder();
		$sFileName = array_pop( explode('/',$sUrl) );
		
		$sDownloadFilePath = $sDownloadFolder->path().'/'.$sFileName;
		
		try{
			$this->createMessage(
				Message::notice,
				'开始安装版本`%s`，JeCat框架版本:`%s`,蜂巢平台版本:`%s`',
				array(
					$sTitle,
					$this->arrRelease[$sTitle]['version']['framework'],
					$this->arrRelease[$sTitle]['version']['platform'],
				)
			);
			$this->checkFilePermission($this->arrRelease[$sTitle]);
			$this->downloadFile($sUrl,$sDownloadFilePath);
			$this->installFile($sDownloadFilePath);
			$this->updateVersion(
				$this->arrRelease[$sTitle]['version']['framework'],
				$this->arrRelease[$sTitle]['version']['platform']
			);
			
			foreach( $this->arrRelease[ $sTitle ]['version']['extension'] as $key => $aVersion ){
				$this->updateExtVer( $key , $aVersion );
			}
			$this->createMessage(
				Message::success,
				'安装`%s`成功',
				$sTitle
			) ;
		}catch(Exception $e){
			$this->createMessage(Message::error,$e->getMessage(),$e->messageArgvs()) ;
		}
	}
	
	private function checkFilePermission( array $aRelease ){
		// loader
		$sRootPath = \org\opencomb\platform\ROOT;
		$aDir = opendir($sRootPath);
		while($aDirObj = readdir($aDir) ){
			if( is_file($aDirObj) ){
				$sFilePath = $sRootPath.'/'.$aDirObj ;
				if(!is_writable( $sFilePath ) ){
					throw new Exception(
						'%s文件没有写入权限，无法安装新系统',
						$sFilePath
					);
				}
			}
		}
		
		// platform and framework
		foreach( array('platform','framework') as $str){
			$sInstallPath = $sRootPath.'/'.$str.'/'.$aRelease['version'][$str];
			if( file_exists( $sInstallPath ) ){
				throw new Exception(
					'%s已存在，无法安装新系统',
					$sInstallPath
				);
			}
		}
		
		// extensions
		foreach( $aRelease['version']['extension'] as $sExtName => $aExtVersion){
			$sExtPath = $sRootPath.'/extensions/'.$sExtName.'/'.$aExtVersion;
			if( file_exists( $sExtPath ) ){
				throw new Exception(
					'%s已存在，无法安装新系统',
					$sExtPath
				);
			}
		}
		return true;
	}
	
	private function downloadFile($sUrl,$sSaveFilePath){
		@$aRemoteFile = fopen($sUrl,'r');
		if(!$aRemoteFile){
			throw new Exception(
				'请求安装文件失败:`%s`',
				$sUrl
			);
			return false;
		}
		
		$aLocalFile = fopen($sSaveFilePath,'w');
		if(!$aLocalFile){
			throw new Exception(
				'打开保存文件失败:`%s`',
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
			$r = $aZipObj->extractTo( $sExtPath );
			if( $r !== true ){
				throw new Exception(
					'解压缩失败:`%s`,请检查framework/目录,platform/目录,extensions/目录的权限',
					$sExtPath
				);
				return false;
			}
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
			throw new Exception('无法写入文件: '.$sServiceSettingFile) ;
		}
		
		return true;
	}
	
	private function updateExtVer($sExtName , Version $aVer ){
		$arrExtList = Setting::singleton()->item('/extensions','installeds') ;
		foreach($arrExtList as &$sExtPath){
			if( self::isStartWith( $sExtPath , $sExtName.'/' ) ){
				$sExtPath = $sExtName.'/'.$aVer->toString(true);
				break;
			}
		}
		Setting::singleton()->setItem('/extensions','installeds',$arrExtList) ;
	}
	
	static private function isStartWith($sLong , $sShort){
		return substr($sLong,0,strlen($sShort)) === $sShort ;
	}
	
	private $arrRelease = array();
}
