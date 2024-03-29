<?php
namespace org\opencomb\coresystem\namecard;

use org\opencomb\coresystem\user\UserModel;
use org\jecat\framework\mvc\model\db\Model;
use org\jecat\framework\bean\BeanFactory;
use org\opencomb\platform\ext\Extension;
use org\jecat\framework\auth\IdManager;
use org\jecat\framework\io\IOutputStream;
use org\jecat\framework\util\IHashTable;
use org\jecat\framework\ui\UI;
use org\jecat\framework\mvc\model\IModel;
use org\jecat\framework\mvc\view\widget\Widget;

/**
 * @wiki /CoreSystem/控件/名片
 * 
 * 名片控件,用来显示用户的信息,例如昵称,头像,积分等等.
 * 可以使用model或者用户id作为信息来源.
 * 
 * ==使用方法==
 * 需要传入一个用户的model作为信息来源,用type参数来控制显示样式,具体参数如下:
 * {|
 * !参数名
 * !类型
 * !默认值
 * !可选
 * !说明
 * |-- --
 * |model
 * |Model
 * |无
 * |可选
 * |头像控件的信息来源,必须手动显示的声明这个属性是Model类型
 * |-- --
 * |type
 * |string
 * |normal
 * |可选
 * |如何展示名片控件.分'simple','normal','full',3个级别,大体上决定名片控件显示信息的多少.控件内部会通过更换模板来实现这个参数的功能,如果指定了template属性,那么以template优先
 * |-- --
 * |uid
 * |string
 * |无
 * |可选
 * |传入用户ID以显示对应的用户信息
 * |-- --
 * |mine
 * |string
 * |'0'
 * |可选
 * |传入'0'使用正在登录的用户的信息来显示名片并忽略model属性,传入其他值则忽略此功能
 * |}
 * 
 * [^]如果使用model属性来指定信息来源,并且这个属性的值是一段表达式,那么你需要另外指定属性的类型来让表达式执行,指定属性类型的代码: attr.model.type='expression'[/^]
 * [^]如果用户使用了widget的临时模板功能,那么临时模板会比widget指定的模板优先级高[/^]
 */
class NameCard extends Widget {
	public function __construct($aUserModel=null, $sId = '', $sTitle = null,  IView $aView = null) {
		if($aUserModel){
			$this->setModel($aUserModel);
		}
		parent::__construct ( $sId, 'coresystem:NameCard_normal.html',$sTitle, $aView );
	}
	
	/**
	 * @return IModel 
	 */
	public function model()
	{
		return $this->aModel;
	}
	
	/**
	 * @param IModel $aModel 
	 */
	public function setModel(IModel $aModel)
	{
		$this->aModel = $aModel;
	}
	
	public function display(UI $aUI,IHashTable $aVariables=null,IOutputStream $aDevice=null)
	{
		if($bMine = (bool)$this->attribute('mine')){
			//如果没登录就什么也不显示了
			if(!$aCurrentId = IdManager::singleton()->currentId()){
				return;
			}
		}
		if($aModel = $this->attribute('model')){
			
			$this->setModel($aModel);
		}
		if($sDisplayType = $this->attribute('type')){
			$this->setDisplayType($sDisplayType);
		}
		if($nId = $this->attribute('uid')){
			$this->setUid((int)$nId);
		}
		if($bMine){
			$this->setMine($bMine);
			$this->setModel($aCurrentId->model());
		}
		
		if(!$this->model())
		{
			$aDevice->write("Namecard widget 没有设置 user model") ;
			return ;
		}		
		parent::display($aUI, $aVariables,$aDevice);
	}
	
	public function displayType(){
		$sTemplateName = '';
		switch ($this->templateName()){
			case 'coresystem:NameCard_simple.html':
				$sTemplateName = 'simple';
				break;
			case 'coresystem:NameCard_normal.html':
				$sTemplateName = 'normal';
				break;
		}
		return $sTemplateName;
	}
	public function setDisplayType($sDisplayType){
		switch ($sDisplayType){
			case 'simple':
				$this->setTemplateName('coresystem:NameCard_simple.html');
				break;
			case 'normal':
				$this->setTemplateName('coresystem:NameCard_normal.html');
				break;
			default:
				$this->setTemplateName('coresystem:NameCard_normal.html');
		}
	}
	/**
	 * 使用用户id为namecard设置信息来源
	 * @param int $nId
	 */
	public function setUid($nId)
	{
		$aModel = UserModel::byUId($nId);
	    if(empty($aModel))
	    {
    		// 使用享元
    		if( !$aModel = Model::flyweight(array(__CLASS__,'user',$nId),false) )
    		{
    			$this->nId = $nId;
    			
    			$arrUserBean = array(
    				'class' => 'model' ,
    				'orm' => array(
    					'table' => 'coresystem:user' ,
    					'hasOne:info' => array(
    						'table' => 'coresystem:userinfo' ,
    					) ,
    				) ,
    			);
    			
    			$aModel = BeanFactory::singleton()->createBean($arrUserBean,'coresystem');
    			$aModel->load(array($nId),array('uid'));
    			// 保存享元
    			Model::setFlyweight($aModel,array(__CLASS__,'user',$nId)) ;
    		}
	    }
// 	    $aModel->printStrcut();exit;
		$this->setModel($aModel);
	}
	
	public function uid(){
		if($this->nId){
			return $this->nId;
		}else{
			return $this->model()->data('uid');
		}
	}
	
	/**
	 * 使用正在登录中的ID来显示名片
	 * @param bool $bMine 为true则使用当前登录用户的数据来显示名片
	 */
	public function setMine($bMine=true){
		$this->bMine = (bool)$bMine;
	}
	
	public function isMine(){
		return $this->bMine;
	}
	
	/**
	 * 取得头像地址
	 * @throws Exception 如果模型中没有需要的列
	 */
	static public function faceUrl($aModel)
	{
		//检查需要的列是否存在,目前支持头像地址(avatar), 以后支持更多
		if(!$aModel['info.avatar'])
		{
			$sFaceUrl = Extension::flyweight('coresystem')->metainfo()->installPath().'/public/images/defaultavatar.jpg';
		}else
		{
			if(strpos($aModel['info.avatar'], 'http://' , 0)==0 || strpos($aModel['info.avatar'], '/' , 0)==0){
				$sFaceUrl =  $aModel['info.avatar'] ;
			}else{
				$sFaceUrl = Extension::flyweight('coresystem')->filesFolder()->path() . '/avatar/' . $aModel['info.avatar'] ;
			}
		}
		return $sFaceUrl;
	}
	
	/**
	 * 当前显示的用户是否是正在登录的用户
	 * 
	 * @return boolean
	 */
	public function isCurrent(){
		return IdManager::singleton()->currentId()->userId() == $this->model()->data('uid');
	}
	
	/**
	 * 取得登录过的id的model的数组
	 * 
	 * @return array
	 */
	public function idIterator(){
		$arrModels = array();
		foreach(IdManager::singleton()->iterator() as $aId){
			$arrModels[] = $aId->model();
		}
		return $arrModels;
	}
	
	/**
	 * 数据来源
	 * @var IModel 
	 */
	private $aModel;
	private $bMine = false;
	private $nId;
}

