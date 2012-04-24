<?php
namespace org\opencomb\coresystem\user ;

use org\opencomb\coresystem\auth\Id;
use org\jecat\framework\db\DB;
use org\jecat\framework\mvc\model\db\Category;
use org\jecat\framework\message\Message;
use org\opencomb\coresystem\mvc\controller\ControlPanel;

class UserGroupsSetting extends ControlPanel
{
	public function createBeanConfig()
	{
		// 加载 Model group 的 Bean Config
		/*$arrGroupConf = BeanFactory::singleton()->findConfig('model/group','coresystem') ;
		$arrGroupConf['list'] = true ;
		
		// 在 group Config 中添加一个 hasAndBelongsToMany 关联
		$arrGroupConf['orm']['belongsTo:user'] = array(
						'fromkeys'=>'gid' ,
						'tokeys'=>'gid' ,
						'keys' => array('uid','gid') ,
						'on' => array("user.uid=@1",$this->params->string('uid')) ,  
						'table' => 'group_user_link' ,
						'colums' => 'uid' ,
					) ;*/
		
					
		return array(
			'title'=>'用户组设置',
			// models
			'model:groups' => array(
				'class' => 'model' ,
				'list' => true ,
				'orm' => array(
					'table' => 'group' ,
					'limit' => -1 ,
					'belongsTo:user' => array(
						'fromkeys'=>'gid' ,
						'tokeys'=>'gid' ,
						'keys' => array('uid','gid') ,
						'table' => 'group_user_link' ,
						'colums' => 'uid' ,
					),
					'where' => array("user.uid=@1",$this->params->string('uid')) ,
				) ,
			) ,
			'model:user' => array('config'=>'model/user') ,
			'model:newUsrGrpLink' => array( 'orm'=>array('table'=>'group_user_link','keys'=>array('uid','gid')) ) ,
		
			// views
			'view:userGroups' => array(
				'template' => 'UserGroupsSetting.html' ,
				'class' => 'form' ,
				'model' => 'groups' ,
			),
			'perms' => array(
					// 权限类型的许可
					'perm.purview'=>array(
							'name' => Id::PLATFORM_ADMIN,
					) ,
			) ,
		) ;
	}
	
	public function process()
	{
		$this->checkPermissions('您没有使用这个功能的权限,无法继续浏览',array()) ;
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
		
		$this->groups->load() ;
		Category::buildTree($this->groups) ;
		
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
						if( $this->newUsrGrpLink->save() )
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

