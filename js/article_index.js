$(document).ready(function(){
	$("#show-index").click(function()
	{
		if($("#show-index").html()=="[ 隐藏 ]")
		{
			$("#index-ul").fadeOut("normal");
			$("#show-index").html("[ 展开 ]");
		}else if($("#show-index").html()=="[ 展开 ]")
		{
			$("#index-ul").fadeIn("normal");
			$("#show-index").html("[ 隐藏 ]");
		}
		else
		{
			return false;
		}
	});
});