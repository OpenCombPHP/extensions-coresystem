<?php
namespace org\opencomb\coresystem\mvc\controller ;

use org\opencomb\coresystem\mvc\controller\Controller;
use org\opencomb\platform\ext\ExtensionManager;
use org\jecat\framework\mvc\view\Webpage;

class ControlPanel extends Controller
{
    protected function defaultFrameConfig()
    {
    	return array('class'=>'org\\opencomb\\coresystem\\mvc\\controller\\UserPanelFrame') ;
    }
    
    protected function setupWebpageHtmlHead(Webpage $aWebpage)
    {
    	$aSetting = ExtensionManager::singleton()->extension('coresystem')->setting() ;
    
    	// title
    	$sTemplate = $aSetting->item('/webpage','userpanel-title-template','用户面板 - %s') ;
    	$aWebpage->setTitle(sprintf($sTemplate,$this->title())) ;
    
    	// description
    	$sTemplate = $aSetting->item('/webpage','userpanel-description-template','%s') ;
    	$aWebpage->setDescription(sprintf($sTemplate,$this->description())) ;
    
    	// keywords
    	$sTemplate = $aSetting->item('/webpage','userpanel-keywords-template','%s') ;
    	$aWebpage->setKeywords(sprintf($sTemplate,$this->keywords())) ;
    }
}

?>