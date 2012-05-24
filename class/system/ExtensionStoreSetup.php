<?php
namespace org\opencomb\extensionstoresetup;

use org\opencomb\platform\mvc\view\widget\Menu;
use org\jecat\framework\lang\aop\AOP;
use org\opencomb\platform\ext\Extension;
use org\jecat\framework\bean\BeanFactory;



use org\opencomb\frameworktest\aspect;
use org\jecat\framework\ui\xhtml\weave\Patch;
use org\jecat\framework\ui\xhtml\weave\WeaveManager;

class ExtensionStoreSetup extends Extension
{
	/**
	 * 载入扩展
	 */
	public function load() {
		// 注册菜单build事件的处理函数
		Menu::registerBuildHandle(
				'org\\opencomb\\coresystem\\mvc\\controller\\ControlPanelFrame'
				, 'frameView'
				, 'mainMenu'
				, array(__CLASS__,'buildControlPanelMenu')
		) ;
		
	}
	
	static public function buildControlPanelMenu(array & $arrConfig)
	{
		// 合并配置数组，增加菜单
		$arrConfig['item:system']['item:platform-manage']['item:extensionstoresetup'] = array(
				'title'=>'扩展中心安装' ,
				'link' => '?c=org.opencomb.extensionstoresetup.ExtensionStoreOpen' ,
				'query' => 'c=org.opencomb.extensionstoresetup.ExtensionStoreOpen' ,
				);
		
	}
}

?>