<?php
namespace org\opencomb\coresystem\user ;

use org\jecat\framework\message\Message;
use org\jecat\framework\auth\IdManager;

class SwichId extends UserPanel
{
	public function createBeanConfig()
	{
		return array(
			'title' => '用户切换' ,
			'keywords' => '用户' ,
			'description' => '用户' ,
			'view:swichid' => array(
				'template' => 'SwichId.html' ,
				'class' => 'view' ,
			) ,
		) ;
	}
	
	public function process()
	{
		$this->requireLogined() ;
		
		if(!$this->params->has('uid')){
			$this->messageQueue ()->create ( Message::error, "无效的ID,无法切换用户" );
			return;
		}
		
		if($this->params->has('forward')){
			$sForwardUrl = $this->params->get('forward');
		}elseif(isset($_SERVER['HTTP_REFERER'])){
			$sForwardUrl = $_SERVER['HTTP_REFERER'];
		}else{
			$sForwardUrl = '/';
		}
		
		$aIdManager = IdManager::singleton();
		$aToId = $aIdManager->id($this->params->get('uid'));
		if(!$aToId){
			$this->messageQueue ()->create ( Message::error, "无效的ID,无法切换用户" );
			return;
		}
		
		$aIdManager->setCurrentId($aToId);
		
		$this->swichid->variables ()->set ( 'sForwardUrl', $sForwardUrl );
		
		$this->messageQueue ()->create ( Message::success, "已成功登录%s用户,5秒种后跳转到之前浏览的页面" ,array($aToId->username()));
	}
}