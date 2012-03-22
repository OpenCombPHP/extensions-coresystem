<?php
namespace org\opencomb\coresystem\mvc\controller ;

use org\jecat\framework\auth\IdManager;

use org\jecat\framework\bean\BeanFactory;
use org\jecat\framework\mvc\view\View;

class UserPanelFrame extends UserSpaceFrame
{	
	public function createBeanConfig()
	{
		$arrBean = parent::createBeanConfig();
		
		$sId = '';
		if($aId = IdManager::singleton()->currentId()){
			$sId = '&uid='.$aId->userId();
		}
		
		$arrBean['frameview:userPanelFrame'] =  array(
					'template' => 'coresystem:UserPanelFrame.html' ,
					'widget:userPanelMenu' => array( 
							'class' => 'menu' ,
							'item:infomanage' => array(
									'title' => '个人资料' ,
									'link'=>'?c=org.opencomb.coresystem.user.UserInfoManager' . $sId,
									'query'=>'c=org.opencomb.coresystem.user.UserInfoManager' . $sId,
							) ,
							'item:avatarmanage' => array(
									'title' => '头像管理' ,
									'link'=>'?c=org.opencomb.coresystem.user.AvatarManager' . $sId,
									'query'=>'c=org.opencomb.coresystem.user.AvatarManager' . $sId,
							) ,
							'item:passwordmanage' => array(
									'title' => '修改密码' ,
									'link'=>'?c=org.opencomb.coresystem.user.PasswordManager' . $sId,
									'query'=>'c=org.opencomb.coresystem.user.PasswordManager' . $sId,
							) ,
							'item:oauth' => array(
									'title' => '绑定网站' ,
									'link'=>'?c=org.opencomb.oauth.controlPanel.OAuthState' . $sId,
									'query'=>'c=org.opencomb.oauth.controlPanel.OAuthState' . $sId,
							) ,
					)
			) ;
		return $arrBean;
	}
}
?>