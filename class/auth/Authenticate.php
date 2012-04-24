<?php
namespace org\opencomb\coresystem\auth ;

use org\jecat\framework\mvc\model\IModel;

class Authenticate
{
	static public function encryptPassword(IModel $aUserModel,$sUsername,$sPlainPassword)
	{
		return call_user_func(self::$fnPasswordEncryptFunction,$aUserModel,$sUsername,$sPlainPassword) ;
	}
	
	static public function encryptPasswordFunction(IModel $aUserModel,$sUsername,$sPlainPassword)
	{
		return md5( md5(md5($sUsername)) . md5($sPlainPassword) ) ;
	}
	
	static public function  registerPasswordEncryptFunction($fnFunction)
	{
		self::$fnPasswordEncryptFunction = $fnFunction ;
	}
	
	static private $fnPasswordEncryptFunction = array(__CLASS__,'encryptPasswordFunction') ;
}

