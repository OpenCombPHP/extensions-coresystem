<?php
namespace org\opencomb\coresystem\user ;

use org\opencomb\coresystem\auth\Id;
use org\jecat\framework\auth\IdManager;
use org\jecat\framework\message\Message;
use org\opencomb\coresystem\mvc\controller\Controller;

class Logout extends Controller
{
	public function createBeanConfig()
	{
		return array(
			'title'=>'注销',
			'view:logout' => array( 'template'=>'Logout.html' )
		) ;
	}

	public function process()
	{
		$aIdMgr = IdManager::singleton() ;
		
		if( $aId=$aIdMgr->currentId() )
		{
			$aIdMgr->removeId( $aId->userId() ) ;
			
			$this->viewLogout->createMessage(Message::success,"%s 用户身份已经从系统中退出了。",$aId->username()) ;
			
			// 清理cookie
			Id::clearCookie() ;
		}
		
		if( $aId=$aIdMgr->currentId() )
		{
			$this->viewLogout->createMessage(Message::notice,"自动切换到 %s 的用户身份。",$aId->username()) ;
			 
			Id::buryCookie($aId) ;
		}
		else 
		{
			$this->viewLogout->createMessage(Message::notice,"正在以游客的身份访问。") ;
		}
	}
}
