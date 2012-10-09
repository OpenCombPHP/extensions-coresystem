<?php
namespace org\opencomb\coresystem\mvc\controller ;

use org\jecat\framework\util\EventManager;
use org\jecat\framework\mvc\view\widget\Widget;
use org\jecat\framework\util\DataSrc;
use org\opencomb\coresystem\auth\PurviewPermission;
use org\jecat\framework\mvc\view\UIFactory;
use org\opencomb\coresystem\auth\Authorizer;
use org\jecat\framework\mvc\view\IView;
use org\jecat\framework\mvc\model\db\orm\Prototype;
use org\jecat\framework\auth\IdManager;
use org\opencomb\platform\ext\Extension;
use org\jecat\framework\auth\AuthenticationException;
use org\jecat\framework\mvc\controller\Controller as JcController ;
use org\opencomb\platform\ext\ExtensionManager;
use org\jecat\framework\setting\Setting;
use org\jecat\framework\mvc\view\Webpage;
use org\jecat\framework\message\Message;

class Controller extends JcController
{
	const default_title_template = '%s - 蜂巢平台' ;
	const default_keywords = '' ;
	const default_description = '' ;
	
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
     * @see IController::mainRun()
     */
    public function mainRun ()
    {
	    try{
	    	parent::mainRun() ;
    	}
    	catch (AuthenticationException $e)
    	{
    		$msgController = Controller::createBean($arrBeanConfig=array(
    			'title' => '权限拒绝' ,
    			'params' => $this->params ,
    			'view' => array(
					'template'=>'coresystem:auth/PermissionDenied.html' ,
    			) ,
    		));
            $msgController->messageQueue ()->create ( Message::error, "访问权限被拒绝" );
            $msgController->mainRun() ;
    	}
    }
    
    static public function registerMenuHandler($fnHandler)
    {
		EventManager::singleton()->registerEventHandle(
			'org\jecat\framework\mvc\view\widget\Widget'
			, Widget::beforeBuildBean
			, $fnHandler
			, null
			, 'coresystem:FrontFrame.html-mainMenu'
		) ;
    }
    
    protected function requirePurview($sPurview,$sExtension,$target=null,$sDenyMessage=null,array $arrDenyArgvs=array())
    {
    	// 添加权限许可
    	$this->authorizer()->requirePermission(
    			new PurviewPermission($sPurview,$target,$sExtension)
    	) ;
    	
    	$this->checkPermissions($sDenyMessage,$arrDenyArgvs) ;
    }
    
    /**
     * @return org\jecat\framework\auth\IIdentity
     */
    protected function requireLogined($sDenyMessage=null,array $arrDenyArgvs=array()) 
    {
    	if( !$aId=IdManager::singleton()->currentId() )
    	{
    		$this->permissionDenied($sDenyMessage,$arrDenyArgvs) ;
    	}
    	return $aId ;
    }
    
    protected function checkPermissions($sDenyMessage=null,array $arrDenyArgvs=array())
    {
    	if( !$this->authorizer()->check(IdManager::singleton()) )
    	{
    		$this->permissionDenied($sDenyMessage,$arrDenyArgvs) ;
    	}
    }
    
	protected function permissionDenied($sDenyMessage=null,array $arrDenyArgvs=array())
	{
		throw new AuthenticationException($this,$sDenyMessage,$arrDenyArgvs) ;
	}

    protected function defaultFrameConfig()
    {
    	return array(
    		'class'=>'webframe' ,
    		'frameview:frameView' => array(
				'template' => 'coresystem:FrontFrame.html' ,
			) ,
    	) ;
    }
    
    public function renderMainView(IView $aMainView)
    {
    	if( $aMainView instanceof Webpage )
    	{
    		$this->setupWebpageHtmlHead($aMainView) ;
    	}
    
    	parent::renderMainView($aMainView) ;
    }
    
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
        
    public function title()
    {
    	$sTitleTpl = Setting::flyweight('coresystem')->value('/frontframe/title_template',self::default_title_template) ;
    	return @sprintf( $sTitleTpl, parent::title()?:'未命名网页' ) ;
    }
    
    public function description()
    {
    	return parent::description() . "\r\n" . Setting::flyweight('coresystem')->value('/frontframe/description',self::default_description) ;
    }
    
    public function keywords($bImplode=true)
    {
    	return parent::keywords($bImplode) . " " . Setting::flyweight('coresystem')->value('/frontframe/keywords',self::default_keywords) ;
    }
}
