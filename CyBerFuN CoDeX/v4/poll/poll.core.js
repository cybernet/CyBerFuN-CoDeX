	var jq = jQuery.noConflict();
function loadpoll()
	{
		jq("#loading_poll").html("Loading...");
		jq("#loading_poll").fadeIn("fast");
		jq("#poll_container").fadeIn("slow", function () {
		 
		  jq.post("poll/poll.core.php", {action:"load"}, function (r){ jq("#poll_container").html(r); 
			if(jq("#results").hasClass("results"))
			{
				jq("div[id='poll_result']").each(function(){
					var percentage = jq(this).attr("name");
					
					jq(this).css({width: "0%"}).animate({
					width: percentage+"%"}, 1600);
					
					});
			 jq("#loading").fadeOut("fast"); 		
			}
		 
		},"html" );});
	}
	function vote()
	{
		var pollId = jq("#pollId").val();
		var choice = jq("#choice").val();
		jq("#poll_container").empty();
		jq("#poll_container").append("<div id=\"loading_poll\" style=\"display:none\"><\/div>");
		jq("#loading_poll").fadeIn("fast", function () {jq("#loading_poll").html("Please wait while your vote is stored...");});
		
			jq.post("poll/poll.core.php",{action:"vote",pollId:pollId,choice:choice}, function(r)
			{
				if(r.status == 0 )
				jq("#loading_poll").fadeIn("fast", function () {jq("#loading_poll").empty(); jq("#loading_poll").html(r.msg);});
				else if(r.status == 1 )
				{
				jq("#loading_poll").empty();
				loadpoll();
				}
			},"json");
		
	
	}
	function addvote(val)
	{
		jq("#choice").val(val);
		jq("#vote_b").show("fast");
	}