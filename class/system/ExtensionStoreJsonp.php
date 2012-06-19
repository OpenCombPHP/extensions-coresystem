<?php
namespace org\opencomb\coresystem\system ;

use org\jecat\framework\verifier\Length;
use org\jecat\framework\util\Version;
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
use org\opencomb\platform\ext\ExtensionManager;

class ExtensionStoreJsonp extends ControlPanel
{
	public function createBeanConfig()
	{	
		$this->setCatchOutput(false) ;
		return array();
	}
	
	public function process()
	{
		$aExtMgr = ExtensionManager::singleton() ;
		$arrExtensionInfo = array();
		foreach($aExtMgr->metainfoIterator() as $item)
		{
			$arrExtensionInfo[$item->name()]=array('name'=>$item->name(),'title'=>$item->title()
													,'compareversion'=>$item->version()->to32Integer()
													,'version'=>$item->version()->toString()
			);
			
		};
		$callback=$_GET['callback'];
		$date = array('name'=>'22');
		$tmp = json_encode($arrExtensionInfo);
		echo $callback.'('.$tmp.')';
		exit() ;
	}
}

?>