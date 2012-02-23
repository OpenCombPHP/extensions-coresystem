<?php
namespace org\opencomb\coresystem\mvc\controller ;

use org\jecat\framework\bean\BeanFactory;
use org\jecat\framework\mvc\view\View;

class UserPanelFrame extends UserSpaceFrame
{	
	public function createBeanConfig()
	{
		$arrBean = parent::createBeanConfig();
		$arrBean['frameview:userPanelFrame'] =  array(
					'template' => 'UserPanelFrame.html' ,
					'widget:mainMenu' => array( 'config'=>'widget/user-panel-frame-menu' ) ,
			) ;
		return $arrBean;
	}
}
?>