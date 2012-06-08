<?php
namespace org\opencomb\coresystem\auth ;

use org\jecat\framework\mvc\model\Model;

class Authenticate
{
	static public function encryptPassword(Model $aUserModel,$sUsername,$sPlainPassword)
	{
		return md5( md5(md5($sUsername)) . md5($sPlainPassword) ) ;
	}
}
