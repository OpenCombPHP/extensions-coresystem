<?php
namespace org\opencomb\coresystem\mvc\controller ;

use org\opencomb\platform\mvc\view\widget\Menu;
use org\opencomb\coresystem\mvc\controller\Controller;
use org\opencomb\platform\ext\ExtensionManager;
use org\jecat\framework\mvc\view\Webpage;

class ControlPanel extends Controller
{
    protected function defaultFrameConfig()
    {
    	return array(
    		'class'=>'webframe' ,
    		'frameview:frameView' => array(
				'template' => 'coresystem:ControlPanelFrame.html' ,
				'widget:mainMenu' => array( 'config'=>'coresystem:widget/control-panel-frame-menu' ) ,
			) ,
    	) ;
    }
    
    static public function registerMenuHandler($fnHandler)
    {
    	// 注册菜单build事件的处理函数
    	Menu::registerBuildHandle(
	    	'org\\jecat\\framework\\mvc\\controller\\WebpageFrame'
	    	, 'org.jecat.framework.mvc.controller.WebpageFrame'
	    	, 'mainMenu'
	    	, $fnHandler
    	) ;
    }
    
    protected function setupWebpageHtmlHead(Webpage $aWebpage)
    {
    	$aSetting = ExtensionManager::singleton()->extension('coresystem')->setting() ;
    
    	// title
    	$sTemplate = $aSetting->item('/webpage','controlpanel-title-template','控制面板 - %s') ;
    	$aWebpage->setTitle(sprintf($sTemplate,$this->title())) ;
    
    	// description
    	$sTemplate = $aSetting->item('/webpage','controlpanel-description-template','%s') ;
    	$aWebpage->setDescription(sprintf($sTemplate,$this->description())) ;
    
    	// keywords
    	$sTemplate = $aSetting->item('/webpage','controlpanel-keywords-template','%s') ;
    	$aWebpage->setKeywords(sprintf($sTemplate,$this->keywords())) ;
    }
}

?>