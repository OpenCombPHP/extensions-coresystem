/* 放置完整的namecard
 * $nUid 可选 , 用户id
 * $sName 可选 , 用户昵称
 * $aTarget 必须 , 放置位置的参照物
 * $position 必须 , 上方(默认)还是遮盖(传true)
 */
function getNameCardExtension(nUid , sName , sService , aTarget , position , bDisappearType)
{
	if(nUid == null && sName == null)
	{
		if(typeof console != 'undefined'){
			console.log('没有指定用户,无法显示用户namecard');
		}
		return;
	}
	
	if(aTarget.next('.namecard_normal_card_full').length == 1){
		var full = aTarget.next('.namecard_normal_card_full');
		positionNameCardExtension(full ,aTarget, position);
		full.show();
		return;
	}
	
	var params = '';
	if(nUid != null){
		params = '&uid='+nUid;
	}else{
		params = '&nickname='+sName;
	}
	if(typeof sService == 'undefined'){
		sService = 'wownei';
	}
	
	jquery.ajax({
		url: "?c=org.opencomb.coresystem.namecard.NameCardExtension"+params+"&service="+sService+"&rspn=noframe"
		
		, dataType:'html'
			
		, beforeSend: function(){
			if(aTarget.find('.namecard_normal_loading').length == 1){
				return;
			}
			var loading = jquery("<div class='namecard_normal_loading'></div>");
			aTarget.after(loading);
			positionNameCardExtension(loading ,aTarget);
		}
	
		, success: function(html) {
			var fullCard = jquery(html).find('.namecard_normal_card_full');
			aTarget.next('.namecard_normal_loading').replaceWith(fullCard);
			
			positionNameCardExtension(fullCard , aTarget, position);
			fullCard.show();

			fullCard.on('mouseleave',function(){
				jquery('.namecard_normal_card_full , .namecard_normal_loading').hide();
			});
			if(bDisappearType){
				aTarget.on('mouseleave',function(){
					jquery('.namecard_normal_card_full , .namecard_normal_loading').hide();
				});
			}
		}
	});
}

function positionNameCardExtension(fullCard , aTarget , position){
	//位置
	var top = aTarget.position().top -6;
	var left = aTarget.position().left -6;
//	if(position == false){
//		top = top;
//	}
	fullCard.css({
		'top':top,
		'left':left
	});
}