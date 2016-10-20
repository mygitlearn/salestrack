$(function(){
	$(".but").click(function(){
		var id = $(this).parent().find(".id").html();
		var url = $(this).parent().find(".address").html();
		if (id) {
			status = 1;
		}else{
			alert("此货物信息有误，无法接收");
		};
		$.ajax({
			"url": url,
			"data": {id:id, status:status},
			"type": "POST",
			success:function(data){
				// alert("接收成功");
				window.location.reload();
			},
			error:function(data){
				return;
			}
		})
	})

	$("#search").click(function(){
		var url = $(this).attr('url').substring(0,44);
		url = url + "index";
		var condition = $(".search-input").val();
		condition == ""? url = url : url = url + "/condition/" + condition;
		window.location.href = url;
	})


});