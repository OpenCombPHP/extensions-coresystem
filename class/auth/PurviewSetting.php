<?php
namespace org\opencomb\coresystem\auth ;

use org\opencomb\coresystem\mvc\controller\Controller;
use org\opencomb\platform\mvc\model\db\orm\Prototype;
use org\opencomb\platform\ext\Extension;
use org\jecat\framework\bean\BeanFactory;
use org\jecat\framework\db\DB;
use org\jecat\framework\auth\IdManager;
use org\opencomb\coresystem\mvc\controller\ControlPanel;
use org\jecat\framework\message\Message;

class PurviewSetting extends ControlPanel
{
	protected $arrConfig = array(
			
			'title'=>'授权设置',
			
			'controller:purviewView' => 'org\\opencomb\\coresystem\\auth\\PurviewView' ,

			'perms' => array(
				// 权限类型的许可
				'perm.purview'=>array(
						'name' => Id::PLATFORM_ADMIN,
				) ,
			) ,
		) ;
	
	public function createBeanConfig(array & $arrConfig)
	{
		$arrConfig['view'] = array(
			'template' => 'coresystem:auth/PurviewSetting.html' ,
			'vars' => array(
				'arrRegisteredPurviews' => self::registeredPurviews()
			) ,
		) ;
	}
	
	public function process()
	{		
		if(!$this->params->string('type'))
		{
			$this->params->set('type',PurviewQuery::user) ;
		}
		
		// 检查参数
		if( !$sId = $this->params->string('id') )
		{
			$this->view->hideForm() ;
			$this->view->createMessage(Message::error,"缺少参数 id") ;
			return ;
		}
		
		// $this->doActions() ;
		
		if( $this->params->string('type')==PurviewQuery::user )
		{
			$aModel = $this->view->setModel('coresystem:user')->model()->hasOne('coresystem:userinfo') ;
		}
		else if( $this->params->string('type')==PurviewQuery::group )
		{
			$aModel = $this->view->setModel('coresystem:group')->model() ;
		}
		else
		{
			$this->view->hideForm() ;
			$this->createMessage(Message::error,"参数 type 无效：%s",$this->params->string('type')) ;
			return ;
		}
		
		$aModel->load($sId) ;
		
		if( !$aModel->rowNum() )
		{
			$this->view->hideForm() ;
			$this->createMessage(Message::error,"参数 id 无效：%s",$sId) ;
			return ;
		}
		
		// 修改权限
		if( $this->params['formname']==='form' )
		{
			$this->modifyPurviews($sId) ;
		}
		
		// 查询权限 
		$this->loadPurviews($sId) ;
		
		// view variables
		$this->view->variables()->set('type',$this->params->string('type')) ;
		if( $this->params->string('type')==PurviewQuery::user )
		{
			$this->view->variables()->set('sPageTitle',"设置用户“{$aModel->username}”的权限") ;
		}
		else
		{	
			$this->view->variables()->set('sPageTitle',"设置用户组“{$aModel->name}”的权限") ;
		}
	}
	
	protected function actionDeleteUnregisterPurview()
	{
		$this->params['addUnregisterPurview']['name'] ;
		if( PurviewAction::singleton()->removePurview( $this->params->string('id')
			, $this->params->string('type')
			, $this->params['purviewNamespace']
			, $this->params['purview']
			, $this->params['target']?:PurviewQuery::ignore
		) )
		{
			$this->view->createMessage(Message::success,"删除了权限 %s:%s[%s]", array(
					$this->params['purviewNamespace']
					, $this->params['purview']
					, $this->params['target']?:'NULL'
			) ) ;
		}
	}
	
	private function modifyPurviews($sId)
	{
		$arrExistsPurviews = PurviewQuery::singleton()->queryPurviews($sId,$this->params->string('type')) ;
					
		if(empty($this->params['purviews']))
		{
			$this->params->purviews = array() ;
		}
		
		$aViewVars = $this->view->variables() ;
		$arrRegisteredPurviews = $aViewVars->get('arrRegisteredPurviews') ;

		// 增加/删除 权限
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
							$this->view->createMessage(Message::success,"取消了权限：%s", $arrPurview['title'] ) ;
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
							$this->view->createMessage(Message::success,"权限变更：%s", $arrPurview['title'] ) ;
						}
					}
				}
			}
		}
		
		if( !empty($this->params['addUnregisterPurview']['name']) )
		{
			if( PurviewAction::singleton()->setPurview( $sId
				, $this->params->string('type')
				, $this->params['addUnregisterPurview']['namespace']
				, $this->params['addUnregisterPurview']['name']
				, $this->params['addUnregisterPurview']['target']?:null
			) )
			{
				$this->view->createMessage(Message::success,"增加了权限 %s:%s[%s]", array(
						$this->params['addUnregisterPurview']['namespace']
						, $this->params['addUnregisterPurview']['name']
						, $this->params['addUnregisterPurview']['target']?:'NULL'
				) ) ;
			}
		}
	}

	private function loadPurviews($sId)
	{
		// 自己直接拥有的权限
		$arrExistsPurviews = PurviewQuery::singleton()->queryPurviews($sId,$this->params->string('type')) ;
		
		// 注册的权限
		$aViewVars = $this->view->variables() ;
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
					
					// 移除
					unset($arrExistsPurviews[$sExtName][$arrPurview['name']]) ;
					if(empty($arrExistsPurviews[$sExtName]))
					{
						unset($arrExistsPurviews[$sExtName]) ;
					}
				}
			}
		}
		
		$aViewVars->set('arrRegisteredPurviews',$arrRegisteredPurviews) ;
		$aViewVars->set('arrUnregisteredPurviews',$arrExistsPurviews) ;
		// print_r($arrExistsPurviews) ;
	}
	
	/////////////////////////////////////////////////////
	
	static public function registeredPurviews()
	{
		return self::$arrRegisteredPurviews ;
	}
	
	static public function queryPurviewTitle($sExtension,$sPurviewName,$target)
	{
		if(!is_array(@self::$arrRegisteredPurviews[$sExtension]))
		{
			return ;
		}
		foreach(self::$arrRegisteredPurviews[$sExtension] as &$arrPurviewList)
		{
			if(!is_array(@$arrPurviewList))
			{
				continue ;
			}
			foreach($arrPurviewList as &$arrPurview)
			{
				if( $arrPurview['name']==$sPurviewName and $arrPurview['target']==$target )
				{
					return $arrPurview['title'] ;
				}
			}
		}
	
		return $sPurviewName ;
	}
	
	
	static private $arrRegisteredPurviews = array(
	
			'coresystem' => array(									// 扩展 =========
	
					'系统' => array(									// 分类 ---------
							array(
									'name' => Id::PLATFORM_ADMIN ,		// 权限名称
									'title' => '平台管理员' ,				// 权限标题
									'target' => null ,					// 目标内容id
							) ,
					) ,
	
					'测试' => array(									// 分类 ---------
							array(
									'name' => 'test-purview1' ,
									'title' => '测试权限1' ,
									'target' => null ,
							) ,
							array(
									'name' => 'test-purview1' ,
									'title' => '测试权限1- targe 20' ,
									'target' => 20 ,
							) ,
	
							array(
									'name' => 'test-purview2' ,
									'title' => '测试权限2' ,
									'target' => null ,
							) ,
							array(
									'name' => 'test-purview3' ,
									'title' => '测试权限3' ,
									'target' => null ,
							) ,
					) ,
			) ,
	) ;
}

