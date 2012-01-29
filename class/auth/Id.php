<?php
namespace org\opencomb\coresystem\auth ;

use org\jecat\framework\mvc\model\IModel;
use org\jecat\framework\bean\BeanFactory;
use org\jecat\framework\auth\Id as JcId ;

class Id extends JcId
{
	const PLATFORM_ADMIN = 'PLATFORM_ADMIN' ;
	
	static public function encryptPassword(IModel $aUserModel,$sUsername,$sPlainPassword)
	{
		return md5( md5(md5($sUsername)) . md5($sPlainPassword) ) ;
	}

	static public function createModelBeanConfig()
	{
		return array( 'conf' => 'model/user' ) ;
	}
	static public function createModel()
	{
		return BeanFactory::singleton()->createBean(self::createModelBeanConfig(),'coresystem') ;
	}
	
	static public function restoreFromCookie()
	{
		if( !parent::detectCookie() )
		{
			return null ;
		}
		
		return parent::restoreFromCookie(self::createModel()) ;
	}
}

?>