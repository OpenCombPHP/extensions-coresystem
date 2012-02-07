<?php
namespace org\opencomb\coresystem\widget;

use org\jecat\framework\mvc\view\widget\Widget;

class NameCard extends Widget {
	public function __construct( $sId = '', $sTitle = null,  IView $aView = null) {
		parent::__construct ( $sId, 'coresystem:NameCard.html',$sTitle, $aView );
	}
	
	/**
	 * 获取一个当前用户的信息面板控件
	 * 
	 * @param string $sTemplate 自定义模板
	 * @param int $options 参数集合
	 * @return FaceWidget
	 */
	static function mine($sTemplate = null)
	{
		$aIdModel = IdManager::fromSession()->currentId ()->userDataModel();
		return new self($aIdModel , $sTemplate);
	}
	
	/**
	 * 根据模型参数取得一个用户信息面板 
	 * @param IModel $aModel
	 * @param string $sTemplate
	 * @return FaceWidget
	 */
	static function instance( IModel $aModel , $sTemplate = null )
	{
		return new self( $aModel , $sTemplate );
	}
	
	/**
	 * @return IModel 
	 */
	public function model()
	{
		return $this->aModel;
	}
	
	/**
	 * 
	 * @param IModel $aModel 
	 */
	public function setModel(IModel $aModel)
	{
		$this->aModel = $aModel;
	}
	
	public function setUsernameVisible($bVisibel)
	{
		$this->bUsernameVisible = $bVisibel;
	}
	public function isUsernameVisible()
	{
		return $this->bUsernameVisible;
	}
	public function username()
	{
		$sUsername = '';
		if(!$this->aModel->hasData('username'))
		{
			$sUsername = $this->aModel->data('username');
		}
		return $sUsername;
	}
	
	public function isFaceVisible()
	{
		return $this->bFaceVisible;
	}
	public function setFaceVisible($bVisibel)
	{
		$this->bFaceVisible = $bVisibel;
	}
	/**
	 * 取得头像地址
	 * @throws Exception 如果模型中没有需要的列
	 */
	public function face()
	{
		//检查需要的列是否存在,目前支持头像地址(face), 以后支持更多
		if(!$this->aModel->hasData('info.face'))
		{
			$sFaceUrl = '/platform/ui/images/viewimg/xshd01.jpg';
		}else
		{
			$sFaceUrl = CoreUser::getFaceFolder()->path() . '/' . $this->aModel->data('info.face');
		}
		return $sFaceUrl;
	}
	
	public function isNicknameVisible()
	{
		return $this->bNicknameVisible;
	}
	public function setNicknameVisible($bVisibel)
	{
		$this->bNicknameVisible = $bVisibel;
	}
	public function nickname()
	{
		$sNickname = '';
		if(!$this->aModel->hasData('nickname'))
		{
			$sNickname = $this->aModel->data('nickname');
		}
		return $sNickname;
	}
	
	public function isEmailVisible()
	{
		return $this->bEmailVisible;
	}
	public function setEmailVisible($bVisibel)
	{
		$this->bNicknameVisible = $bVisibel;
	}
	public function email()
	{
		$sEmail = '';
		if(!$this->aModel->hasData('email'))
		{
			$sEmail = $this->aModel->data('email');
		}
		return $sEmail;
	}
	
	public function isSexVisible()
	{
		return $this->bSexVisible;
	}
	public function setSexVisible($bVisibel)
	{
		$this->bSexVisible = $bVisibel;
	}
	public function sex()
	{
		$sSex = '';
		if(!$this->aModel->hasData('sex'))
		{
			$sSex = $this->aModel->data('sex');
		}
		return $sSex;
	}
	
	public function isBirthdayVisible()
	{
		return $this->bBirthdayVisible;
	}
	public function setBirthdayVisible($bVisibel)
	{
		$this->bBirthdayVisible = $bVisibel;
	}
	public function birthday()
	{
		$sBirthday = '';
		if(!$this->aModel->hasData('birthday'))
		{
			$sBirthday = $this->aModel->data('birthday');
		}
		return $sBirthday;
	}
	
	public function isCityVisible()
	{
		return $this->bCityVisible;
	}
	public function setCityVisible($bVisibel)
	{
		$this->bCityVisible = $bVisibel;
	}
	public function city()
	{
		$sCity = '';
		if(!$this->aModel->hasData('city'))
		{
			$sCity = $this->aModel->data('city');
		}
		return $sCity;
	}
	
	public function isLastLoginTimeVisible()
	{
		return $this->bLastLoginTimeVisible;
	}
	public function setLastLoginTimeVisible($bVisibel)
	{
		$this->bLastLoginTimeVisible = $bVisibel;
	}
	public function lastLoginTime()
	{
		$sLastLoginTime = '';
		if(!$this->aModel->hasData('lastLoginTime'))
		{
			$sLastLoginTime = $this->aModel->data('lastLoginTime');
		}
		return $sLastLoginTime;
	}
	
	public function isRegisterTimeVisible()
	{
		return $this->bRegisterTimeVisible;
	}
	public function setRegisterTimeVisible($bVisibel)
	{
		$this->bRegisterTimeVisible = $bVisibel;
	}
	public function registerTime()
	{
		$sRegisterTime = '';
		if(!$this->aModel->hasData('registerTime'))
		{
			$sRegisterTime = $this->aModel->data('registerTime');
		}
		return $sRegisterTime;
	}
	
	public function isActiveTimeVisible()
	{
		return $this->bActiveTimeVisible;
	}
	public function setActiveTimeVisible($bVisibel)
	{
		$this->bActiveTimeVisible = $bVisibel;
	}
	public function activeTime()
	{
		$sActiveTime = '';
		if(!$this->aModel->hasData('activeTime'))
		{
			$sActiveTime = $this->aModel->data('activeTime');
		}
		return $sActiveTime;
	}
	
	private $bFaceVisible = true;						//头像地址
	private $bUsernameVisible = false;						//用户名
	private $bNicknameVisible = false;						//昵称	
	private $bEmailVisible = false;						//email地址
	private $bSexVisible = false;							//性别
	private $bBirthdayVisible = false;					//生日
	private $bCityVisible = false;						//城市
	private $bLastLoginTimeVisible = false;			//最后登录时间
	private $bRegisterTimeVisible = false;				//注册时间
	private $bActiveTimeVisible = false;				//在线时长
	
	/**
	 * 数据来源
	 * @var IModel 
	 */
	private $aModel;
}

?>