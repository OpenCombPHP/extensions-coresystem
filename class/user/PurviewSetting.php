<?php
namespace org\opencomb\coresystem\user ;

use jc\message\Message;

use oc\mvc\controller\Controller;

class PurviewSetting extends Controller
{
	public function createBeanConfig()
	{
		return array(
			'view:setting' => array(
				'template' => 'PurviewSetting.html' ,
				'class' => 'form' ,
				'vars' => array(
					'arrRegisteredPurviews' => array(
						array(
							'name'=>'coresystem' ,
							'title'=>'核心系统' ,
							'purviews' => array(
								array(
									'name' => 'PLATFORM_ADMIN' ,
									'title' => '平台管理员' ,
									'bit' => 1 ,
								) ,
							) ,
						)
					)
				) ,
			)
		) ;
	}
	
	public function process()
	{
		if( !$nUId = $this->aParams->int('uid') )
		{
			$this->viewSetting->hideForm() ;
			$this->viewSetting->createMessage(Message::error,"缺少参数 uid") ;
			return ;
		}
		
		$arrUserPurviews = PurviewManager::singleton()->userPurviews($nUId) ;
		
		// 修改用户权限
		if( $this->viewSetting->isSubmit($this->aParams) )
		{
			$this->modifyUserPurviews($nUId,$arrUserPurviews) ;
		}
		
		// 查询用户的权限 
		$this->loadUserPurviews($nUId,$arrUserPurviews) ;
	}
	
	private function modifyUserPurviews($nUId,&$arrUserPurviews)
	{
	}

	private function loadUserPurviews($nUId,&$arrUserPurviews)
	{
		$aViewVars = $this->viewSetting->variables() ;
		$arrRegisteredPurviews = $aViewVars->get('arrRegisteredPurviews') ;
		
		foreach($arrRegisteredPurviews as &$arrExtension)
		{
			$sExtName = $arrExtension['name'] ;
			foreach($arrExtension['purviews'] as &$arrPurview)
			{
				$sPurviewName = $arrPurview['name'] ;
				if( empty($arrUserPurviews[$sExtName][$sPurviewName]) )
				{
					continue ;
				}
				
				$arrPurview['checked'] = ($arrUserPurviews[$sExtName][$sPurviewName] & $arrPurview['bit']) == $arrPurview['bit'] ;
			}
		}
		
		$aViewVars->set('arrRegisteredPurviews',$arrRegisteredPurviews) ;
	}
}

?>