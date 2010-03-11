function feltoltott_torrentek(tnev,kereses){
   if ($('#'+kereses).is(":hidden")) {
		$('#'+kereses).fadeIn("slow");
	}
$('#'+kereses).html('<center><img src=\"pic/searching.gif\" width=\"105\" height=\"16\" /><br /><strong>Searching...</strong></center>');

	$.get("check_tname.php", { torrent_nev: tnev },
	  function(data){
		$('#'+kereses).html(data);
	  });
}


function lejon(id)
{
  
	var rejteni = '#' + id;

	if ($(rejteni).css('display') == 'none') {
  		$(rejteni).show('slow',function(){$(rejteni).fadeTo('slow',1)});
  		

	}

}

function becsuk(id)
{
  
	var rejteni = '#' + id;

	if ($(rejteni).css('display') != 'none') {
  		$(rejteni).fadeTo('slow',0,function(){$(rejteni).hide('slow');});
  		

	}

}


function ellenoriz(){
var szab = document.getElementById('szab');
var seed = document.getElementById('seed');
if(szab.checked==true && seed.checked==true){
lejon('kat5');
document.getElementById('feltolt').disabled=false;
}
else{
becsuk('kat5');
document.getElementById('feltolt').disabled=true;
}
}