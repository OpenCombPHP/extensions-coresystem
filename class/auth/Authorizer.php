<?php
namespace org\opencomb\coresystem\auth ;

use org\opencomb\mvc\model\db\orm\Prototype;
use org\opencomb\coresystem\CoreSystem;
use org\jecat\framework\db\sql\Insert;
use org\jecat\framework\db\DB;
use org\jecat\framework\lang\Object;

class Authorizer extends Object
{	
	const user = 'user' ;
	const group = 'group' ;
	
	const ignore = '*ignore*' ;
	
	public function __construct()
	{
		$this->sTablePurview = Prototype::transTableName('purview','coresystem') ;
		$this->sTableUser = Prototype::transTableName('user','coresystem') ;
		$this->sTableGroup = Prototype::transTableName('group','coresystem') ;
		$this->sTableGroupUserLink = Prototype::transTableName('group_user_link','coresystem') ;
	}
	
	public function hasPurview($uid,$sNamespace,$sPurviewName,$target=null)
	{
		// 检查用户组
		if( $this->queryUserGroupPurview($uid,$sNamespace,$sPurviewName,$target) )
		{
			return true ;
		}
		
		// 检查是否从下级用户组冒泡
		if( $this->queryUserGroupFamilyPurview($uid,false,$sNamespace,$sPurviewName,$target) )
		{
			return true ;
		}
				
		// 检查是否从上级用户组继承
		if( $this->queryUserGroupFamilyPurview($uid,true,$sNamespace,$sPurviewName,$target) )
		{
			return true ;
		}
		
		// 检查用户本身是否具备请求的权限
		if( $this->queryUserPurview($uid,$sNamespace,$sPurviewName,$target) )
		{
			return true ;
		}
		
		// 最后检查特殊权限"平台管理员"，该权限可涵盖所有系统中的权限
		if( $sNamespace!='coresystem' or $sPurviewName!=Id::PLATFORM_ADMIN )
		{
			return $this->hasPurview($uid,'coresystem',Id::PLATFORM_ADMIN,$target) ;
		}
		else
		{
			return false ;
		}
	}
	
	public function queryPurviews($id,$type=self::user,$sNamespace=self::ignore,$sPurviewName=self::ignore,$target=self::ignore)
	{
		$sSQL = "select * from `{$this->sTablePurview}` where type='{$type}' and id='{$id}' " ;
		if( $sNamespace!==self::ignore )
		{
			$sSQL.= " and extension='".addslashes($sNamespace)."'" ;
		}
		if( $sPurviewName!==self::ignore )
		{
			$sSQL.= " and name='".addslashes($sPurviewName)."'" ;
		}
		if( $target!==self::ignore )
		{
			$sSQL.= $target===null? " and target=NULL": (" and target='".addslashes($target)."'") ;
		}
	
		$arrPurviews = array() ;
		$aRecords = DB::singleton()->query($sSQL) ;
	
		foreach($aRecords as $arrPurviewRow)
		{
			if($type==self::group)
			{
				$arrPurviewRow['inheritance'] = (bool) $arrPurviewRow['inheritance'] ;
				$arrPurviewRow['bubble'] = (bool) $arrPurviewRow['bubble'] ;
			}
			else
			{
				$arrPurviewRow['inheritance'] = $arrPurviewRow['bubble'] = false ;
			}
			$arrPurviews[$arrPurviewRow['extension']][$arrPurviewRow['name']][$arrPurviewRow['target']] = $arrPurviewRow ;
		}
	
		return $arrPurviews ;
	}
	
	static public function registeredPurviews()
	{
		return self::$arrRegisteredPurviews ;
	}
	static public function queryPurviewTitle($sExtension,$sPurviewName,$target)
	{
		foreach(self::$arrRegisteredPurviews[$sExtension] as &$arrPurviewList)
		{
			foreach($arrPurviewList as &$arrPurview)
			{
				if( $arrPurview['name']==$sPurviewName and $arrPurview['target']==$target )
				{
					return $arrPurview['title'] ;
				}
			}
		}
		
		return $sPurviewName ;
	}
	
	
	
	// -------------------------------------------------------------------
	
