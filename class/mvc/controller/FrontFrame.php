<?php
namespace org\opencomb\coresystem\mvc\controller ;

use org\jecat\framework\bean\BeanFactory;

use org\jecat\framework\mvc\controller\WebpageFrame;
use org\jecat\framework\mvc\view\View;

class FrontFrame extends WebpageFrame
{
	public function __construct()
	{
		parent::__construct() ;
		
		$aFrameView = new View('frameView',"coresystem:FrontFrame.html") ;
		$this->addFrameView($aFrameView) ;
		
		// 菜单
		$aFrameView->addWidget(
			BeanFactory::singleton()->createBeanByConfig('widget/front-frame-menu','coresystem')
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