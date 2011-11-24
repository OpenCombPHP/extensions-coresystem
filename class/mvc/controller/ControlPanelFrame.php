<?php
namespace org\opencomb\coresystem\mvc\controller ;

use org\jecat\framework\bean\BeanFactory;
use org\jecat\framework\mvc\controller\WebpageFrame;
use org\jecat\framework\mvc\view\View;

class ControlPanelFrame extends WebpageFrame
{	
	public function createBeanConfig()
	{
		return array(
			'frameview:frameView' => array(
					
					'template' => 'ControlPanelFrame.html' ,
					
					'widget:mainMenu' => array( 'config'=>'widget/control-panel-frame-menu' ) ,
			) ,
		) ;
	}
	
	public function build(array & $arrConfig,$sNamespace='*')
	{
		if($sNamespace=='*')
		{
			$sNamespace = $this->application()->extensions()->extensionNameByClass( get_class($this) )?: '*' ;
		}
		return parent::build($arrConfig,$sNamespace) ;
	}
}

?>