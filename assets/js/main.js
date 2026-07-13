document.addEventListener('DOMContentLoaded', function () {
  var navToggle = document.getElementById('navToggle');
  var mainNav = document.getElementById('main-nav');

  if (navToggle && mainNav) {
    navToggle.addEventListener('click', function () {
      var expanded = this.getAttribute('aria-expanded') === 'true';
      this.setAttribute('aria-expanded', String(!expanded));
      mainNav.classList.toggle('open');
      // simple show/hide for small screens
      if (mainNav.style.display === 'block') {
        mainNav.style.display = '';
      } else {
        mainNav.style.display = 'block';
      }
    });
  }
});