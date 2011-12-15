<?php
namespace org\opencomb\coresystem\system ;

use org\jecat\framework\message\Message;

use org\jecat\framework\setting\Setting;

use org\opencomb\platform\Platform;

use org\opencomb\coresystem\mvc\controller\ControlPanel;

class DebugStatSetting extends ControlPanel
{
	public function createBeanConfig()
	{
		return array(
			'view:view' => array(
				'template' => 'system/DebugStatSetting.html' ,
				'class' => 'form' ,
			)
		) ;
	}
	
	public function process()
	{
		$aSetting = Setting::singleton() ;
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
			"/platform/class" => array(
					'enableClassPathCache' => array(
						'value' => false ,
						'title' => '禁止缓存类路径' ,
					) , 
			)  ,
			"/platform" => array(
					'serialize' => array(
						'value' => false ,
						'title' => '禁止系统序列化' ,
					) ,
			)  ,
			"/platform/debug" => array(
					'stat' => array(
						'value' => true ,
						'title' => '激活调试状态' ,
					) ,
			)  ,
		) ;
	}
}


?>