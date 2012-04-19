/* 放置完整的namecard
 * $nUid 可选 , 用户id
 * $sName 可选 , 用户昵称
 * $aTarget 必须 , 放置位置的参照物
 * $position 必须 , 上方(默认)还是遮盖(传true)
 */
function getNameCardExtension(nUid,sName, aTarget , position)
{
	if(nUid == null && sName == null)
	{
		if(typeof console != 'undefind'){
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
	
	jquery.ajax({
		url: "?c=org.opencomb.coresystem.namecard.NameCardExtension"+params+"&rspn=noframe"
		, dataType:'html'
		, beforeSend: function(){
			aTarget.find('.namecard_normal_loading').show();
		}
		, success: function(html) {
			aTarget.find('.namecard_normal_loading').hide();
			var fullCard = jquery(html).find('.namecard_normal_card_full');
			aTarget.after(fullCard);
			
			positionNameCardExtension(fullCard , aTarget, position);
			fullCard.show();
			
			fullCard.on('mouseout',function(){
				fullCard.hide();
			});
		}
	});
}

function positionNameCardExtension(fullCard , aTarget , position){
	//位置
	var top = aTarget.position().top -5;
	var left = aTarget.position().left -5;
	if(position == true){
		
	}
	fullCard.css({
		'top':top,
		'left':left
	});
}