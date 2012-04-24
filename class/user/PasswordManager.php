<?php
namespace org\opencomb\coresystem\user ;

use org\opencomb\coresystem\auth\Authenticate;
use org\jecat\framework\message\Message;

class PasswordManager extends UserPanel
{
	public function createBeanConfig()
	{
		return array(
			'title'=>'修改密码',
// 			// 模型
// 			'model:user' => array(
// 				'conf' => 'model/user' ,
// 			) ,
			
			// 视图
			'view:passwordManager' => array(
				'template' => 'PasswordManager.html' ,
				'class' => 'form' ,
				
				'widgets' => array(
					array( 
						'id' => 'oldPassword' ,
						'class' => 'text' ,
						'type' => 'password' ,
						'title' => '旧密码' ,
						'verifier:length'=>array('min'=>6,'max'=>255),
					) ,
					array( 
						'id' => 'newPassword' ,
						'class' => 'text' ,
						'type' => 'password' ,
						'title' => '新密码' ,
						'verifier:length'=>array('min'=>6,'max'=>255),
					) ,
					array( 
						'id' => 'newPasswordConfirm' ,
						'class' => 'text' ,
						'type' => 'password' ,
						'title' => '确认新密码' ,
						'verifier:length'=>array('min'=>6,'max'=>255),
					) ,
				) ,
			) ,
		) ;
	}
	
	public function process()
	{
		$aId = $this->requireLogined() ;
		
		if ($this->viewPasswordManager->isSubmit ( $this->params )) //前面定义了名为article的视图,之后就可以用$this->viewArticle来取得这个视图.控制器把视图当作自己的成员来管理,通过"viewArticle","viewarticle","article"这3种成员变量名都可以访问到这个view,推荐第一种
		{
			do
			{
				//加载所有控件的值
				$this->viewPasswordManager->loadWidgets ( $this->params );
				//校验所有控件的值
				if (! $this->viewPasswordManager->verifyWidgets ())
				{
					break;
				}
				// 加载数据
// 				if( !$this->modelUser->load($aId->userId()) )
// 				{
// 					$this->viewPasswordManager->createMessage(Message::error,"预加载用户信息时遇到错误。") ;
// 					$this->viewPasswordManager->hideForm() ;
// 					return ;
// 				}
				$aModel = $aId->model();
				$sOld = Authenticate::encryptPassword($aModel, $aId->username(), $this->viewPasswordManager->widget('oldPassword')->value());
				$sNew = Authenticate::encryptPassword($aModel, $aId->username(), $this->viewPasswordManager->widget('newPassword')->value());
				$sNewConfirm = Authenticate::encryptPassword($aModel, $aId->username(), $this->viewPasswordManager->widget('newPasswordConfirm')->value());
				
				if($sOld != $aModel['password'])
				{
					$this->viewPasswordManager->createMessage(Message::error,"旧密码错误。") ;
					$this->viewPasswordManager->hideForm() ;
					return ;
				}
				
				if($sNew !=  $sNewConfirm)
				{
					$this->viewPasswordManager->createMessage(Message::error,"两次输入的新密码不相同。") ;
					$this->viewPasswordManager->hideForm() ;
					return ;
				}
				
				if($sNewConfirm == $aModel['password'])
				{
					$this->viewPasswordManager->createMessage(Message::error,"旧密码和新密码重复。") ;
					$this->viewPasswordManager->hideForm() ;
					return ;
				}
				$aModel->setData('password', $sNewConfirm);
			//	$this->viewPasswordManager->exchangeData(DataExchanger::WIDGET_TO_MODEL) ;
				if( !$aModel->save() )
				{
					$this->viewPasswordManager->createMessage(Message::error,"保存密码时遇到错误。") ;
					$this->viewPasswordManager->hideForm() ;
					return ;
				}
				else
				{
					// 更新session中的对像
					$aId->setModel($aModel) ;
					$this->viewPasswordManager->createMessage(Message::success,"保存成功。") ;
					$this->viewPasswordManager->hideForm() ;
				}
			}while(0);
		}
	}
}
