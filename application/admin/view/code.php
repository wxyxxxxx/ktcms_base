<script src="http://cdn.bootcss.com/html2canvas/0.5.0-beta4/html2canvas.js"></script>
<script type="text/javascript">



$(document).ready(function(){

	var Heightsc = $(window).height();
	var FooterHc = $(".footer").height();

    var w = 260;
    var h = 260;



    html2canvas($("#code"), {
      // canvas:canvas,
      onrendered: function(canvas) {
          // document.body.innerHTML='';
          var dataUrl = canvas.toDataURL("image/png");  
          var newImg = document.createElement("img");
          newImg.src =  dataUrl;
          newImg.width = w;
          newImg.height = h;
          console.log(newImg);

          $("#img").remove();
          $("#code").html(newImg);
          // document.getElementById('w_view2').appendChild(newImg);
      }
    });
});
</script>