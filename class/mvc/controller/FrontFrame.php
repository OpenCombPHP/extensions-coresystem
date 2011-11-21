<?php
namespace org\opencomb\coresystem\mvc\controller ;

use jc\bean\BeanFactory;

use jc\mvc\controller\WebpageFrame;
use jc\mvc\view\View;

class FrontFrame extends WebpageFrame
{
	public function __construct()
	{
		parent::__construct() ;
		
		$aFrameView = new View('frameView',"coresystem:FrontFrame.html") ;
		$this->addFrameView($aFrameView) ;
		
		// 菜单
		$aFrameView->addWidget(
			BeanFactory::singleton()->createBeanByConfig('widget/frame-menu','coresystem')
		) ;
	}
	
	
}

?>