<?php
namespace org\opencomb\coresystem\auth ;

use org\jecat\framework\mvc\model\Model;

use org\jecat\framework\db\sql\Insert;
use org\jecat\framework\db\DB;
use org\jecat\framework\lang\Object;

class PurviewAction extends Object
{
	public function __construct()
	{
		$this->sTablePurview = DB::singleton()->transTableName('coresystem:purview') ; ;
	}
	
	public function setPurview($id,$sType,$sNamespace,$sPurviewName,$target=null,$bInheritance=false,$bBubble=true)
	{
		if( $sType==PurviewQuery::group and $this->purviewRow($sType,$id,$sNamespace,$sPurviewName,$target) )
		{
			if($sType==PurviewQuery::group)
			{
				$sInheritance = "inheritance=" . ($bInheritance?"'1'":"'0'") ;
				$sBubble = ",bubble=" . ($bBubble?"'1'":"'0'") ;
			}
			else
			{
				$sInheritance = $sBubble = '' ;
			}
			
			$sSQL = "update {$this->sTablePurview} as pur set {$sInheritance}{$sBubble} where pur.id='{$id}' and pur.type='{$sType}' and "
						. PurviewQuery::sqlPurviewWhere($sNamespace,$sPurviewName,$target);
			
			return DB::singleton()->execute($sSQL) ;
		}
		
		else 
		{
			$arrData = array(
					'type' => $sType ,
					'id' => $id ,
					'extension' => $sNamespace ,
					'name' => $sPurviewName ,
			) ;
			if($target!==null)
			{
				$arrData['target'] = $target ;
			}
			if($sType==PurviewQuery::group)
			{
				$arrData['inheritance'] = $bInheritance?'1':'0' ;
				$arrData['bubble'] = $bBubble?'1':'0' ;
			}
			
			return Model::create('coresystem:purview')->insert($arrData) ;
		}
	}
	public function removePurview($id,$sType,$sNamespace,$sPurviewName,$target=null)
	{
		$sSQL = "delete from {$this->sTablePurview} where id='{$id}' and type='{$sType}' and "
						. PurviewQuery::sqlPurviewWhere($sNamespace,$sPurviewName,$target,'');
		return DB::singleton()->execute($sSQL) ;
	}
	
	protected function purviewRow($type,$id,$sNamespace,$sPurviewName,$target=null)
	{
		$sSQL = "select * from {$this->sTablePurview} as pur where pur.id='{$id}' and pur.type='{$type}' and " . PurviewQuery::sqlPurviewWhere($sNamespace,$sPurviewName,$target) ;
		$aRecords = DB::singleton()->query($sSQL) ;
		return $aRecords->rowCount()? $aRecords: null ;
	}
	
	private $sTablePurview ;
}

?>