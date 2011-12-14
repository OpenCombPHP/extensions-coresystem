<?php
namespace org\opencomb\coresystem\auth ;

use org\opencomb\mvc\model\db\orm\Prototype;
use org\opencomb\ext\Extension;
use org\opencomb\coresystem\auth\PurviewAction;
use org\opencomb\coresystem\auth\Authorizer;
use org\jecat\framework\bean\BeanFactory;
use org\jecat\framework\db\DB;
use org\jecat\framework\auth\IdManager;
use org\opencomb\coresystem\mvc\controller\ControlPanel;
use org\jecat\framework\message\Message;

class PurviewSetting extends ControlPanel
{
	public function createBeanConfig()
	{
		return array(
				
			'controller:purviewView' => array(
				'class' => 'org\\opencomb\\coresystem\\auth\\PurviewView' ,
			) ,
				
			'view:setting' => array(
				'template' => 'PurviewSetting.html' ,
				'class' => 'form' ,
		
				'vars' => array(
					'arrRegisteredPurviews' => Authorizer::registeredPurviews()
				) ,
				
			) ,
		) ;
	}
	
	
	public function process()
	{
		// 权限检查
		$this->requirePurview(Id::PLATFORM_ADMIN,'coresystem') ;
		
		
		if(!$this->params->string('type'))
		{
			$this->params->set('type',Authorizer::user) ;
		}
		
		// 检查参数
		if( !$sId = $this->params->string('id') )
		{
			$this->viewSetting->hideForm() ;
			$this->viewSetting->createMessage(Message::error,"缺少参数 id") ;
			return ;
		}
		
		if( $this->params->string('type')==Authorizer::user )
		{
			$aModel = BeanFactory::singleton()->createBeanByConfig('model/user','coresystem') ;
		}
		else if( $this->params->string('type')==Authorizer::group )
		{
			$aModel = BeanFactory::singleton()->createBeanByConfig('model/group','coresystem') ;
		}
		else
		{
			$this->viewSetting->hideForm() ;
			$this->viewSetting->createMessage(Message::error,"参数 type 无效：%s",$this->params->string('type')) ;
			return ;
		}
		
		$aModel->load($sId) ;
		if( $aModel->isEmpty() )
		{
			$this->viewSetting->hideForm() ;
			$this->viewSetting->createMessage(Message::error,"参数 id 无效：%s",$sId) ;
			return ;
		}
		
		// 修改权限
		if( $this->viewSetting->isSubmit($this->params) )
		{
			$this->modifyPurviews($sId) ;
		}
		
		// 查询权限 
		$this->loadPurviews($sId) ;
		
		// view variables
		$this->viewSetting->variables()->set('type',$this->params->string('type')) ;
		if( $this->params->string('type')==Authorizer::user )
		{
			$this->viewSetting->variables()->set('sPageTitle',"设置用户“{$aModel->username}”的权限") ;
		}
		else
		{	
			$this->viewSetting->variables()->set('sPageTitle',"设置用户组“{$aModel->name}”的权限") ;
		}
	}
	
	private function modifyPurviews($sId)
	{
		$arrExistsPurviews = Authorizer::singleton()->queryPurviews($sId,$this->params->string('type')) ;
					
		if(empty($this->params['purviews']))
		{
			$this->params->purviews = array() ;
		}
		
		$aViewVars = $this->viewSetting->variables() ;
		$arrRegisteredPurviews = $aViewVars->get('arrRegisteredPurviews') ;

		// 删除权限
		foreach($arrRegisteredPurviews as $sExtName=>&$arrExtension)
		{
			foreach($arrExtension as $sPurviewCategory=>&$arrPurviewList)
			{				
				foreach($arrPurviewList as $arrPurview)
				{
					$sPurviewName = $arrPurview['name'] ;
					$target = $arrPurview['target']?: 'NULL' ;
					
					// 未勾选(删除权限)
					if( empty($this->params['purviews'][$sExtName][$sPurviewName][$target]['checked']) )
					{
						// 用户本来拥有此权限， 取消权限
						if( !empty($arrExistsPurviews[$sExtName][$sPurviewName][$arrPurview['target']]) )
						{
							PurviewAction::singleton()->removePurview($sId,$this->params->string('type'),$sExtName,$sPurviewName,$arrPurview['target']) ;
							$this->viewSetting->createMessage(Message::success,"取消了权限：%s", $arrPurview['title'] ) ;
						}
					}
					
					// 勾选(增加权限)
					else 
					{
						$bInheritance = !empty($this->params['purviews'][$sExtName][$sPurviewName][$target]['checked:inheritance']) ;
						$bBubble = !empty($this->params['purviews'][$sExtName][$sPurviewName][$target]['checked:bubble']) ;
						
						// 用户本来没有此权限， 增加权限
						if( empty($arrExistsPurviews[$sExtName][$sPurviewName][$arrPurview['target']])
								or $arrExistsPurviews[$sExtName][$sPurviewName][$arrPurview['target']]['inheritance']!=$bInheritance
								or $arrExistsPurviews[$sExtName][$sPurviewName][$arrPurview['target']]['bubble']!=$bBubble
						)
						{
							PurviewAction::singleton()->setPurview(
									$sId,$this->params->string('type'),$sExtName,$sPurviewName,$arrPurview['target'], $bInheritance, $bBubble
								) ;
							$this->viewSetting->createMessage(Message::success,"权限变更：%s", $arrPurview['title'] ) ;
						}
					}
				}
			}
		}
	}

	private function loadPurviews($sId)
	{
		// 自己直接拥有的权限
		$arrExistsPurviews = Authorizer::singleton()->queryPurviews($sId,$this->params->string('type')) ;
		
		$aViewVars = $this->viewSetting->variables() ;
		$arrRegisteredPurviews = $aViewVars->get('arrRegisteredPurviews') ;
		
		foreach($arrRegisteredPurviews as $sExtName=>&$arrExtension)
		{			
			foreach($arrExtension as $sPurviewCategory=>&$arrPurviewList)
			{
				foreach($arrPurviewList as &$arrPurview)
				{
					if( !isset($arrExistsPurviews[$sExtName][$arrPurview['name']][$arrPurview['target']]) )
					{
						$arrPurview['checked'] = $arrPurview['checked:inheritance'] = $arrPurview['checked:bubble'] = false ;
					}
					else
					{
						$arrPurview['checked'] = true ;
						$arrPurview['checked:inheritance'] = $arrExistsPurviews[$sExtName][$arrPurview['name']][$arrPurview['target']]['inheritance'] ;
						$arrPurview['checked:bubble'] = $arrExistsPurviews[$sExtName][$arrPurview['name']][$arrPurview['target']]['bubble'] ;
					}
					
					if($arrPurview['target']===null)
					{
						$arrPurview['target'] = 'NULL' ;
					}
				}
			}
		}
		
		$aViewVars->set('arrRegisteredPurviews',$arrRegisteredPurviews) ;
	}
}

?>