<?php
namespace org\opencomb\coresystem\system ;

use org\opencomb\platform\service\Service;

use org\opencomb\coresystem\auth\Id;

use org\jecat\framework\message\Message;
use org\jecat\framework\setting\Setting;
use org\opencomb\coresystem\mvc\controller\ControlPanel;

class DebugStatSetting extends ControlPanel
{
	public function createBeanConfig()
	{
		return array(
			'title'=>'Debug状态设置',
			'view:view' => array(
				'template' => 'system/DebugStatSetting.html' ,
				'class' => 'form' ,
			),
			'perms' => array(
					// 权限类型的许可
					'perm.purview'=>array(
							'namespace'=>'coresystem',
							'name' => Id::PLATFORM_ADMIN,
					) ,
				)
		);
	}
	
	public function process() {
		$this->checkPermissions('您没有使用这个功能的权限,无法继续浏览',array()) ;
		
		$aSetting = Service::singleton()->setting() ;
		$arrStats = $this->stats() ;
		
		$bIsSubmiting = $this->view->isSubmit() ;
		
		
		foreach($arrStats as $sPath=>&$arrItemList)
		{
			foreach($arrItemList as $sItemName=>&$arrItem)
			{
				// 加载状态
				$arrItem['setting'] = (bool)$aSetting->item($sPath,$sItemName) ;
				
				// 保存内状态
				if($bIsSubmiting)
				{
					$bSubmitItemValue = empty($this->params['stat'][$sPath][$sItemName])? (!$arrItem['value']): $arrItem['value'] ; 
					
					if($arrItem['setting']!=$bSubmitItemValue)
					{
						$arrItem['setting'] = $bSubmitItemValue ;
						$aSetting->setItem($sPath,$sItemName,$bSubmitItemValue) ;
						
						$this->view->createMessage(Message::success,"系统状态 %s 已经保存",array($arrItem['title'])) ;
					}
				}
				
				$arrItem['checked'] = ($arrItem['setting'] == $arrItem['value']) ;
			}
		}
		
		$this->view->variables()->set('arrStats',$arrStats) ;
	}
	
	public function stats()
	{
		return array(
			/*"/service/class" => array(
					'enableClassPathCache' => array(
						'value' => false ,
						'title' => '禁止缓存类路径' ,
					) , 
			)  ,
			"/service" => array(
					'serialize' => array(
						'value' => false ,
						'title' => '禁止系统序列化' ,
					) ,
			)  ,*/
			"/service/debug" => array(
					'stat' => array(
						'value' => true ,
						'title' => '激活调试状态' ,
					) ,
			)  ,
		) ;
	}
}


?>