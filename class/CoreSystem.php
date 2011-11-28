<?php
namespace org\opencomb\coresystem ;

use org\opencomb\Platform;
use org\jecat\framework\system\AccessRouter;
use org\jecat\framework\auth\DBPurviewManager;
use org\jecat\framework\auth\PurviewManager;
use org\opencomb\ext\Extension;

class CoreSystem extends Extension
{
	public function load()
	{
		$aAccessRouter = AccessRouter::singleton() ;
		$aAccessRouter->addController("org\\opencomb\\coresystem\\user\\Register",'register','') ;
		$aAccessRouter->addController("org\\opencomb\\coresystem\\user\\Login",'login','') ;
		$aAccessRouter->addController("org\\opencomb\\coresystem\\user\\Logout",'logout','') ;
	}
	
	public function active(Platform $aPlatform)
	{
		// 设置权限管理器
		PurviewManager::setSingleton( DBPurviewManager::singleton(true,'coresystem_purview') ) ;
	}
}
