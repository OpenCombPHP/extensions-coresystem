<?php
namespace org\opencomb\coresystem\user ;

use org\opencomb\coresystem\auth\Authenticate;
use org\jecat\framework\message\Message;
use org\jecat\framework\mvc\view\DataExchanger;
use org\jecat\framework\db\ExecuteException;
use org\opencomb\coresystem\mvc\controller\Controller ;

class Register extends Controller
{
	public function createBeanConfig()
	{
		return array(
			'title'=>'注册',
			// 模型
			'model:user' => array( 'conf' => 'model/user' ) ,
			
			// 视图
			'view:register' => array(
				'template' => 'Register.html' ,
				'class' => 'form' ,
				'model' => 'user' ,
				
				'widgets' => array(
					array( 'conf' => 'widget/username' ) ,
					array( 'conf' => 'widget/password' ) ,
					array( 'conf' => 'widget/password', 'id' => 'passwordRepeat', 'title'=>'密码重复' ) ,
					
					array( 'class'=>'group', 'widgets'=>array('password','passwordRepeat'), 'verifier:same'=>array() ) ,
				) ,
			) ,
		) ;
	}
	
	public function process()
	{
	    if( $this->viewRegister->isSubmit( $this->params ) )		 
		{
            $this->params['username'] = trim($this->params['username']) ;
            
            // 加载 视图窗体的数据
            $this->viewRegister->loadWidgets( $this->params ) ;
            
            // 校验 视图窗体的数据
            if( $this->viewRegister->verifyWidgets() )
            {
            	$this->viewRegister->exchangeData(DataExchanger::WIDGET_TO_MODEL) ;

            	// 注册时间
            	$this->modelUser->setData('registerTime',time()) ;
            	$this->modelUser->setData('registerIp',$_SERVER['REMOTE_ADDR']) ;
            	$this->modelUser->setData('info.nickname',$this->modelUser->username) ;
            	
            	$sPassword = Authenticate::encryptPassword($this->modelUser,$this->modelUser->username,$this->viewRegister->widget('password')->value()) ;
            	$this->modelUser->setData('password',$sPassword) ;

            	try {
            		$this->modelUser->save() ;
            		$this->viewRegister->createMessage( Message::success, "注册成功！" ) ;
            			
            		$this->viewRegister->hideForm() ;
            		
            	} catch (ExecuteException $e) {
            			
            		if($e->isDuplicate())
            		{
            			$this->viewRegister->createMessage(
            					Message::error
            					, "用户名：%s 已经存在"
            					, $this->params->get('username')
            			) ;
            		}
            		else
            		{
            			throw $e ;
            		}
            	}
           	}
		}
	}
}

?>