<?php
namespace org\opencomb\coresystem\user ;

use org\jecat\framework\bean\BeanFactory;

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
		// 从享元中查 user model
		if( $aModel = self::flyweight('by-'.$sColumn.'/'.$value,false) )
		{
			return $aModel ;
		}
		
		// 从缓存查 user model
		if(!$aCache)
		{
			$aCache = Cache::highSpeed() ;
		}
		
		$sCacheKey = '/mvc/model/user/by-'.$sColumn.'/'.$value ;
		if( $aModel = $aCache->item($sCacheKey) ) 
		{
			self::setFlyweight($aModel, 'by-uid/'.$aModel->uid) ;
			self::setFlyweight($aModel, 'by-username/'.$aModel->username) ;
			return $aModel ;
		}
		
		$aPrototype = BeanFactory::singleton()->createBean($arrConfig=array(
				'class'=>'prototype',
				'table' => 'coresystem:user' ,
				'key' => 'uid' ,
				'hasOne:info' => array(
						'table' => 'coresystem:userinfo' ,
						'fromkeys' => 'uid' ,
						'tokeys' => 'uid' ,
				) ,
		)) ;
		
		$aModel = new self($aPrototype) ;

		if( !$aModel->load($value,$sColumn) )
		{
			return null ;
		}

		self::setFlyweight($aModel, 'by-uid/'.$aModel->uid) ;
		self::setFlyweight($aModel, 'by-username/'.$aModel->username) ;
		$aCache->setItem($sCacheKey,$aModel) ;
				
		return $aModel ;
	}
}

