<?php
namespace org\opencomb\coresystem ;

use org\opencomb\platform\service\Service;
use org\opencomb\platform\service\ServiceSerializer;
use org\jecat\framework\bean\BeanFactory;
use org\opencomb\coresystem\auth\Id;
use org\jecat\framework\auth\IdManager;
use org\opencomb\platform\Platform;
use org\jecat\framework\system\AccessRouter;
use org\opencomb\platform\ext\Extension;
use org\opencomb\coresystem\lib\LibManager ;
use org\jecat\framework\ui\xhtml\parsers\ParserStateTag;
use org\jecat\framework\ui\xhtml\UIFactory ;
use org\jecat\framework\mvc\view\UIFactory as MvcUIFactory ;

class CoreSystem extends Extension
{
	public function load()
	{
		// controller class alias
		$aAccessRouter = AccessRouter::singleton() ;
		$aAccessRouter->addController("org\\opencomb\\coresystem\\user\\Register",'register','') ;
		$aAccessRouter->addController("org\\opencomb\\coresystem\\user\\Login",'login','') ;
		$aAccessRouter->addController("org\\opencomb\\coresystem\\user\\Logout",'logout','') ;
		$aAccessRouter->addController("org\\opencomb\\coresystem\\mvc\\controller\\WelcomeControlPanel",'control.panel','') ;
		$aAccessRouter->addController("org\\opencomb\\coresystem\\user\\UserPanel",'user.panel','') ;
		
		$aAccessRouter->setDefaultController("org\\opencomb\\coresystem\\mvc\\controller\\WelcomeControlPanel") ;
		
		// bean class alias
		BeanFactory::singleton()->registerBeanClass("org\\opencomb\\coresystem\\auth\\Authorizer",'authorizer') ;
		BeanFactory::singleton()->registerBeanClass("org\\opencomb\\coresystem\\auth\\PurviewPermission",'perm.purview') ;
		BeanFactory::singleton()->registerBeanClass("org\\opencomb\\coresystem\\auth\\PurviewPermission",'purview') ;
		BeanFactory::singleton()->registerBeanClass("org\\opencomb\\coresystem\\namecard\\NameCard",'namecard') ;
		
		/////////////////////////////////////////////////////////////////////////
		// 注册前端库

		// jquery
		LibManager::singleton()->registerLibrary('jquery','1.7.1','coresystem:jquery-1.7.1.js',null,null,true) ;
		
		// jquery.ui
		LibManager::singleton()->registerLibrary('jquery.ui','1.8.16'
				, 'coresystem:jquery.ui/jquery-ui-1.8.16.full.min.js'
				, 'coresystem:jquery.ui/jquery-ui-1.8.16.full.css'
				, 'jquery', true
		) ;
		
		// jquery.progressbar
		LibManager::singleton()->registerLibrary('jquery.progressbar','*'
				// js
				, array(
					'coresystem:jquery.progressbar.min.js' ,
				)
				// css
				, array(
				)
				, array('jquery'), true
		) ;
		
		// --------------------------
		// 提供给系统序列化
		ServiceSerializer::singleton()->addSystemObject(LibManager::singleton()) ;
	}
	
	public function active(Service $aService)
	{
		// 注册 ui
		$this->registerLibNode() ;

		// 从 cookie 中恢复 id
		if( !IdManager::singleton()->currentId() )
		{
			if( $aId = Id::restoreFromCookie() )
			{
				IdManager::singleton()->addId($aId) ;
			}
		}
	}
	
	private function registerLibNode()
	{
		ParserStateTag::singleton()->addTagNames('lib') ;

		UIFactory::singleton()->compilerManager()->compilerByName('org\\jecat\\framework\\ui\xhtml\\Node')->setSubCompiler('lib',__NAMESPACE__.'\\lib\\LibCompiler') ;
		MvcUIFactory::singleton()->compilerManager()->compilerByName('org\\jecat\\framework\\ui\xhtml\\Node')->setSubCompiler('lib',__NAMESPACE__.'\\lib\\LibCompiler') ;
		
		// 重新计算 ui 的编译策略签名
		UIFactory::singleton()->calculateCompileStrategySignture() ;
		MvcUIFactory::singleton()->calculateCompileStrategySignture() ;
	}
}

