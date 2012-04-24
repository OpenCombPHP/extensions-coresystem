<?php
namespace org\opencomb\coresystem\mvc\controller ;

use org\jecat\framework\bean\BeanFactory;
use org\jecat\framework\mvc\controller\WebpageFrame;

class DevelopConsoleFrame extends WebpageFrame
{	
	public function createBeanConfig()
	{
		return array(
			'frameview:frameView' => array(
					
					'template' => 'DevelopConsoleFrame.html' ,
					
					'widget:mainMenu' => array( 'config'=>'widget/develop-console-frame-menu' ) ,
			) ,
		) ;
	}
	
	public function buildBean(array & $arrConfig,$sNamespace='*',\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
	{
		if($sNamespace=='*')
		{
			$sNamespace = $this->application()->extensions()->extensionNameByClass( get_class($this) )?: '*' ;
		}
		return parent::buildBean($arrConfig,$sNamespace) ;
	}
}
