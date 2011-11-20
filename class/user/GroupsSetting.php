<?php
namespace org\opencomb\coresystem\user ;

use jc\bean\BeanFactory;

use jc\message\Message;

use org\opencomb\coresystem\mvc\controller\ControlPanel;

class GroupsSetting extends ControlPanel
{
	public function createBeanConfig()
	{
		// 加载 Model group 的 Bean Config
		$arrGroupConf = BeanFactory::singleton()->findConfig('model/group','coresystem') ;
		
		// 在 group Config 中添加一个 hasAndBelongsToMany 关联
		/*$arrGroupConf['orm']['hasAndBelongsToMany:user'] = array(
		
						'bridge' => 'user' ,
		
						'fromKeys'=>'gid' ,
						'toBridgeKeys'=>'gid' ,
						'fromkBridgeKeys'=>'uid' ,
						'toKeys'=>'uid' ,
						
						// 'on' => array( 'eq', 'bridge.uid', $this->params->string('uid') ) ,
		
						'table' => 'user' ,
						'colums' => 'uid' ,
					) ;*/
		
					
		return array(
		
			'model:groups' => $arrGroupConf ,
		
			'view:userGourps' => array(
				'template' => 'UserGourps.html' ,
				'class' => 'form' ,
				
			) 
		) ;
	}
	
	public function process()
	{
		// 检查参数
		if( !$nUId = $this->params->int('uid') )
		{
			$this->userGourps->hideForm() ;
			$this->userGourps->createMessage(Message::error,"缺少参数 uid") ;
			return ;
		}
		
		$this->groups->load($this->params->string('uid')) ;
		$this->groups->printStruct() ;
	}
}
