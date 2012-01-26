<?php 
namespace org\opencomb\coresystem\auth ;

use org\jecat\framework\auth\AuthenticationException;
use org\jecat\framework\message\Message;
use org\jecat\framework\auth\IdManager;
use org\opencomb\coresystem\mvc\controller\ControlPanel;

class TestPurview extends ControlPanel
{
	public function createBeanConfig()
	{
		$arrPurview = array() ;
		foreach(PurviewSetting::registeredPurviews() as $sExtName=>$arrPurviewsOfExt)
		{
			foreach($arrPurviewsOfExt as $arrPurviewList)
			{
				foreach($arrPurviewList as $arrPurviewInfo)
				{
					$arrPurview[] = array($arrPurviewInfo['title'],$sExtName.':'.$arrPurviewInfo['name']) ;
				}
			}
		}
		
		$aId = IdManager::singleton()->currentId() ;
		
		return array(
			'view:test' => array(
					'template' => 'TestPurview.html' ,
					'class' => 'form' ,
					
					'widgets' => array(
						'userid' => array(
							'class' => 'text' ,
							'value' => $aId? $aId->userId(): '' ,
						) ,
						'purview' => array(
							'class' => 'select' ,
							'options' => $arrPurview ,
						) ,
						'target' => array(
							'class' => 'text' ,
							'value' => 'NULL' ,
						) ,
					)
			) ,		
		) ;
	}
	
	public function process()
	{
		$this->test->loadWidgets( $this->params ) ;
		
		if( $this->test->isSubmit($this->params) )
		{
			list($sNamespace,$sPurview) = explode(':',$this->test->widget('purview')->value()) ;
			$target = $this->test->widget('target')->value() ;
			if( empty($target) or strtolower($target)=='null' )
			{
				$target = null ;
			}
			
			// -- 指定uid用户 -----------------------------------
			$arrTestLevels = array(
					PurviewQuery::auth_user=>'用户权限' ,
					PurviewQuery::auth_group=>'所属用户组权限' ,
					PurviewQuery::auth_group_bubble=>'下级用户组“冒泡”权限' ,
					PurviewQuery::auth_group_inheritance=>'上级用户组“可继承”权限' ,
					//PurviewQuery::auth_platform_admin=>'平台管理员权限' ,
			) ;
			
			foreach($arrTestLevels as $nLevel=>$sName)
			{
				if( PurviewQuery::singleton()->hasPurview(
					$this->test->widget('userid')->value()
					, $sNamespace, $sPurview, $target, $nLevel
				) )
				{
					$this->test->createMessage(Message::success,"[权限验证等级] %s：通过",$sName) ;
				}
				else
				{
					$this->test->createMessage(Message::forbid,"[权限验证等级] %s：拒绝",$sName) ;
				}
			}
			
			// -- 当前登录用户 -----------------------------------
			try{
				$this->requirePurview($sPurview,$sNamespace,$target) ;
				$this->test->createMessage(Message::success,"当前登录用户权限检查：通过") ;
			}catch(AuthenticationException $e){
				
				$this->test->createMessage(Message::forbid,"当前登录用户权限检查：".$e->messageSentence(),$e->messageArgvs()) ;
			}
		}
			
	}
}
