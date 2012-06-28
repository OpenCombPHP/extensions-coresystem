<?php
namespace org\opencomb\coresystem\mvc\controller ;

use org\jecat\framework\util\EventManager;
use org\jecat\framework\mvc\view\widget\Widget;
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
				//'widget:mainMenu' => array( 'config'=>'coresystem:widget/control-panel-frame-menu' ) ,
			) ,
    	) ;
    }
    
	static public function registerMenuHandler($fnHandler)
	{
		EventManager::singleton()->registerEventHandle(
			'org\jecat\framework\mvc\view\widget\Widget'
			, Widget::beforeBuildBean
			, $fnHandler
			, null
			, 'coresystem:ControlPanelFrame.html-mainMenu'
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
