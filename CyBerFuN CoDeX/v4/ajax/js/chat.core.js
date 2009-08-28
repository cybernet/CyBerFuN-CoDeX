function chat_alert()
	{
		$.post("ajax/chat.php", {action:"chat_alert"}, function (r)
		{
			
			if(r)
			{
				var class_check = $("#room_"+r.room).hasClass("room_body");
				var userAgent = navigator.userAgent;
				if (class_check == true)
				{
				chat_focus(r.room)
				}
				else
				{
				$("#alert_box").remove();
				$("#rooms").append("<div align=\"center\" class=\"alert_box\" title=\"Click to drag\" id=\"alert_box\"><\/div>");
				$("#alert_box").append("Hey, your friend <a href=\"userdetails.php?id="+r.user+"\"><b>"+r.username+"</b></a> wants to talk to you!<br/><input type=\"button\" onclick=\"render_chatroom("+r.room+",'"+r.username+"');close_alert();\" value=\"Accept\">&nbsp;<input type=\"button\" onclick=\"reject_chat("+r.room+",'show');\" value=\"Reject\">");
				
				$("#alert_box").css("top",(Math.round($(window).height()/2)+$(window).scrollTop())+"px" );
				$("#alert_box").css("left",(Math.round($(window).width()/2))-(Math.round($("#alert_box").width()/2))+"px" );
				//check if the browser is IE to know if we add round corners 
				//if((/MSIE/.test(userAgent) == false)) {
				$("#alert_box").css("-moz-border-radius",5);
				//}
				$("#alert_box").draggable();
				$("#alert_box").draggable('option', 'cursor', "move");
				$("#alert_box").draggable('option', 'containment', "document");
				$("#alert_box").draggable('option', 'opacity', 0.35);
				}
			}
			
		}
		,"json"
		); 
		setTimeout("chat_alert()",35000);
	}
	function chat_request(user)
	{
		$(document).fadeIn("normal", function () {$.post("ajax/chat.php", {action:"chat_request",to:user}, function (r) { render_chatroom(r.room,r.username)},"json");} );
	}
	
	function chat_status(room_id,action)
	{
		if(action == "progress")
		$.post("ajax/chat.php", {action:"progress",room:room_id});
		if(action == "close")
		$.post("ajax/chat.php", {action:"close_room",room:room_id});
	}
	function close_alert()
	{
		$("#alert_box").fadeOut(100, function () {$("#alert_box").remove();});
	}
	function reject_chat(room,action)
	{
	if(action == "show"){
		var userAgent = navigator.userAgent;
		$("#alert_box").remove();
		$("#rooms").append("<div align=\"center\" class=\"alert_box\" title=\"Click to drag\" id=\"alert_box\"><\/div>");
		$("#alert_box").empty().append("Reason:<br/><input type=\"text\" id=\"reject_reason\" size=\"60\" onchange=\"reject_chat("+room+",'send');\"/>");
				
		$("#alert_box").css("top",(Math.round($(window).height()/2)+$(window).scrollTop())+"px" );
		$("#alert_box").css("left",(Math.round($(window).width()/2))-(Math.round($("#alert_box").width()/2))+"px" );
		
		$("#alert_box").css("-moz-border-radius",5);
	
		$("#alert_box").draggable();
		$("#alert_box").draggable('option', 'cursor', "move");
		$("#alert_box").draggable('option', 'containment', "document");
		$("#alert_box").draggable('option', 'opacity', 0.35);
		}
		else if(action == "send")
		{
			//var msgTo = curUser+" rejected the chat request.Reason :"+;
			$.post("ajax/chat.php", {room:room,shout:$("#reject_reason").val(),action:"reject"}); 
			close_alert();
		}
	}
	function global_refresh()
	{
		$("input[id='room_id']").each(function() {
			var room = $(this).val();
			refresh(room);
		});
		setTimeout("global_refresh()", 25000);
	}
	function delete_shout(room_id,shout_id)
	{
		
		$("img[id='delete_button_"+room_id+"']").each(function() {
			$(this).fadeOut(100);
			
		});
		$.post("ajax/chat.php", {room:room_id,shoutid:shout_id,action:"delete"}); 
		refresh(room_id);
		
	}
	
	function refresh(room_id)
	{		var act = $("input[value='focus']:first").attr("alt");
		$("#loading_"+room_id).fadeIn("slow",function () {
		$.post("ajax/chat.php", {room:room_id}, function(data){ $("#msg_"+room_id).html(data); 
		if (act == "shout")
			{
			$("img[id='edit_button_"+room_id+"']").each(function() {
				$(this).show();
			});
			}},"html");
		
			
		$("#loading_"+room_id).fadeOut("fast");
		});

			
			
	}
	function edit_shout(room_id,shout_id)
	{
		$.post("ajax/chat.php", {room:room_id,shoutid:shout_id,action:"get_shout"}, function (edit){$("#shout_"+room_id).val(edit);},"text");
		$("#room_"+room_id).append("<input type=\"hidden\" name=\"edit\" id=\"edit_"+room_id+"\" value=\""+shout_id+"\"  />");
		$("#focus_"+room_id).attr("alt","edit");
		$("img[id='edit_button_"+room_id+"']").each(function() {
			$(this).fadeOut(200);
			
		});
	}
	
	//this will handle key press:)
	$(document).keyup(function (e){
	
		var room_id = $("input[value='focus']:first").attr("name");
		var action = $("input[value='focus']:first").attr("alt");
		
		if(e.keyCode == 13)
		{
			if(action == "shout"){
			$.post("ajax/chat.php", {room:room_id,shout:$("#shout_"+room_id).val(),action:"shout"}); 
			$("#shout_"+room_id).val("");
			refresh(room_id);
			}
			if(action == "edit")
			{
				shout_id = $("#edit_"+room_id).val();
				$.post("ajax/chat.php", {room:room_id,shout:$("#shout_"+room_id).val(),shoutid:shout_id,action:"edit_shout"}); 
				$("#edit_"+room_id).remove();
				$("#focus_"+room_id).attr("alt","shout");
				$("#shout_"+room_id).val("");
				refresh(room_id);
			}
			
		}
	});

	function render_chatroom(room_id,user_name)
	{
		var class_check = $("#room_"+room_id).hasClass("room_body");
		var userAgent = navigator.userAgent;
		//alert(userAgent);
			
		if( class_check != true ){
		$("#rooms").append("<div id=\"room_"+room_id+"\" onclick=\"chat_focus("+room_id+");\" class=\"room_body\"><b>"+user_name+"</b> -- instant chat<\/div>");
		$("#room_"+room_id).append("<div class=\"move\" id=\"handle_"+room_id+"\" ><img src=\"ajax/edit.png\" width=\"13\" height=\"13\" border=\"0\" alt=\"Move\" title=\"Move me!\" /><\/div>");
		$("#room_"+room_id).append("<div class=\"close\" ><img src=\"ajax/delete.png\" width=\"13\" height=\"13\" border=\"0\" alt=\"Close\" title=\"Close me!\" onclick=\"close_chatroom("+room_id+")\" /><\/div>");
		$("#room_"+room_id).append("<div id=\"loading_"+room_id+"\" class=\"loading\" ><img src=\"ajax/ajax_loader.gif\" width=\"18\" height=\"18\" border=\"0\" alt=\"Loading...\" /><\/div>");
		$("#room_"+room_id).append("<div id=\"msg_"+room_id+"\" class=\"room_msg\"><\/div><input type=\"hidden\" name=\"room_id\" id=\"room_id\" value=\""+room_id+"\"  />");
		$("#room_"+room_id).append("<input type=\"hidden\" name=\""+room_id+"\" alt=\"shout\" id=\"focus_"+room_id+"\" value=\"un_focus\"  />");
		$("#room_"+room_id).append("<input type=\"text\" name=\"shout_"+room_id+"\" id=\"shout_"+room_id+"\" class=\"room_shout\"/>");
		
		$("#room_"+room_id).css("top",(120+$(window).scrollTop())+"px" );
		$("#room_"+room_id).css("left","120px" );
		$("#room_"+room_id).css("position","fixed" );
			
		$("#room_"+room_id).css("-moz-border-radius",5);
			
		//drag thing:)
		$("#room_"+room_id).draggable();
		$("#room_"+room_id).draggable('option', 'cursor', "move");
		$("#room_"+room_id).draggable('option', 'containment', "document");
		$("#room_"+room_id).draggable('option', 'handle', "img");
		$("#room_"+room_id).draggable('option', 'opacity', 0.35);
		
		friends_refresh();
		chat_status(room_id,"progress");
		chat_focus(room_id);
		refresh(room_id);
		}
	}
	
	function close_chatroom(room_id)
	{
		answer = confirm('Close chat room ?');
		if(answer == true){
		$("#room_"+room_id).fadeOut(400, function () {$("#room_"+room_id).remove();});
		chat_status(room_id,"close");
		friends_refresh();
		}
	
		
	}
	function chat_focus(room_id)
	{
		$("div[id^='room_']").each(function() {
			$(this).css("opacity",0.35);
			
		});
		$("input[id^='focus_']").each(function() {
			$(this).val("un_focus");
			
		});
		$("#room_"+room_id).css("opacity",1);
		$("#focus_"+room_id).val("focus");
	}
	function friends_refresh()
	{
		$.post("ajax/chat.php", {action:"onlineList"}, function (online)
		{
			$("#online_friends").empty();
			$("#online_friends").append("<fieldset id=\"online_set\"></fieldset>");
					
			$("#online_friends").css("top",Math.round($("#show_online").offset().top+16)+"px");
			$("#online_friends").css("left",Math.round($("#show_online").offset().left-$("#online_set").width())+"px");
					
			$("#online_set").css("width", "100px");
			$("#online_set").css("border", "1px #006699 solid");
			$("#online_set").css("padding", "3px");
			$("#online_set").css("text-align", "left");
			$("#online_set").css("background", "#cccccc");
			$("#online_set").css("opacity", 0.9);
			$("#online_set").html(online);
		}); 
		setTimeout("friends_refresh()",60000);
	}
	
	function friends_online()
	{
		var state = $("#online_friends").css("display");
		if(state == "none")
		{
			$("#online_friends").slideDown("normal",
				function(){
					friends_refresh();
				
				});
		}
		else if( state != "none")
			$("#online_friends").slideUp("fast");
		
	}