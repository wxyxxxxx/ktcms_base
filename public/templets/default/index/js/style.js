/*导航*/
$('.navbar-toggle').click(function(){
		$('.nav-phone').toggle();
		$(this).find("span").toggleClass("show");
	});
/*碧果展示*/
	$('.bgzs .pull-right .thumbnail').hover(function(){
		$(this).find(".bj").addClass("on");
		$(this).find("a img").addClass("active");
	},function(){
		$(this).find(".bj").removeClass("on");
		$(this).find("a img").removeClass("active");
	})


