<?php
namespace org\opencomb\coresystem ;

use jc\message\Message;

use jc\mvc\view\DataExchanger;
use jc\db\ExecuteException;
use oc\mvc\controller\Controller ;

class Register extends Controller
{
	public function createBeanConfig()
	{
		return array(
		
			// 模型
			'model:User' => array(
				'orm' => array(
					'table' => 'user' ,
					'hasOne:info' => array(
						'table' => 'userinfo' ,
					) ,
				) ,
			) ,
			
			// 视图
			'view:Register' => array(
				'template' => 'Register.html' ,
				'class' => 'form' ,
				'model' => 'User' ,
				
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
	    if( $this->viewRegister->isSubmit( $this->aParams ) )		 
		{
            // 加载 视图窗体的数据
            $this->viewRegister->loadWidgets( $this->aParams ) ;
            
            // 校验 视图窗体的数据
            if( $this->viewRegister->verifyWidgets() )
            {
            	$this->viewRegister->exchangeData(DataExchanger::WIDGET_TO_MODEL) ;

            	// 注册时间
            	$this->viewRegister->model()->setData('registerTime',time()) ;

            	try {
            		$this->viewRegister->model()->save() ;
            		$this->viewRegister->createMessage( Message::success, "注册成功！" ) ;
            		
            		$this->viewRegister->model()->setData('success',"1") ;
            			
            	} catch (ExecuteException $e) {
            			
            		if($e->isDuplicate())
            		{
            			$this->viewRegister->createMessage(
            					Message::error
            					, "用户名：%s 已经存在"
            					, $this->viewRegister->model()->data('username')
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