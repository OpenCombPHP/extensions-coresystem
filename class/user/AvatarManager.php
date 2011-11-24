<?php
namespace org\opencomb\coresystem\user ;

use org\jecat\framework\mvc\view\DataExchanger;

use org\jecat\framework\message\Message;

use org\opencomb\coresystem\mvc\controller\ControlPanel;

class AvatarManager extends ControlPanel
{
	public function createBeanConfig()
	{
		return array(

			// 模型
			'model:user' => array(
				'conf' => 'model/user' ,
			) ,
			
			// 视图
			'view:avatarManager' => array(
				'template' => 'AvatarManager.html' ,
				'class' => 'form' ,
				'model' => 'user' ,
				
				'widgets' => array(
					array( 'conf'=>'widget/avatar-uploader', 'exchange'=>'info.avatar' ) ,
				) ,
			) ,
		) ;
	}
	
	public function process()
	{		
		$aId = $this->requireLogined() ;
		
		// 加载数据
		if( !$this->modelUser->load($aId->userId()) )
		{
			$this->viewAvatarManager->createMessage(Message::error,"预加载用户信息时遇到错误。") ;
			$this->viewAvatarManager->hideForm() ;
			return ;
		}
		
		$this->viewAvatarManager->exchangeData(DataExchanger::MODEL_TO_WIDGET) ;
		
		
		// 处理用户上传新头像
		if( $this->viewAvatarManager->isSubmit($this->params) )
		{
			$this->viewAvatarManager->loadWidgets($this->params) ;
			
			if( !$this->viewAvatarManager->verifyWidgets() )
			{
				return ;
			}
			
			$this->viewAvatarManager->exchangeData(DataExchanger::WIDGET_TO_MODEL) ;
			
			if( !$this->modelUser->save() )
			{
				$this->viewAvatarManager->createMessage(Message::error,"保存用户信息时遇到错误。") ;
			}
		}
	}
}

?>