<?php
namespace org\opencomb\coresystem\auth ;

use org\jecat\framework\auth\IPermissionChecker;
use org\jecat\framework\lang\Object;

class PurviewChecker extends Object implements IPermission
{
	const ignore = null ;
	
	public function __construct($sPurviewName=self::ignore,$target=self::ignore,$sNamespace='*')
	{
		// $this->
	}
	
	public function check(IdManager $aIdManager)
	{
		
	}
}

?>