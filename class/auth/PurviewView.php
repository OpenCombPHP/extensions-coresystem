<?php
namespace org\opencomb\coresystem\auth ;

use org\jecat\framework\bean\BeanFactory;
use org\jecat\framework\db\DB;
use org\opencomb\coresystem\mvc\controller\ControlPanel;
use org\jecat\framework\message\Message;

class PurviewView extends ControlPanel
{
	public function createBeanConfig()
	{
		return array(
			'title'=>'授权查看',
			'view:purview' => array(
				'class'=>'form' ,
				'template' => 'PurviewView.html' ,
				'vars' => array(
						'purviews' => array() ,
						'selfPurviews' => array('*'=>array()) ,
						'childGroupPurviews' => array() ,
						'parentGroupPurviews' => array() ,
				) ,
			) ,
		) ;
	}
	
	public function process()
	{
		//权限
		$this->requirePurview(Id::PLATFORM_ADMIN,'coresystem',PurviewQuery::ignore,'您没有这个功能的权限,无法继续浏览');
		
		if(!$this->params->string('type'))
		{
			$this->params->set('type',PurviewQuery::user) ;
		}
		
		// 检查参数
		if( !$sId = $this->params->string('id') )
		{
			$this->purview->hideForm() ;
			$this->purview->createMessage(Message::error,"缺少参数 id") ;
			return ;
		}
		
		if( $this->params->string('type')==PurviewQuery::user )
		{
			$aModel = BeanFactory::singleton()->createBeanByConfig('model/user','coresystem') ;
		}
		else if( $this->params->string('type')==PurviewQuery::group )
		{
			$aModel = BeanFactory::singleton()->createBeanByConfig('model/group','coresystem') ;
		}
		else
		{
			$this->purview->hideForm() ;
			$this->purview->createMessage(Message::error,"参数 type 无效：%s",$this->params->string('type')) ;
			return ;
		}
		
		$aModel->load($sId) ;
		if( $aModel->isEmpty() )
		{
			$this->purview->hideForm() ;
			$this->purview->createMessage(Message::error,"参数 id 无效：%s",$sId) ;
			return ;
		}
				
		// 查看"用户"的权限
		if( $this->params->string('type')=='user' )
		{
			// 用户所拥有的权限
			$sSql = "select * from coresystem:purview where id='{$sId}' and type='user' ;" ;
			foreach(DB::singleton()->query($sSql) as $arrPurview)
			{
				$this->putinFoundPurview('selfPurviews','*',$arrPurview) ;
			}
			
			// 所属用户组拥有的权限
			$sSql = "select grp.gid,grp.name,grp.lft,grp.rgt from coresystem:group_user_link as lnk left join coresystem:group as grp on (lnk.gid=grp.gid) where lnk.uid='{$sId}' ;" ;
			foreach(DB::singleton()->query($sSql) as $arrGroup)
			{
				// 该分组直接拥有的权限
				$sSql = "select * from coresystem:purview where id='{$arrGroup['gid']}' and type='group' ;" ;
				foreach(DB::singleton()->query($sSql) as $arrPurview)
				{
					$this->putinFoundPurview('parentGroupPurviews',$arrGroup['name'],$arrPurview) ;
				}
				
				// 分组从家族树中获得的权限
				$this->loadGroupsFamilyPurviews($arrGroup['lft'],$arrGroup['rgt']) ;
			}
		}
		// 查看"用户组"的权限
		else
		{
			// 该用户组直接拥有的权限
			$sSql = "select * from coresystem:purview where id='{$sId}' and type='group' ;" ;
			foreach(DB::singleton()->query($sSql) as $arrPurview)
			{
				$this->putinFoundPurview('selfPurviews','*',$arrPurview) ;
			}
				
			// 从用户组家族树中获得的权限
			$this->loadGroupsFamilyPurviews($aModel->lft,$aModel->rgt) ;
		}		
	}
	
	
	private function loadGroupsFamilyPurviews($nGrpLft,$nGrpRgt)
	{		
		// 所有从上级分类继承到的权限
		$sSql = "select pur.*, grp.name as gname, grp.gid from coresystem:group as grp left join coresystem:purview as pur on (grp.gid=pur.id and pur.type='group') where grp.lft<{$nGrpLft} and grp.rgt>{$nGrpRgt} and pur.inheritance='1'
						group by pur.name, pur.target
						order by grp.lft desc;" ;
		foreach(DB::singleton()->query($sSql) as $arrPurview)
		{
			$this->putinFoundPurview('parentGroupPurviews',$arrPurview['gname'],$arrPurview) ;
		}
		
		// 所有从下级分类冒泡得到的权限
		$sSql = "select pur.*, grp.name as gname, grp.gid from coresystem:group as grp left join coresystem:purview as pur on (grp.gid=pur.id and pur.type='group') where grp.lft>{$nGrpLft} and grp.rgt<{$nGrpRgt} and pur.bubble='1'
						group by pur.name, pur.target
						order by grp.lft asc;" ; 
		foreach(DB::singleton()->query($sSql) as $arrPurview)
		{
			$this->putinFoundPurview('childGroupPurviews',$arrPurview['gname'],$arrPurview) ;
		}
	}
	
	private function putinFoundPurview($sSource,$sGroupName,&$arrPurview)
	{
		$arrAllPurviews =& $this->purview->variables()->getRef('purviews') ;
		
		if( !empty($arrAllPurviews[$arrPurview['extension']][$arrPurview['name']][$arrPurview['target']]) )
		{
			return ;
		}
		
		$arrPurviews =& $this->purview->variables()->getRef($sSource) ;
		$arrPurviews[$sGroupName][] = $arrPurview ;
	}
}
