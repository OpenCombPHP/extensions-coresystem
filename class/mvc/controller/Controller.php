<?php
namespace org\opencomb\coresystem\mvc\controller ;

use org\jecat\framework\message\Message;

use org\opencomb\coresystem\auth\PurviewPermission;
use org\jecat\framework\mvc\view\UIFactory;
use org\jecat\framework\mvc\view\IView;
use org\opencomb\platform\ext\Extension;
use org\jecat\framework\auth\AuthenticationException;
use org\opencomb\platform\ext\ExtensionManager;
use org\jecat\framework\setting\Setting;
use org\jecat\framework\mvc\view\Webpage;
use org\jecat\framework\mvc\controller\Controller as JcController;

class Controller extends JcController
{
	public function createBeanConfig()
	{
		// 用自己的类名做为模板文件名创建一个视图
		
		$sTemplateFilename = '' ; 
		$sClassName = get_class($this) ;
		$sExtensionName = ExtensionManager::singleton()->extensionNameByClass( $sClassName ) ;
		
		// 子目录
		$arrSlices = explode('\\', $sClassName) ;
		if( count($arrSlices)>3 )		// 去掉前面的3段（org/com,组织名,扩展名）
		{
			$arrSlices = array_slice($arrSlices,3) ;
			$sFileName = implode('/',$arrSlices).'.html' ;
			
			// 检查模板文件是否存在
			if( UIFactory::singleton()->sourceFileManager()->find($sFileName,$sExtensionName) )
			{
				$sTemplateFilename = $sFileName ;
			}
		}
		
		if(!$sTemplateFilename)
		{
			$sFileName = end($arrSlices).'.html' ;
			
			// 检查模板文件是否存在
			if( UIFactory::singleton()->sourceFileManager()->find($sFileName,$sExtensionName) )
			{
				$sTemplateFilename = $sFileName ;
			}
		}
		
		return $sTemplateFilename? array(
			'view:view' => array( 'template' => $sTemplateFilename , )
		): array() ;
	}
	
    /**
     * properties:
     * 	name				string						名称
     * 	params				array,org\jecat\framework\util\IDataSrc 		参数
     *  model.ooxx			config
     *  view.ooxx			config
     *  controller.ooxx		config
     * 
     * @see org\jecat\framework\bean\IBean::buildBean()
     */
    public function buildBean(array & $arrConfig,$sNamespace='*',\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
    {
    	if($sNamespace=='*')
    	{
    		$sNamespace = $this->application()->extensions()->extensionNameByClass( get_class($this) )?: '*' ;
    	}
    	return parent::buildBean($arrConfig,$sNamespace) ;
    }
    
    /**
     * 
     * @see Controller::mainRun()
     */
    public function mainRun ()
    {
	    try{
	    	parent::mainRun() ;
    	}
    	catch (AuthenticationException $e)
    	{
    		$aController = new PermissionDenied($this->params) ;
    		$this->add($aController) ;

    		$aController->createMessage(Message::forbid,$e->messageSentence(),$e->messageArgvs()) ;
    		
    		$aController->mainRun() ;
    	}
    }
    
    protected function requirePurview($sPurview,$sExtension,$target=null,$sDenyMessage=null,array $arrDenyArgvs=array())
    {
    	// 添加权限许可
    	$this->authorizer()->requirePermission(
    			new PurviewPermission($sPurview,$target,$sExtension)
    	) ;
    	
    	$this->checkPermissions($sDenyMessage,$arrDenyArgvs) ;
    }

    protected function defaultFrameConfig()
    {
    	return array('class'=>'org\\opencomb\\coresystem\\mvc\\controller\\FrontFrame') ;
    }
    
    /*public function renderMainView(IView $aMainView)
    {
    	if( $aMainView instanceof Webpage )
    	{
    		$this->setupWebpageHtmlHead($aMainView) ;
    	}
    
    	parent::renderMainView($aMainView) ;
    }*/
    
    /**
     * @exmaple /配置/访问扩展的配置
     * @forwiki /配置
     * 
     * 设置网页 html head 信息
     * 
     * @param Webpage $aWebpage
     */
    protected function setupWebpageHtmlHead(Webpage $aWebpage)
    {
    	if(!Extension::flyweight('coresystem'))
    	{
    		throw new \Exception() ;
    	}
    	// 通过 Extension::flyweight() 方法取得 扩展coresystem 的享元对象。
    	// 每个激活的扩展，在系统运行时都有一个Extension类的享元对象，该对象负责维护对应扩展的相关信息和状态。
    	// 然后通过 扩展享元对象的setting() 方法取得该扩展的Setting 对象。
    	// Extentsion::setting() 返回的 Setting对象只包含对应扩展的配置信息；
    	// Setting::singleton() 返回的 Setting对象包含全系统的配置信息，各个扩展的配置信息只是全系统配置树结构上的一个分支。
    	$aExtSetting = Extension::flyweight('coresystem')->setting() ;
    	$aServiceSetting = Setting::singleton() ;
    		
    	// 系统缺省的网页title
    	$sTitleTemplate = $aExtSetting->item('/webpage','title-template','%s') ;
    	$this->replaceSettingValue($sTitleTemplate,$aServiceSetting) ;
    	$aWebpage->setTitle(sprintf($sTitleTemplate,$this->title())) ;
    
    	// 系统缺省的网页description
    	$sTemplate = $aExtSetting->item('/webpage','description-template','%s') ;
    	$this->replaceSettingValue($sTemplate,$aServiceSetting) ;
    	$aWebpage->setDescription(sprintf($sTemplate,$this->description())) ;
    
    	// 系统缺省的网页keywords
    	$sTemplate = $aExtSetting->item('/webpage','keywords-template','%s') ;
    	$this->replaceSettingValue($sTemplate,$aServiceSetting) ;
    	$aWebpage->setKeywords(sprintf($sTemplate,$this->keywords())) ;
    }
    
    private function replaceSettingValue(& $sText,Setting $aSetting)
    {
    	$sText = preg_replace_callback('|%%(.+?):(.+?)%%|', function($arrMatches) use ($aSetting){
    		return $aSetting->item($arrMatches[1],$arrMatches[2]) ;
    	}, $sText) ;
    }
}
