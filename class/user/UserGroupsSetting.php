<?php
namespace org\opencomb\coresystem\user ;

use org\opencomb\coresystem\auth\Id;
use org\jecat\framework\db\DB;
use org\jecat\framework\mvc\model\Prototype;
use org\jecat\framework\mvc\model\Category;
use org\jecat\framework\bean\BeanFactory;
use org\jecat\framework\message\Message;
use org\opencomb\coresystem\mvc\controller\ControlPanel;

class UserGroupsSetting extends ControlPanel
{
	protected $arrConfig = array(
			'title'=>'用户组设置',
			'perms' => array(
				'perm.purview'=>array(
						'name' => Id::PLATFORM_ADMIN,
				) ,
			) ,
	) ;
	
	public function process()
	{
		// 检查参数
		if( !$nUId = $this->params->int('uid') )
		{
			$this->view->hideForm() ;
			$this->createMessage(Message::error,"缺少参数 uid") ;
			return ;
		}
		
		// 检查 uid 用户是否存在
		if( !$this->model('coresystem:user')->load($nUId)->rowNum() )
		{
			$this->view->hideForm() ;
			$this->createMessage(Message::error,"指定的用户无效。") ;
			return ;
		}
		
		$this->view()->setModel('coresystem:group')
				->ass(array(
					'assoc' => Prototype::belongsTo ,
					'name' => 'user' ,
					'table'=>'coresystem:group_user_link',
					'fromKeys' =>'gid',
					'toKeys'=>'gid',
					'on' => "user.uid='{$this->params->string('uid')}'" ,
				))
				->load() ;
		
		Category::buildTree($this->group) ;
		
		$this->doActions() ;
		
		// reload after doActions
		$this->group->load();
	}
	
	protected function form()
	{
		$this->params['groups'] = $this->params['groups'] ?: array() ;
		
		$this->model('coresystem:group_user_link','newUsrGrpLink');
		
		foreach($this->group as $aGroup)
		{
			if( !$aGroup['user.uid'] )
			{
				// 新加入分组
				if( in_array($aGroup['gid'],$this->params['groups']) )
				{
					$this->newUsrGrpLink->uid = $this->user->uid ;
					$this->newUsrGrpLink->gid = $aGroup['gid'] ;
					if( $this->newUsrGrpLink->insert() )
					{
						$aGroup['user.uid'] = $this->user->uid ;
						$this->view()->createMessage(
							Message::success,
							"用户 %s 已经加入了分组 %s",
							array(
								$this->user['username'],
								$aGroup['name']
							)
						) ;
					}
					
				}
			}
			else
			{
				// 解除分组
				if( !in_array($aGroup['gid'],$this->params['groups']) )
				{
					$this->newUsrGrpLink->delete(
						'`gid`='.$aGroup['gid'].' and `uid`='.$this->user->uid
					);
					$this->view()->createMessage(
						Message::success,
						"用户 %s 已经从分组 %s 中移除",
						array(
							$this->user['username'],
							$aGroup['name']
						)
					) ;
				}
			}
		}
	}
}
