<?php
namespace org\opencomb\coresystem\mvc\controller ;

use org\opencomb\coresystem\auth\Authorizer;
use org\jecat\framework\mvc\view\IView;
use org\jecat\framework\mvc\model\db\orm\Prototype;
use org\jecat\framework\auth\IdManager;
use org\opencomb\ext\Extension;
use org\jecat\framework\auth\AuthenticationException;
use org\jecat\framework\mvc\controller\Controller as JcController ;
use org\opencomb\ext\ExtensionManager;
use org\jecat\framework\setting\Setting;
use org\jecat\framework\mvc\view\Webpage;

class Controller extends JcController
{
	public function createBeanConfig()
	{
		// 用自己的类名做为模板文件名创建一个视图
		
		$sClassName = get_class($this) ;
		$arrSlices = explode('\\', $sClassName) ;
		if( count($arrSlices)>3 )		// 去掉前面的3段（org/com,组织名,扩展名）
		{
			$arrSlices = array_slice($arrSlices,3) ; 
		}
		
		return array(
			'view:view' => array(
				'template' => implode('/',$arrSlices).'.html' ,
			)
		) ;
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
     * @see IController::mainRun()
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
    		
    		if( $sMessage = $e->message() )
    		{
    			$aController->viewMain->variables()->set('message',$sMessage) ;
    		}
    		
    		$aController->mainRun() ;
    	}
    }
    
    /**
     * @return org\opencomb\coresystem\auth\Authorizer
     */
    public function authorizer()
    {
    	return Authorizer::singleton() ;
    }
    
    protected function requirePurview($sPurview,$sExtension,$target=null,$sMessage=null,array $arrArgvs=array())
    {
    	if( !$this->authorizer()->hasPurview($this->requireLogined(),$sExtension,$sPurview) )
    	{
    		$this->permissionDenied($sMessage,$arrArgvs) ;
    	}
    }
    
    /**
     * @return org\jecat\framework\auth\IIdentity
     */
    protected function requireLogined($sMessage=null,array $arrArgvs=array()) 
    {
    	if( !$aId=IdManager::singleton()->currentId() )
    	{
    		$this->permissionDenied($sMessage,$arrArgvs) ;
    	}
    	
    	return $aId ;
    }
    
	protected function permissionDenied($sMessage=null,array $arrArgvs=array())
	{
		throw new AuthenticationException($this,$sMessage,$arrArgvs) ;
	}

    public function createFrame()
    {
    	return new FrontFrame($this->params()) ;
    }
    
    public function renderMainView(IView $aMainView)
    {
    	if( $aMainView instanceof Webpage )
    	{
    		$this->setupWebpageHtmlHead($aMainView) ;
    	}
    
    	parent::renderMainView($aMainView) ;
    }
    
    protected function setupWebpageHtmlHead(Webpage $aWebpage)
    {
    	$aSetting = ExtensionManager::singleton()->extension('coresystem')->setting() ;
    		
    	// title
    	$sTitleTemplate = $aSetting->item('/webpage','title-template','%s') ;
    	$aWebpage->setTitle(sprintf($sTitleTemplate,$this->title())) ;
    
    	// description
    	$sTemplate = $aSetting->item('/webpage','description-template','%s') ;
    	$aWebpage->setDescription(sprintf($sTemplate,$this->description())) ;
    
    	// keywords
    	$sTemplate = $aSetting->item('/webpage','keywords-template','%s') ;
    	$aWebpage->setKeywords(sprintf($sTemplate,$this->keywords())) ;
    }
}

?>