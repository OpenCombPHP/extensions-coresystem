<?php
namespace org\opencomb\coresystem\widget;

use org\jecat\framework\io\IOutputStream;
use org\jecat\framework\util\IHashTable;
use org\jecat\framework\ui\UI;
use org\jecat\framework\mvc\model\IModel;
use org\jecat\framework\mvc\view\widget\Widget;

/**
 * @wiki /CoreSystem/控件/名片
 * 
 * 名片控件,用来显示用户的信息,例如昵称,头像,积分等等.
 * 
 * == 使用方法 ==
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
 * |template
 * |string
 * |'NameCard_normal.html'
 * |可选
 * |指定模板文件,指定这个属性后会忽略type属性
 * |-- --
 * |mine
 * |bool
 * |false
 * |可选
 * |是否使用正在登录的用户的信息来显示名片,该属性优先级高于model属性
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
		if($aModel = $this->attribute('model')){
			
			$this->setModel($aModel);
		}
		
		if($sDisplayType = $this->attribute('type')){
			$this->setDisplayType($sDisplayType);
		}
		
		if($sTemplateName = $this->attribute('template')){
			$this->setTemplateName($sTemplateName);
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
			case 'coresystem:NameCard_full.html':
				$sTemplateName = 'full';
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
			case 'full':
				$this->setTemplateName('coresystem:NameCard_full.html');
				break;
			default:
				$this->setTemplateName('coresystem:NameCard_normal.html');
		}
	}
	
	//使用正在登录中的ID来显示名片
	public function setMine($bMine){
		$this->bMine = (bool)$bMine;
	}
	
	public function isMine(){
		return $this->bMine;
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
	
	/**
	 * 数据来源
	 * @var IModel 
	 */
	private $aModel;
	private $bMine = false;
}

?>