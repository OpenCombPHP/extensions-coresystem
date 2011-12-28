<?php
namespace org\opencomb\coresystem\mvc\controller ;

use org\jecat\framework\bean\BeanFactory;

use org\jecat\framework\mvc\controller\WebpageFrame;
use org\jecat\framework\mvc\view\View;

class FrontFrame extends WebpageFrame
{
	public function createBeanConfig()
	{
		return array(
			'frameview:frameView' => array(
				'template' => 'coresystem:FrontFrame.html' ,
				'widget:mainMenu' => array( 'config'=>'coresystem:widget/front-frame-menu' ) ,
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
?>