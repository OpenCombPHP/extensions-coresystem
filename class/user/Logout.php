<?php
namespace org\opencomb\coresystem\user ;

use org\opencomb\coresystem\auth\Id;
use org\opencomb\base\FrontFrame;
use org\jecat\framework\auth\IdManager;
use org\jecat\framework\message\Message;
use org\opencomb\coresystem\mvc\controller\Controller;

class Logout extends Controller
{
	protected $arrConfig =  array(
		'title'=>'注销',
	) ;

	public function process()
	{
		$aIdMgr = IdManager::singleton() ;
		
		if( $aId=$aIdMgr->currentId() )
		{
			$aIdMgr->removeId( $aId->userId() ) ;
			
			$this->view->createMessage(Message::success,"%s 用户身份已经从系统中退出了。",$aId->username()) ;
			
			// 清理cookie
			Id::clearCookie() ;
		}
		
		if( $aId=$aIdMgr->currentId() )
		{
			$this->view->createMessage(Message::notice,"自动切换到 %s 的用户身份。",$aId->username()) ;
			 
			Id::buryCookie($aId) ;
		}
		else 
		{
			$this->view->createMessage(Message::notice,"正在以游客的身份访问。") ;
		}
		
		$this->view->variables()->set('forward', $this->params['forward']?:'?c=index') ;
	}

}

?>