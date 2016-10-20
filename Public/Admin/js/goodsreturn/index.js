$(function(){
	$(".btn").click(function(){
		var big_code = $(this).parent().find("textarea[name='big_code']").val();
		var small_code = $(this).parent().find("textarea[name='small_code']").val();
		var firms = $(this).parent().find("#firms").val();
		var reason = $(this).parent().find("#reason").val();
		var url = $(this).attr("url");
		url = url + "/backgoods";

		$.ajax({
			"url": url,
			"data": {big_code:big_code, small_code:small_code, firms:firms, reason:reason},
			"type": "POST",
			success:function(data){
				alert("已提交退货信息");
			},
			error:function(data){
				alert("信息有误");
			}

		})

	})




})