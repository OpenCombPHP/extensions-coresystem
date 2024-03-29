<?php
namespace org\opencomb\coresystem\mvc\controller ;

use org\opencomb\coresystem\mvc\controller\Controller;
use org\opencomb\platform\ext\ExtensionManager;
use org\jecat\framework\mvc\view\Webpage;

class ControlPanel extends Controller
{
    protected function defaultFrameConfig()
    {
    	return array('class'=>'org\\opencomb\\coresystem\\mvc\\controller\\ControlPanelFrame') ;
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
