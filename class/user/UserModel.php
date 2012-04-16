<?php
namespace org\opencomb\coresystem\user ;

use org\jecat\framework\cache\Cache;
use org\jecat\framework\mvc\model\db\orm\Association;
use org\jecat\framework\mvc\model\db\orm\Prototype;
use org\jecat\framework\mvc\model\db\Model;

class UserModel extends Model
{
	/**
	 * @return UserModel
	 */
	static public function byUId($sUId,Cache $aCache=null)
	{
		return self::loadModel('uid',$sUId,$aCache) ;
	}
	/**
	 * @return UserModel
	 */
	static public function byUsername($sUsername)
	{
		return self::loadModel('username',$sUsername,$aCache) ;
	}
	/**
	 * @return UserModel
	 */
	static protected function loadModel($sColumn,$value,Cache $aCache=null)
	{
		if(!$aCache)
		{
			$aCache = Cache::highSpeed() ;
		}
		
		$sKey = '/mvc/model/user/by-'.$sColumn.'/'.$value ;
		if( $aModel = $aCache->item($sKey) ) 
		{
			return $aModel ;
		}
		
		$aPrototype = Prototype::create('coresystem:user','uid')
				->setName('user')
				->createAssociation(Association::hasOne,'coresystem:userinfo','uid','uid')
					->setName('info') 
				->done() ;
		
		$aModel = new self($aPrototype) ;

		if( !$aModel->load($value,$sColumn) )
		{
			return null ;
		}
		
		$aCache->setItem($sKey,$aModel) ;
				
		return $aModel ;
	}
}

