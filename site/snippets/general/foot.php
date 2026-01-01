
<?php // move to a separate js file and call it in the head snippet?>

<script async defer>

  const htmlElement = document.querySelector(':root')
  htmlElement.classList.add('js');
  var mainNav = document.querySelector('#mainNav');
  var mainNavigation = document.querySelector('#mainNavHeader');
  var toggle = document.querySelector('#menu-toggle');
  // var menu = document.querySelector('#menu');

  toggle.addEventListener('click', function(){
    if (mainNavigation.classList.contains('is-active')) {
      this.setAttribute('aria-expanded', 'false');
      // menu.classList.remove('is-active');
      mainNav.classList.remove('is-active');
      mainNavigation.classList.remove('is-active');
    } else {
      // menu.classList.add('is-active');
      mainNav.classList.add('is-active');
      mainNavigation.classList.add('is-active');
      this.setAttribute('aria-expanded', 'true');
    }
  });
  window.onresize = function(event) {
    if (window.screen.width >= 768) {
      toggle.setAttribute('aria-expanded', 'false');
      mainNav.classList.remove('is-active');
      mainNavigation.classList.remove('is-active');
    }
  };
</script>
</body>
</html>