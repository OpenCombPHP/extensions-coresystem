<?php
namespace org\opencomb\coresystem\user ;

use org\jecat\framework\db\sql\StatementState;

use org\jecat\framework\mvc\model\db\Model;

use org\opencomb\coresystem\auth\Id;

use org\opencomb\coresystem\mvc\controller\ControlPanel;

class AdminUsers extends ControlPanel
{
	protected $arrConfig = array(
			
			'title' => '用户管理' ,
			'keywords' => '用户' ,
			'description' => '用户' ,
			
			'perms' => array(
				// 权限类型的许可
				'perm.purview'=>array(
						'name' => Id::PLATFORM_ADMIN,
				) ,
			) ,
		) ;
	
	public function process()
	{
		$aModel = $this->view->setModel('coresystem:user')
						->hasOne('coresystem:userinfo') ;
		
		if( $this->params->has('username') )
		{
			$aModel->where("username like '".addslashes($this->params['username'])."%'") ;
		}
		
		$aModel->load() ;
	}
	
	
}

?>