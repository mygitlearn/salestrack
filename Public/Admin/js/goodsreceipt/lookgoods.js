$(function(){

	$("#search").click(function(){
		var id = $(this).attr('num');
		id == ""? id="" : id="/id/"+id;
		var url = $(this).attr('url').substring(0,44);
		url = url + "lookgoods";
		var condition = $(".search-input").val();
		condition == ""? url=url : condition="/condition/"+condition;
		url = url+id+condition;
		window.location.href = url;
	})


});