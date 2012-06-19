<?php
namespace org\opencomb\coresystem\group ;

use org\opencomb\coresystem\auth\Id;
use org\jecat\framework\mvc\controller\Request;
use org\jecat\framework\message\Message;
use org\jecat\framework\system\Application;
use org\jecat\framework\mvc\controller\Relocater;
use org\jecat\framework\mvc\model\Category;
use org\opencomb\coresystem\mvc\controller\ControlPanel;

class AdminGroups extends ControlPanel
{
	protected $arrConfig = array(
			'title'=>'用户组设置',
			'perms' => array(
					// 权限类型的许可
					'perm.purview'=>array(
							'name' => Id::PLATFORM_ADMIN,
					) ,
			) ,
	) ;
	
	public function process()
	{
		// 为视图创建模型
		$aModel = $this->view()->setModel('coresystem:group')->model() ;
		
		$this->doActions() ;
		
		// 加载模型
		Category::buildTree( $aModel->load() ) ;
	}
	
	public function delete()
	{
		$aModel = $this->view()->model() ;
		
		if( !isset($this->params['gid']) or !$aModel->load($this->params['gid'])->rowNum() )
		{
			$this->createMessage(Message::error,"id为 %d 的用户组不存在",$this->params['gid']) ;
		}
		else
		{
			// 清理权限
			// todo ...
		
			// 解除用户关系
			// todo ...

			// 删除权限
			$aDelCategory = new Category($aModel) ;
			$aDelCategory->delete() ;
			
			$this->createMessage(Message::success, "用户组 %s 已经删除", $aModel->name) ;
			
			$this->location( '?c=org.opencomb.coresystem.group.AdminGroups' ) ;
		}
	}
}

?>