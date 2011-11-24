<?php
namespace org\opencomb\coresystem ;

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
		
		$aAccessRouter->setDefaultController('org\\opencomb\\coresystem\\user\\AvatarManager') ;
		
		// 权限管理器
		PurviewManager::setSingleton( new DBPurviewManager('coresystem_purview') ) ;
	}
}
