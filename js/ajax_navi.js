var navi='.page_navi';
var navi_a='.page_navi a';
var content='#contendlist';

$(document).ready(function ajax_navi(){
	$(navi_a).click(function()
		{
			var z = $(this).attr("href");
			$.ajax({
				url: z,
				type:"POST",
				data:"action=ajax_navi",
				beforeSend:function()
				{
					//alert('begin');
					document.body.style.cursor = 'wait';
					var C=0.7;
					$(content).css({opacity:C,MozOpacity:C,KhtmlOpacity:C,filter:'alpha(opacity=' + C * 100 + ')'});				
					$(navi).html('<a>载入中...</a>');
				},
				error: function(request) 
				{
					alert('ERROR!');
					alert(request.responseText);
				},			
				success: function (data)
				{
					$(content).html(data);					
					document.body.style.cursor = 'auto';
					var C=1; 
					$(content).css({opacity:C,MozOpacity:C,KhtmlOpacity:C,filter:'alpha(opacity=' + C * 100 + ')'});
					ajax_navi();//翻页后DOM变化了,需要重新绑定函数
					jQuery('html, body').animate({scrollTop:$(content).offset().top - 100}, 'slow');
				}
			});
			return false;
		});
})