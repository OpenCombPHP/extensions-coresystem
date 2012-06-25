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
		$this->setCatchOutput(false) ;
	}
}
