<?php
namespace org\opencomb\coresystem ;

use jc\auth\DBPurviewManager;
use jc\auth\PurviewManager;
use oc\ext\Extension;

class CoreSystem extends Extension
{
	public function load()
	{
		$aAccessRouter = $this->application()->accessRouter() ;
		$aAccessRouter->addController("org\\opencomb\\coresystem\\user\\Register",'register','') ;
		$aAccessRouter->addController("org\\opencomb\\coresystem\\user\\Login",'login','') ;
		$aAccessRouter->addController("org\\opencomb\\coresystem\\user\\Logout",'logout','') ;
		
		$aAccessRouter->setDefaultController('org\\opencomb\\coresystem\\user\\AvatarManager') ;
		
		// 权限管理器
		PurviewManager::setSingleton( new DBPurviewManager('coresystem_purview') ) ;
	}
}
