<?php
namespace org\opencomb\coresystem\auth ;

use org\jecat\framework\bean\BeanFactory;
use org\jecat\framework\auth\IdManager;
use org\jecat\framework\auth\PermissionBase;

class PurviewPermission extends PermissionBase
{
	public function __construct($sPurviewName=PurviewQuery::ignore,$target=PurviewQuery::ignore,$sNamespace='*')
	{
		$this->sPurviewName = $sPurviewName ;
		$this->sPurviewNamespace = $sNamespace ;
		$this->purviewTarget = $target ;
	}
	
	public function name()
	{
		return $this->sPurviewName ;
	}
	public function ns()
	{
		return $this->sPurviewNamespace ;
	}
	public function target()
	{
		return $this->purviewTarget ;
	}
	
	public function check(IdManager $aIdManager)
	{
		if( !$aId = $aIdManager->currentId() )
		{
			return false ;
		}

		return PurviewQuery::singleton()->hasPurview(
				$aId->userId()
				, $this->sPurviewNamespace
				, $this->sPurviewName
				, $this->purviewTarget
				, $this->nQueryFlag
		) ;
	}
	
	public function buildBean(array & $arrConfig,$sNamespace='*',BeanFactory $aBeanFactory=null)
	{
		// purview name
		if(!empty($arrConfig['name']))
		{
			$this->sPurviewName = $arrConfig['name'] ;
		}
		
		// purview namespace
		$this->sPurviewNamespace = empty($arrConfig['namespace'])? $sNamespace: $arrConfig['namespace'] ;
		
		// purview target
		if(!empty($arrConfig['target']))
		{
			$this->purviewTarget = $arrConfig['target'] ;
		}
		
		// ignore group --------
		if(!empty($arrConfig['ignore.group']))
		{
			$this->nQueryFlag = $this->nQueryFlag&~PurviewQuery::auth_group ;
		}
		if(!empty($arrConfig['ignore.group.bubble']))
		{
			$this->nQueryFlag = $this->nQueryFlag&~PurviewQuery::auth_group_bubble ;
		}
		if(!empty($arrConfig['ignore.group.inheritance']))
		{
			$this->nQueryFlag = $this->nQueryFlag&~PurviewQuery::auth_group_inheritance ;
		}
		
		// purview flag 
		// flag属性优于 ignore.group属性
		if(!empty($arrConfig['flag']))
		{
			$this->nQueryFlag = (int)$arrConfig['flag'] ;
		}
		
		parent::buildBean($arrConfig) ;
	}
	
	private $sPurviewName ;
	private $sPurviewNamespace ;
	private $purviewTarget ;
	private $nQueryFlag = PurviewQuery::auth_default ;
}

