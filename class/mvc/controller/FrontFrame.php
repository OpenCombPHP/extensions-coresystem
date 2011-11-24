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