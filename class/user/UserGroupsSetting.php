<?php
namespace org\opencomb\coresystem\user ;

use oc\mvc\model\db\orm\Prototype;
use jc\db\DB;
use jc\mvc\model\db\Category;
use jc\bean\BeanFactory;
use jc\message\Message;
use org\opencomb\coresystem\mvc\controller\ControlPanel;

class UserGroupsSetting extends ControlPanel
{
	public function createBeanConfig()
	{
		// 加载 Model group 的 Bean Config
		$arrGroupConf = BeanFactory::singleton()->findConfig('model/group','coresystem') ;
		
		// 在 group Config 中添加一个 hasAndBelongsToMany 关联
		$arrGroupConf['orm']['belongsTo:user'] = array(
						'fromkeys'=>'gid' ,
						'tokeys'=>'gid' ,
						'keys' => array('uid','gid') ,
						'on' => array(
							array('eq', 'to.uid', $this->params->string('uid') )
						) ,
						'table' => 'group_user_link' ,
						'colums' => 'uid' ,
					) ;
		
					
		return array(
				
			// models
			'model:groups' => $arrGroupConf ,
			'model:user' => array('config'=>'model/user') ,
			'model:newUsrGrpLink' => array( 'orm'=>array('table'=>'group_user_link','keys'=>array('uid','gid')) ) ,
		
			// views
			'view:userGroups' => array(
				'template' => 'UserGroupsSetting.html' ,
				'class' => 'form' ,
				'model' => 'groups' ,
			) 
		) ;
	}
	
	public function process()
	{
		// 检查参数
		if( !$nUId = $this->params->int('uid') )
		{
			$this->userGroups->hideForm() ;
			$this->userGroups->createMessage(Message::error,"缺少参数 uid") ;
			return ;
		}
		
		// 检查 uid 用户是否存在
		if( !$this->user->load($nUId) )
		{
			$this->userGroups->hideForm() ;
			$this->userGroups->createMessage(Message::error,"指定的用户无效。") ;
			return ;
		}
		
		$aGoupsIter = Category::loadTotalCategory($this->groups->prototype()) ;
		foreach(Category::buildTree($aGoupsIter) as $aGroup)
		{
			$this->groups->addChild($aGroup) ;
		}
		
		if( $this->userGroups->isSubmit($this->params) )
		{
			if(empty($this->params['groups']))
			{
				$this->params['groups'] = array() ;
			}
			
			$sUsrGrpLinkTable = $this->newUsrGrpLink->prototype()->tableName() ;
			
			foreach($this->groups->childIterator() as $aGroup)
			{
				if( !$aGroup['user.uid'] )
				{
					// 新加入分组
					if( in_array($aGroup->gid,$this->params['groups']) )
					{
						$this->newUsrGrpLink->uid = $this->user->uid ;
						$this->newUsrGrpLink->gid = $aGroup->gid ;
						if( $this->newUsrGrpLink->setSerialized(false)->save() )
						{
							$aGroup['user.uid'] = $this->user->uid ;
							$this->userGroups->createMessage(Message::success,"用户 %s 已经加入了分组 %s",array($this->user['username'],$aGroup['name'])) ;
						}
						
					}
				}
				else
				{
					// 解除分组
					if( !in_array($aGroup->gid,$this->params['groups']) )
					{
						if( DB::singleton()->execute("delete from {$sUsrGrpLinkTable} where uid='{$this->user->uid}' and gid='{$aGroup->gid}' ;") )
						{
							$aGroup['user.uid'] = null ;
							$this->userGroups->createMessage(Message::success,"用户 %s 已经从分组 %s 中移除",array($this->user['username'],$aGroup['name'])) ;
						}
					}
				}
			}
		}
	}
}
