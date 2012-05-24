<?php
namespace org\opencomb\coresystem\system ;

use org\jecat\framework\verifier\Length;
use org\jecat\framework\lang\Object;
use org\jecat\framework\message\Message;
use org\jecat\framework\lang\oop\ClassLoader;
use org\jecat\framework\mvc\view\widget\menu\Menu;
use org\jecat\framework\mvc\view\View;
use org\jecat\framework\mvc\controller\IController;
use org\jecat\framework\lang\aop\AOP;
use org\opencomb\platform\ext\Extension;
use org\jecat\framework\bean\BeanFactory;
use org\opencomb\frameworktest\aspect;
use org\opencomb\platform\system\PlatformSerializer;
use org\jecat\framework\ui\xhtml\weave\Patch;
use org\jecat\framework\ui\xhtml\weave\WeaveManager;
use org\opencomb\coresystem\mvc\controller\ControlPanel;
use org\opencomb\coresystem\mvc\controller\ControlPanelFrame;
use org\jecat\framework\setting\Setting;

class ExtensionStoreOpen extends ControlPanel
{
	public function createBeanConfig()
	{	
		$this->setCatchOutput(false) ;
		return array(
				'title'=> '扩展中心安装',
				'view:extensionStoreStep'=>array(
						'template'=>'ExtensionStoreOpen.html',
						'class'=>'form',
				)
		);
	}
	
	public function process()
	{
		
	}
}

?>