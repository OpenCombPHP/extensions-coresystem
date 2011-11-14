<?php
namespace org\opencomb\coresystem ;

use oc\ext\Extension;

class CoreSystem extends Extension
{
	public function load()
	{
		$aAccessRouter = $this->application()->accessRouter() ;
		$aAccessRouter->addController("org\\opencomb\\coresystem\\user\\Register",'register','') ;
		
		$aAccessRouter->setDefaultController('org\\opencomb\\coresystem\\user\\Register') ;
	}
}
