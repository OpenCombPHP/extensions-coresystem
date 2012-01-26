<?php
namespace org\opencomb\coresystem\auth ;

use org\jecat\framework\auth\Authorizer as JcAuthorizer ;

class Authorizer extends JcAuthorizer
{
	public function __construct()
	{
		// 增加平台管理员许可
		$this->requirePermission(new PurviewPermission(
				Id::PLATFORM_ADMIN, null, 'coresystem'
		)) ;
	}
	
}

