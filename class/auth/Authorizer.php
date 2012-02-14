<?php
namespace org\opencomb\coresystem\auth ;

use org\jecat\framework\auth\IPermission;
use org\jecat\framework\auth\GroupPermission;
use org\jecat\framework\auth\Authorizer as JcAuthorizer ;

class Authorizer extends JcAuthorizer
{
	public function requirePermission(IPermission $aPermission,$bRestrict=false)
	{
		// 增加平台管理员许可
		if( $aPermission instanceof PurviewPermission )
		{
			$aGrpPerm = new GroupPermission() ;
			$aGrpPerm->add($aPermission,true) ;
			$aGrpPerm->add( PurviewPermission::flyweight(array(Id::PLATFORM_ADMIN, null, 'coresystem'),true), $bRestrict ) ;
			
			$aPermission = $aGrpPerm ;
		}
		
		return parent::requirePermission($aPermission,$bRestrict) ;
	}
}

