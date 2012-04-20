<?php
namespace org\opencomb\coresystem\namecard ;

use org\jecat\framework\message\Message;
use org\opencomb\coresystem\user\UserModel;
use org\opencomb\coresystem\mvc\controller\Controller;

class NameCardExtension extends Controller
{
	public function createBeanConfig()
	{
		return array(
			'title'=>'名片信息',
				
			'view' => array(
				'template' => 'NameCardExtension.html' ,
			) ,
		) ;
	}
	
	public function process()
	{
		$nId = (int)$this->params->get('uid');
		if(!$nId )
		{
			$this->messageQueue ()->create ( Message::error, "未指定用户" );
		}
		
		$this->aModel = UserModel::byUId($nId);
		
		$this->view->variables()->set('aModel',$this->aModel) ;
	}
}