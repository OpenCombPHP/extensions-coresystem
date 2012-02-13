<?php
namespace org\opencomb\coresystem ;

use org\jecat\framework\bean\BeanFactory;

use org\opencomb\coresystem\auth\Id;
use org\jecat\framework\auth\IdManager;
use org\opencomb\platform\Platform;
use org\jecat\framework\system\AccessRouter;
use org\opencomb\platform\ext\Extension;

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
		
		// bean class alias
		BeanFactory::singleton()->registerBeanClass("org\\opencomb\\coresystem\\auth\\Authorizer",'authorizer') ;
		BeanFactory::singleton()->registerBeanClass("org\\opencomb\\coresystem\\auth\\PurviewPermission",'perm.purview') ;
		BeanFactory::singleton()->registerBeanClass("org\\opencomb\\coresystem\\widget\\NameCard",'namecard') ;
	}
	
	public function active(Platform $aPlatform)
	{
		// 从 cookie 中恢复 id
		if( !IdManager::singleton()->currentId() )
		{
			if( $aId = Id::restoreFromCookie() )
			{
				IdManager::singleton()->addId($aId) ;
			}
		}
	}
}
