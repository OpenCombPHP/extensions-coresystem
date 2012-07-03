<?php
namespace org\opencomb\coresystem\mvc\controller ;


class UserSpaceFrame extends FrontFrame
{
	public function createBeanConfig()
	{
		$arrBean = parent::createBeanConfig();
		$arrBean['frameview:userSpaceFrame'] =  array(
				'template' => 'coresystem:UserSpaceFrame.html' ,
				//'widget:mainMenu' => array( 'config'=>'coresystem:widget/user-space-menu' ) ,
		) ;
		return $arrBean;
	}
}
