<?php
namespace org\opencomb\coresystem\system ;

use org\opencomb\coresystem\mvc\controller\ControlPanel;

class ExtensionStoreOpen extends ControlPanel
{
	protected $arrConfig = array(
		'title'=> '扩展中心安装',
	);
	
	public function process()
	{
	    $sUrlHost = $_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
	    $this->view()->variables()->set('sUrlHost',$sUrlHost);
		$this->setCatchOutput(false) ;
	}
}
