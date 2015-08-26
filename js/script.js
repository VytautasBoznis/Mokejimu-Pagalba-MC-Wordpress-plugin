$(document).ready(function(){
  $("#wrap").mouseover(function(){
   $("#video").stop().slideDown("slow");
  });
  $("#wrap").mouseout(function(){
   $("#video").slideUp("slow");
  });
 });
  