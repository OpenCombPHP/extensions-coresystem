<?php
namespace org\opencomb\coresystem\user ;

use jc\auth\PurviewManager;
use jc\bean\BeanFactory;
use jc\db\DB;
use jc\auth\IdManager;
use org\opencomb\coresystem\mvc\controller\ControlPanel;
use jc\message\Message;

class PurviewSetting extends ControlPanel
{
	public function createBeanConfig()
	{
		return array(
			'view:setting' => array(
				'template' => 'PurviewSetting.html' ,
				'class' => 'form' ,
		
		
				'vars' => array(
		
					'arrRegisteredPurviews' => array(
		
						'coresystem' => array(
							'title'=>'核心系统' ,
							'purviews' => array(
								Id::PLATFORM_ADMIN => array(
									Id::PLATFORM_ADMIN_BIT => array(
										'title'=>'平台管理员'
									) ,
								) ,
							) ,
						) ,
						
					) ,
				) ,
				
			) ,
		) ;
	}
	
	public function process()
	{
		// 权限检查
		$aId = $this->requireLogined() ;
		$aId->userId() ;
		if( !$aId->hasPurview('coresystem',Id::PLATFORM_ADMIN,null,Id::PLATFORM_ADMIN_BIT) )
		{
			$this->permissionDenied("缺少访问此网页的权限") ;
		}
		
		// 检查参数
		if( !$nUId = $this->params->int('uid') )
		{
			$this->viewSetting->hideForm() ;
			$this->viewSetting->createMessage(Message::error,"缺少参数 uid") ;
			return ;
		}
		
		$aModel = BeanFactory::singleton()->createBeanByConfig('model/user','coresystem') ;
		$aModel->load($nUId) ;
		if( $aModel->isEmpty() )
		{
			$this->viewSetting->hideForm() ;
			$this->viewSetting->createMessage(Message::error,"uid 为 %d 的用户不存在",$nUId) ;
			return ;
		}
		
		
		// 修改用户权限
		if( $this->viewSetting->isSubmit($this->params) )
		{
			$this->modifyUserPurviews($nUId) ;
		}
		
		// 查询用户的权限 
		$this->loadUserPurviews($nUId) ;
	}
	
	private function modifyUserPurviews($nUId)
	{
		$arrUserPurviews = PurviewManager::singleton()->userPurviews($nUId) ;
		
		if(empty($this->params['purviews']))
		{
			$this->params->purviews = array() ;
		}
		
		$aViewVars = $this->viewSetting->variables() ;
		$arrRegisteredPurviews = $aViewVars->get('arrRegisteredPurviews') ;
	
		foreach($arrRegisteredPurviews as $sExtName=>&$arrExtension)
		{
			foreach($arrExtension['purviews'] as $sPurviewName=>&$arrPurviewBits)
			{				
				foreach($arrPurviewBits as $nBit=>&$arrBit)
				{
					// 未勾选
					if( empty($this->params['purviews'][$sExtName][$sPurviewName][$nBit]) )
					{
						// 用户本来拥有此权限， 取消权限
						if( !empty($arrUserPurviews[$sExtName][$sPurviewName]) and ($arrUserPurviews[$sExtName][$sPurviewName]&$nBit)==$nBit)
						{
							PurviewManager::singleton()->removeUserPurview($nUId,$sExtName,$sPurviewName,null,$nBit) ;
							$this->viewSetting->createMessage(Message::success,"取消了权限：%s", $arrBit['title'] ) ;
						}
					}
					
					// 勾选
					else 
					{
						// 用户本来没有此权限， 增加权限
						if( empty($arrUserPurviews[$sExtName][$sPurviewName]) or ($arrUserPurviews[$sExtName][$sPurviewName]&$nBit)!=$nBit)
						{
							PurviewManager::singleton()->addUserPurview($nUId,$sExtName,$sPurviewName,null,$nBit) ;
							$this->viewSetting->createMessage(Message::success,"增加了权限：%s", $arrBit['title'] ) ;
						}
					}
				}
			}
		}
	}

	private function loadUserPurviews($nUId)
	{
		$arrUserPurviews = PurviewManager::singleton()->userPurviews($nUId) ;
		
		$aViewVars = $this->viewSetting->variables() ;
		$arrRegisteredPurviews = $aViewVars->get('arrRegisteredPurviews') ;
		
		foreach($arrRegisteredPurviews as $sExtName=>&$arrExtension)
		{
			foreach($arrExtension['purviews'] as $sPurviewName=>&$arrPurviewBits)
			{
				if( empty($arrUserPurviews[$sExtName][$sPurviewName]) )
				{
					continue ;
				}
				
				foreach($arrPurviewBits as $nBit=>&$arrBit)
				{
					$arrBit['checked'] = ($arrUserPurviews[$sExtName][$sPurviewName] & $nBit) == $nBit ;
				}
			}
		}
		
		$aViewVars->set('arrRegisteredPurviews',$arrRegisteredPurviews) ;
	}
}

?>