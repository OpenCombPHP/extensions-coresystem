<?php 
namespace org\opencomb\coresystem\user ;

use org\jecat\framework\auth\IdManager;

use org\jecat\framework\message\Message;
use org\jecat\framework\mvc\model\db\Model;
use org\opencomb\coresystem\auth\Authenticate;
use org\opencomb\coresystem\mvc\controller\Controller ;

class ModifyPassword extends Controller
{
	protected $arrConfig = array(
			'view' => 'coresystem:user/ModifyPassword.html' ,
			'title' => '修改密码' ,
	) ;
	
	public function process()
	{
		$this->doActions() ;
	}
	
	protected function form()
	{
		$aMyModel = $this->requireLogined()->model() ;
		
		if(!$this->view()->verifyWidgets())
		{
			return ;
		}
		
		if( empty($this->params['newpwd']) or $this->params['newpwd']!=$this->params['newpwdrepeat'] )
		{
			$this->createMessage(Message::failed,"两次输入的新密码必须一直，并且不能为空。") ;
			return ;
		}
		
		if( Authenticate::encryptPassword($aMyModel, $aMyModel['username'], $this->params['oripwd']) != $aMyModel['password'] )
		{
			$this->createMessage(Message::forbid,"旧密码错误。") ;
			return ;
		}
		
		$aMyModel['password'] = Authenticate::encryptPassword($aMyModel, $aMyModel['username'], $this->params['newpwdrepeat']) ;
		$aMyModel->update(null,"user.uid='{$aMyModel['uid']}'") ;
		
	}
}