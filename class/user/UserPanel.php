<?php
namespace org\opencomb\coresystem\user ;

use org\opencomb\coresystem\mvc\controller\UserSpace;

use org\opencomb\coresystem\mvc\controller\Controller;
use org\opencomb\platform\ext\ExtensionManager;
use org\jecat\framework\mvc\view\Webpage;

class UserPanel extends UserSpace
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
//     public function createBeanConfig()
//     {
//     	return array(
//     			'view:userpanel' => array(
//     					'template' => 'UserPanel.html'
//     			) ,
//     	) ;
//     }
}

