<?php
namespace org\opencomb\coresystem\user ;

use org\jecat\framework\mvc\model\Model;

use org\opencomb\coresystem\auth\Authenticate;
use org\jecat\framework\message\Message;
use org\jecat\framework\mvc\view\DataExchanger;
use org\jecat\framework\db\ExecuteException;
use org\opencomb\coresystem\mvc\controller\Controller ;

class Register extends Controller
{
	protected $arrConfig = array(
		'title'=>'注册',
	) ;
	
	public function registerForm()
	{
		$this->params['username'] = trim($this->params['username']) ;
		
		if( !$this->view()->loadWidgets($this->params) )
		{
			return ;
		}

		
		$aModel = $this->view()
				->setModel(
					Model::create('coresystem:user')
						->hasOne('coresystem:userinfo')
				)
				->exchangeData(DataExchanger::WIDGET_TO_MODEL)
				->model() ;
		

		try {
			
			$aModel->setRow(array(
						'registerTime' => time() ,
						'registerIp' => $_SERVER['REMOTE_ADDR'] ,
						'userinfo.nickname' => $aModel['username'] ,
						'password' => Authenticate::encryptPassword($aModel,$aModel['username'],$aModel['password']) ,
				))
				->insert() ;
			
			$this->createMessage( Message::success, "注册成功！" ) ;

			$this->view->hideForm('registerForm') ;
            		
		 } catch (ExecuteException $e) {
			if($e->isDuplicate())
			{
				$this->view->createMessage(Message::error, "用户名：%s 已经存在", $this->params['username'] ) ;
			}
			else
			{
				throw $e ;
			}
		}
	}
}

?>