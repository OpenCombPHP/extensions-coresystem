<?php
namespace org\opencomb\coresystem\user ;

use jc\auth\Id as JcId ;

class Id extends JcId
{
	const PLATFORM_ADMIN = 'PLATFORM_ADMIN' ;
	const PLATFORM_ADMIN_BIT = 1 ;
	
	static public function encryptPassword($sUsername,$sPassword)
	{
		return md5( md5(md5($sUsername)) . md5($sPassword) ) ;
	}

}

?>