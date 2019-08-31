$(document).on("scroll", function (){
	var pageTop = $(document).scrollTop();
	var pageBottom = pageTop + $(window).height();

	var tags = $(".section");
	for (var i = 0; i < tags.length; i++) {
		var tag = tags[i];

		if ($(tag).position().top < pageBottom) {
			$(tag).addClass('visible');
		} else {
			$(tag).removeClass('visible');
			$
		}
	}
	if ($(window).scrollTop() > 150) {
		$('#logo').addClass('visible');
	} else {
		$('#logo').removeClass('visible');
	}
});

function toggleMenu() {
  var x = document.getElementById("site-nav-links");
  if (x.className === "site-nav-links not-visible") {
    x.className = "site-nav-links visible";
  } else {
    x.className = "site-nav-links not-visible";
  }
}