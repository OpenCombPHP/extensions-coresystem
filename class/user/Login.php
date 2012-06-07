<?php
namespace org\opencomb\coresystem\user ;

use org\opencomb\coresystem\auth\Id;
use org\opencomb\coresystem\auth\Authenticate;
use org\jecat\framework\auth\IdManager;
use org\jecat\framework\db\DB;
use org\jecat\framework\message\Message;
use org\opencomb\coresystem\mvc\controller\Controller ;

class Login extends Controller
{
	protected $arrConfig = array(
		'title'=>'登录',
	) ;
	
	public function process()
	{
        if( 0 and $this->view->isSubmit( $this->params ) )		 
		{do{
            $this->params['username'] = trim($this->params['username']) ;
            
            // 加载 视图窗体的数据
            $this->view->loadWidgets( $this->params ) ;
            
            // 校验 视图窗体的数据
            if( !$this->view->verifyWidgets() )
            {
            	break ;
            }

            $this->user->load( $this->params['username'], 'username' ) ;
			if( $this->user->isEmpty() )
			{
				$this->view->createMessage(Message::failed,"用户名无效") ;
				break ;
			}
				
			if( $this->user->password != Authenticate::encryptPassword($this->user,$this->params['username'],$this->params['password']) )
			{
				$this->view->createMessage(Message::failed,"密码错误，请检查键盘大小写状态") ;
				break ;
			}

			// 
			$aId = new Id($this->user) ;
			IdManager::singleton()->addId($aId,true) ;
			
			// 保存 last login 信息
			Id::makeLoginInfo($aId) ;
			$this->user->save() ;
           	
			$this->view->createMessage(Message::success,"登录成功") ;
			// $this->view->hideForm() ;
			
			if( $this->params['forward'] )
			{
				$this->view->variables()->set('forwarding',$this->params['forward']) ;
			}
			
			// 
			if( !empty($this->params['rememberme']) )
			{
				Id::buryCookie($aId) ;
			}
			
			
		} while(0) ; }
		
		
		
	}
	
}

?>
