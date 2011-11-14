<?php
namespace org\opencomb\coresystem\user ;

use jc\db\sql\Insert;
use jc\db\DB;
use jc\lang\Object;

class PurviewManager extends Object
{
	const ignore = '*ignore*' ;
	
	public function addUserPurview($uid,$sExtensionName,$sPurviewName,$target=null,$nBit=1)
	{
		if( $this->purviewRow('user',$uid,$sExtensionName,$sPurviewName,$target) )
		{
			$nBit = (int) $nBit ;
			$sSQL = "update coresystem_purview set bit = bit|{$nBit} "  . $this->sqlWhere('user',$uid,$sExtensionName,$sPurviewName,$target);
			
			return DB::singleton()->execute($sSQL) ;
		}
		
		else 
		{
			$aSql = new Insert('coresystem_purview') ;
			$aSql->setData('type','user') ;
			$aSql->setData('id',$uid) ;
			$aSql->setData('extension',$sExtensionName) ;
			$aSql->setData('name',$sPurviewName) ;
			if($target!==null)
			{
				$aSql->setData('target',$target) ;
			}
			$aSql->setData('bit',$nBit) ;
			
			return DB::singleton()->execute($aSql) ;
		}
	}
	public function removeUserPurview($uid,$sExtensionName,$sPurviewName,$target=null,$nBit=1)
	{
		if( !$aPurviewRecord=$this->purviewRow('user',$uid,$sExtensionName,$sPurviewName,$target) )
		{
			return ;
		}
		
		$nOriBit = (int)$aPurviewRecord->field(0,'bit') ;
		$nNewBit = $nOriBit^((int)$nBit) ;
		
		if( $nNewBit )
		{
			$sSQL = "update coresystem_purview set bit = {$nNewBit} "  . $this->sqlWhere('user',$uid,$sExtensionName,$sPurviewName,$target);
			return DB::singleton()->execute($sSQL) ;
		}
		else 
		{
			$sSQL = "delete from coresystem_purview "  . $this->sqlWhere('user',$uid,$sExtensionName,$sPurviewName,$target);
			return DB::singleton()->execute($sSQL) ;
		}
	}
	public function hasUserPurview($uid,$sExtensionName,$sPurviewName,$target=null,$nBit=1)
	{
		$nBit = (int) $nBit ;
	
		if( !$aPurviewRecord=$this->purviewRow('user',$uid,$sExtensionName,$sPurviewName,$target) )
		{
			return false ;
		}
		
		$nOriBit = (int)$aPurviewRecord->field(0,'bit') ;
		return ($nOriBit & $nBit) == $nBit ;
	}
	public function userPurviews($uid,$sExtensionName=self::ignore,$sPurviewName=self::ignore,$target=self::ignore)
	{
		$uid = (int) $uid ;
		$sSQL = "select * from coresystem_purview where type='user' and id={$uid} " ;
		if( $sExtensionName!==self::ignore )
		{
			$sSQL.= " and extension='".addslashes($sExtensionName)."'" ;
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
			$arrPurviews[$arrPurviewRow['extension']][$arrPurviewRow['name']] = (int)$arrPurviewRow['bit'] ;
		}
		
		return $arrPurviews ;
	} 
	
	protected function purviewRow($type='user',$id,$sExtensionName,$sPurviewName,$target=null)
	{
		$sSQL = "select * from coresystem_purview " . $this->sqlWhere($type,$id,$sExtensionName,$sPurviewName,$target) ;
		$aRecords = DB::singleton()->query($sSQL) ;
		return $aRecords->rowCount()? $aRecords: null ;
	}
	
	protected function sqlWhere($type='user',$id,$sExtensionName,$sPurviewName,$target=null)
	{
		$sSQL = "where type='{$type}'
					and extension='" . addslashes($sExtensionName) . "' 
					and name='" . addslashes($sExtensionName) . "'" ;

		$sSQL.= $target===null? " and target=NULL": (" and target='".addslashes($target)."'") ;
		$sSQL.= " and id='".addslashes($id)."'" ;
		
		return $sSQL ;
	}
}

?>