	/**
	 * 查询用户 uid 所属的用户组是否拥有指定的权限
	 */
	protected function queryUserPurview($uid,$sNamespace,$sPurviewName,$target=null)
	{
		$uid = addslashes($uid) ;
		$sNamespace = addslashes($sNamespace) ;
		$sPurviewName = addslashes($sPurviewName) ;
		
		$sPurviewWhere = self::sqlPurviewWhere($sNamespace,$sPurviewName,$target) ;
		
		$sSql = "select pur.id
				from `{$this->sTablePurview}` as pur
				where pur.id='{$uid}' and pur.type='user' and {$sPurviewWhere}
				Limit 1 ;" ;
		
		return DB::singleton()->query($sSql)->rowCount()>0 ;
	}
	
	/**
	 * 查询用户 uid 所属的用户组是否拥有指定的权限
	 */
	protected function queryUserGroupPurview($uid,$sNamespace,$sPurviewName,$target=null)
	{
		$uid = addslashes($uid) ;
		$sNamespace = addslashes($sNamespace) ;
		$sPurviewName = addslashes($sPurviewName) ;
		
		$sPurviewWhere = self::sqlPurviewWhere($sNamespace,$sPurviewName,$target) ;
		
		$sSql = "select usrs.gid
				from `{$this->sTableGroupUserLink}` as usrs join `{$this->sTablePurview}` as pur (usrs.gid=pur.id and pur.type='group')
				where usrs.uid='{$uid}' and {$sPurviewWhere}
				Limit 1 ;" ;
		
		return DB::singleton()->query($sSql)->rowCount()>0 ;
	}
	protected function queryUserGroupFamilyPurview($uid,$isParentGroups,$sNamespace,$sPurviewName,$target=null)
	{
		$uid = addslashes($uid) ;
		
		$sFamilyGrpsJoinOn = $isParentGroups?
				"(fmyGrps.lft<grps.lft and fmyGrps.rgt>grps.rgt)" : 
				"(fmyGrps.lft>grps.lft and fmyGrps.rgt<grps.rgt)" ; 
		
		$sPurviewWhere = self::sqlPurviewWhere($sNamespace,$sPurviewName,$target) ;
		$sPurviewWhere.= ($isParentGroups? " and pur.bubble='1'": " and pur.inheritance='1'") ;
		
		$sSql = "select 
				from `{$this->sTableGroupUserLink}` as usrs left join ( 
						`$this->sTableGroup` as grps left join (
							`$this->sTableGroup` as fmyGrps left join
								`{$this->sTablePurview}` as pur on (pur.id=fmyGrps.gid and pur.type='group')
						) on {$sFamilyGrpsJoinOn}
					) on (usrs.gid=grps.gid)
				where usrs.uid='{$uid}' and {$sPurviewWhere}
				Limit 1 ;" ;
		
		return DB::singleton()->query($sSql)->rowCount()>0 ;
	}
	
	static public function sqlPurviewWhere($sNamespace,$sPurviewName,$target=null,$sTableAlias='pur.')
	{
		$sSQL = "{$sTableAlias}extension='" . addslashes($sNamespace) . "' 
					and {$sTableAlias}name='" . addslashes($sPurviewName) . "'" ;
		$sSQL.= $target===null? " and {$sTableAlias}target IS NULL": (" and {$sTableAlias}target='".addslashes($target)."'") ;
		
		return $sSQL ;
	}
	
	private $sPurviewTable ;
	
	static private $arrRegisteredPurviews = array(
				
			'coresystem' => array(									// 扩展 =========

				'系统' => array(								// 分类 ---------
						array(
								'name' => Id::PLATFORM_ADMIN ,	// 权限名称
								'title' => '平台管理员' ,		// 权限标题
								'target' => null ,				// 目标内容id
						) ,
				) ,

				'测试' => array(								// 分类 ---------
						array(
								'name' => 'test-purview1' ,
								'title' => '测试权限1' ,
								'target' => null ,
						) ,
						array(
								'name' => 'test-purview1' ,
								'title' => '测试权限1- targe 20' ,
								'target' => 20 ,
						) ,

						array(
								'name' => 'test-purview2' ,
								'title' => '测试权限2' ,
								'target' => null ,
						) ,
						array(
								'name' => 'test-purview3' ,
								'title' => '测试权限3' ,
								'target' => null ,
						) ,
				) ,
			) ,
	) ;
}

?>