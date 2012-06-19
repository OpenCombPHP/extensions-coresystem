<?php
namespace org\opencomb\coresystem\mvc\controller ;

class WelcomeControlPanel extends ControlPanel
{
	public function createBeanConfig()
	{
		return array(
			'view:welcome' => array(
				'template' => 'WelcomeControlPanel.html'
			) ,
		) ;
	}
}
