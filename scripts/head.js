window.onscroll = function() {myFunction()};

var navbar = document.getElementById("navbar");
var sticky = navbar.offsetTop;

function myFunction() {
  if (window.pageYOffset >= sticky) {
    navbar.classList.add("sticky");
	document.querySelector('body > main').classList.add("main-sticky");
  } else {
    navbar.classList.remove("sticky");
	document.querySelector('body > main').classList.remove("main-sticky");
  }
}
