<?php
namespace org\opencomb\coresystem\auth ;

use org\opencomb\mvc\model\db\orm\Prototype;
use org\jecat\framework\db\sql\Insert;
use org\jecat\framework\db\DB;
use org\jecat\framework\lang\Object;

class PurviewAction extends Object
{
	public function __construct()
	{
		$this->sTablePurview = Prototype::transTableName('purview','coresystem') ; ;
	}
	
	public function setPurview($id,$sType,$sNamespace,$sPurviewName,$target=null,$bInheritance=false,$bBubble=true)
	{
		if( $sType==Authorizer::group and $this->purviewRow($sType,$id,$sNamespace,$sPurviewName,$target) )
		{
			if($sType==Authorizer::group)
			{
				$sInheritance = "inheritance=" . ($bInheritance?"'1'":"'0'") ;
				$sBubble = ",bubble=" . ($bBubble?"'1'":"'0'") ;
			}
			else
			{
				$sInheritance = $sBubble = '' ;
			}
			
			$sSQL = "update {$this->sTablePurview} as pur set {$sInheritance}{$sBubble} where pur.id='{$id}' and pur.type='{$sType}' and "
						. Authorizer::sqlPurviewWhere($sNamespace,$sPurviewName,$target);
			
			return DB::singleton()->execute($sSQL) ;
		}
		
		else 
		{
			$aSql = new Insert($this->sTablePurview) ;
			$aSql->setData('type',$sType) ;
			$aSql->setData('id',$id) ;
			$aSql->setData('extension',$sNamespace) ;
			$aSql->setData('name',$sPurviewName) ;
			if($target!==null)
			{
				$aSql->setData('target',$target) ;
			}
			if($sType==Authorizer::group)
			{
				$aSql->setData('inheritance',$bInheritance?'1':'0') ;
				$aSql->setData('bubble',$bBubble?'1':'0') ;
			}
			
			return DB::singleton()->execute($aSql) ;
		}
	}
	public function removePurview($id,$sType,$sNamespace,$sPurviewName,$target=null)
	{
		$sSQL = "delete from {$this->sTablePurview} where id='{$id}' and type='{$sType}' and "
						. Authorizer::sqlPurviewWhere($sNamespace,$sPurviewName,$target,'');
		return DB::singleton()->execute($sSQL) ;
	}
	
	protected function purviewRow($type,$id,$sNamespace,$sPurviewName,$target=null)
	{
		$sSQL = "select * from {$this->sTablePurview} as pur where pur.id='{$id}' and pur.type='{$type}' and " . Authorizer::sqlPurviewWhere($sNamespace,$sPurviewName,$target) ;
		$aRecords = DB::singleton()->query($sSQL) ;
		return $aRecords->rowCount()? $aRecords: null ;
	}
	
	private $sTablePurview ;
}

?>