(function(){
  const toggle = document.querySelector('[data-reader-toggle]');
  if (toggle) {
    toggle.addEventListener('click', function(){
      document.body.classList.toggle('reading-light');
    });
  }

  // Smooth scroll to top
  document.querySelectorAll('[data-scroll-top]')?.forEach(function(btn){
    btn.addEventListener('click', function(){
      window.scrollTo({top: 0, behavior: 'smooth'});
    });
  });
})();