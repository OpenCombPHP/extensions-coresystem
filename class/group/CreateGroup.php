<?php
namespace org\opencomb\coresystem\group ;

use org\jecat\framework\mvc\model\Model;
use org\opencomb\coresystem\auth\Id;
use org\jecat\framework\message\Message;
use org\jecat\framework\mvc\model\Category;
use org\jecat\framework\mvc\view\DataExchanger;
use org\opencomb\coresystem\mvc\controller\ControlPanel;

class CreateGroup extends ControlPanel
{
	protected $arrConfig = array(
			'title'=>'新建用户组',
			'perms' => array(
					// 权限类型的许可
					'perm.purview'=>array(
							'name' => Id::PLATFORM_ADMIN,
					) ,
			) ,
	) ;
	
	public function process()
	{
		$aGourps = $this->view()->setModel('coresystem:group')->model() ;
		
		// 增加新菜单
		$this->doActions() ;
		
		// 加载已有分组菜单
		Category::buildTree( $aGourps->load() ) ;

		foreach($aGourps as $arrGroup)
		{
			$sText = str_repeat('--',Category::depth($aGourps)) . $arrGroup['name'] ;
			$this->view->parentGroup->addOption($sText,$arrGroup['rgt']) ;
		}
	}
	
	public function form()
	{
		if( !$this->view()->loadWidgets() )
		{
			return ;
		}

		$this->view->hideForm()->fetch() ;

		$aGourps = $this->view()->model() ;
		$aNewCategory = new Category($aGourps) ;
			
		if($nGrpRgtFoot=$this->view->parentGroup->value())
		{
			$aNewCategory->insertCategoryToPoint($nGrpRgtFoot) ;
		}
		else
		{
			$aNewCategory->insertBefore(Category::end) ;
		}
		
		$this->createMessage(Message::success,"分组%s已经保存",$aGourps['name']) ;
	}
	
}

?>