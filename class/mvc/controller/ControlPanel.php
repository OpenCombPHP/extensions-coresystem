<?php
namespace org\opencomb\coresystem\mvc\controller ;

use org\jecat\framework\setting\Setting;
use org\jecat\framework\util\EventManager;
use org\jecat\framework\mvc\view\widget\Widget;
use org\opencomb\coresystem\mvc\controller\Controller;
use org\opencomb\platform\ext\ExtensionManager;
use org\jecat\framework\mvc\view\Webpage;
use org\jecat\framework\mvc\controller\Controller as JcController ;

class ControlPanel extends Controller
{
	const default_title_template = '控制面板 | %s' ;
	const default_keywords = '' ;
	const default_description = '' ;
	
    protected function defaultFrameConfig()
    {
    	return array(
    		'class'=>'webframe' ,
    		'frameview:frameView' => array(
				'template' => 'coresystem:ControlPanelFrame.html' ,
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
    
    public function title()
    {
    	$sTitle = JcController::title() ;
    	$sTitleTpl = Setting::flyweight('coresystem')->item('controlpanel','title_template',self::default_title_template) ;
    	return @sprintf( $sTitleTpl, $sTitle?:'未命名网页' ) ;
    }
    public function description()
    {
    	return JcController::description() . "\r\n" . Setting::flyweight('coresystem')->item('controlpanel','description',self::default_description) ;
    }
    public function keywords($bImplode=true)
    {
    	return JcController::keywords($bImplode) . " " . Setting::flyweight('coresystem')->item('controlpanel','keywords',self::default_keywords) ;
    }
}
