<?php 
namespace org\opencomb\coresystem\auth ;

use org\jecat\framework\auth\AuthenticationException;
use org\jecat\framework\message\Message;
use org\jecat\framework\auth\IdManager;
use org\opencomb\coresystem\mvc\controller\ControlPanel;

class PurviewTester extends ControlPanel
{
	public function createBeanConfig(array & $arrBeanConfig)
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
		
		$arrBeanConfig = array(
			'view' => array(
					'template' => 'auth/PurviewTester.html' ,
					
					'widgets' => array(
						'userid' => array(
							'class' => 'text' ,
							'value' => $aId? $aId->userId(): '' ,
						) ,
						'purview' => array(
							'class' => 'select' ,
							'options' => $arrPurview ,
						) ,
						'purviewName' => array(
							'class' => 'text' ,
						) ,
						'purviewNamespace' => array(
							'class' => 'text' ,
						) ,
						'target' => array(
							'class' => 'text' ,
							'value' => PurviewQuery::all ,
						) ,
						'ignoreTarget' => array(
							'class' => 'checkbox' ,
							'value' => '1' ,
							'title' => '忽略' ,
						) ,
					)
			) ,		
		) ;
	}
	
	public function process()
	{
		$this->view->loadWidgets( $this->params ) ;
		
		if( $this->view->isSubmit($this->params) )
		{
			$sNamespace = $this->view->widget('purviewNamespace')->value() ; 
			$sPurview = $this->view->widget('purviewName')->value() ; 
			
			if($this->view->widget('ignoreTarget')->value())
			{
				$target = PurviewQuery::ignore ;
			}
			else
			{
				$target = $this->view->widget('target')->value()?: PurviewQuery::all ;
			}
			
			$sUid = $this->view->widget('userid')->value() ;
			if(!$sUid)
			{
				$sUid = IdManager::singleton()->currentId()->userId() ;
			}
			
			$this->createMessage(Message::notice,"用户ID: %d",$sUid) ;
			$this->createMessage(Message::notice,"权限: %s; 所属扩展: %s",array($sPurview,$sNamespace)) ;
			
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
					$sUid, $sNamespace, $sPurview, $target, $nLevel
				) )
				{
					$this->view->createMessage(Message::success,"[权限验证等级] %s：通过",$sName) ;
				}
				else
				{
					$this->view->createMessage(Message::forbid,"[权限验证等级] %s：拒绝",$sName) ;
				}
			}
			
			// -- 当前登录用户 -----------------------------------
			try{
				$this->requirePurview($sPurview,$sNamespace,$target) ;
				$this->view->createMessage(Message::success,"当前登录用户权限检查：通过") ;
			}catch(AuthenticationException $e){
				
				$this->view->createMessage(Message::forbid,"当前登录用户权限检查：".$e->messageSentence(),$e->messageArgvs()) ;
			}
		}
			
	}
}
