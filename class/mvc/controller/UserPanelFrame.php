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
							'item:avatarmanage' => array(
									'title' => '我的头像' ,
									'link'=>'http://www.wonei.com/cp.php?ac=avatar',//?c=org.opencomb.coresystem.user.AvatarManager' . $sId,
									'query'=>'http://www.wonei.com/cp.php?ac=avatar',
							) ,
							'item:infomanage' => array(
									'title' => '个人资料' ,
									'link'=>'http://www.wonei.com/cp.php?ac=profile',//?c=org.opencomb.coresystem.user.UserInfoManager' . $sId,
									'query'=>'http://www.wonei.com/cp.php?ac=profile',
							) ,
							'item:oauth' => array(
									'title' => '绑定网站' ,
									'link'=>'?c=org.opencomb.oauth.controlPanel.OAuthState' . $sId,
									'query'=>'c=org.opencomb.oauth.controlPanel.OAuthState' . $sId,
							) ,
							'item:gameinfo' => array(
									'title' => '游戏资料' ,
									'link'=>'cp.php?ac=gameinfo',
									'query'=>'cp.php?ac=gameinfo',
							) ,
							'item:private' => array(
									'title' => '隐私设置' ,
									'link'=>'cp.php?ac=privacy',
									'query'=>'cp.php?ac=privacy',
							) ,
							'item:statefilter' => array(
									'title' => '动态筛选' ,
									'link'=>'cp.php?ac=privacy&op=view',
									'query'=>'cp.php?ac=privacy&op=view',
							) ,
							'item:theme' => array(
									'title' => '装饰小窝' ,
									'link'=>'cp.php?ac=theme',
									'query'=>'cp.php?ac=theme',
							) ,
							'item:domain' => array(
									'title' =>'我的域名' ,
									'link'=>'cp.php?ac=domain',
									'query'=>'cp.php?ac=domain',
							) ,
							'item:sendmail' => array(
									'title' =>'邮件提醒' ,
									'link'=>'cp.php?ac=sendmail',
									'query'=>'cp.php?ac=sendmail',
							) ,
							'item:passwordmanage' => array(
									'title' => '帐号安全' ,
									'link'=>'http://www.wonei.com/cp.php?ac=password',//?c=org.opencomb.coresystem.user.PasswordManager' . $sId,
									'query'=>'http://www.wonei.com/cp.php?ac=password',
							) ,
							'item:credit' => array(
									'title' =>'声望' ,
									'link'=>'cp.php?ac=credit',
									'query'=>'cp.php?ac=credit',
							) ,
							'item:advance' => array(
									'title' =>'高级' ,
									'link'=>'http://www.wonei.com/cp.php?ac=advance',
									'query'=>'http://www.wonei.com/cp.php?ac=advance',
							) ,
					)
			) ;
		return $arrBean;
	}
}
?>