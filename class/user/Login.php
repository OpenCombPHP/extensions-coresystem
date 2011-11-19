<?php
namespace org\opencomb\coresystem\user ;

use jc\auth\IdManager;

use jc\db\DB;

use jc\message\Message;
use org\opencomb\coresystem\mvc\controller\Controller ;

class Login extends Controller
{
	public function createBeanConfig()
	{
		return array(
		
			// 模型
			'model:user' => array( 'conf' => 'model/user' ) ,
			
			// 视图
			'view:login' => array(
				'template' => 'Login.html' ,
				'class' => 'form' ,
				'model' => 'user' ,
				
				'widgets' => array(
					array( 'conf' => 'widget/username' ) ,
					array( 'conf' => 'widget/password' ) ,
					'rememberme' => array( 'class' => 'checkbox' ,'title' => '记住密码' ) ,
				) ,
			) ,
		) ;
	}
	
	public function process()
	{
	    if( $this->viewLogin->isSubmit( $this->params ) )		 
		{do{
            $this->params['username'] = trim($this->params['username']) ;
            
            // 加载 视图窗体的数据
            $this->viewLogin->loadWidgets( $this->params ) ;
            
            // 校验 视图窗体的数据
            if( !$this->viewLogin->verifyWidgets() )
            {
            	break ;
            }

            $this->modelUser->load( $this->params['username'], 'username' ) ;
			if( $this->modelUser->isEmpty() )
			{
				$this->viewLogin->createMessage(Message::failed,"用户名无效") ;
				break ;
			}
				
			if( $this->modelUser->password != Id::encryptPassword($this->params['username'],$this->params['password']) )
			{
				$this->viewLogin->createMessage(Message::failed,"密码错误，请检查键盘大小写状态") ;
				break ;
			}

			// 
			IdManager::fromSession()->addId(new Id($this->modelUser)) ;
           	
			$this->viewLogin->createMessage(Message::success,"登录成功") ;
			$this->viewLogin->hideForm() ;
			
		} while(0) ; }
	}
}

?>