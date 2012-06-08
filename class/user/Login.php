<?php
namespace org\opencomb\coresystem\user ;

use org\jecat\framework\mvc\model\Model;
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
	
	public function form()
	{
		if( !$this->view->loadWidgets() )
		{
			return ;
		}
		
		$aUser = Model::create('coresystem:user')
				->limit(1)
				->load($this->params['username'],'username') ;
		
		if( !$aUser->rowNum() )
		{
			$this->createMessage(Message::failed,"用户名无效") ;
			return ;
		}
		
		if( $aUser['password']!=Authenticate::encryptPassword($aUser,$this->params['username'],$this->params['password']) )
		{
			$this->createMessage(Message::failed,"密码错误，请检查键盘大小写状态") ;
			return ;
		}
		
		//
		$aId = new Id($aUser) ;
		IdManager::singleton()->addId($aId,true) ;

		// 保存 last login 信息
		//Id::makeLoginInfo($aId) ;
		//$this->user->save() ;
		
		$this->createMessage(Message::success,"登录成功") ;
		$this->view->hideForm() ;
			
		if( $this->params['forward'] )
		{
			$this->view->variables()->set('forwarding',$this->params['forward']) ;
		}
			
		//
		if( !empty($this->params['rememberme']) )
		{
			Id::buryCookie($aId) ;
		}
	}
}


