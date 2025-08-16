document.addEventListener('DOMContentLoaded', function(){
  const toggle = document.getElementById('readerModeToggle');
  if (toggle) {
    toggle.addEventListener('click', function(){
      document.body.classList.toggle('reader-light');
      localStorage.setItem('readerMode', document.body.classList.contains('reader-light') ? 'light' : 'dark');
    });
    const saved = localStorage.getItem('readerMode');
    if (saved === 'light') {
      document.body.classList.add('reader-light');
    }
  }

  const reader = document.querySelector('.reader-container');
  if (reader) {
    const imgs = reader.querySelectorAll('img');
    imgs.forEach(img => {
      const src = img.getAttribute('src');
      const i = new Image();
      i.onload = () => { img.src = src; };
      i.src = src;
    });
    // Arrow key navigation
    document.addEventListener('keydown', (e) => {
      const prev = document.querySelector('a.btn.btn-primary.btn-sm[href*="chapter="]');
      const nav = document.querySelectorAll('.reader-toolbar a.btn.btn-primary');
      let prevHref = null, nextHref = null;
      if (nav.length > 0) {
        nav.forEach(a => { if (a.textContent.trim().toLowerCase().startsWith('prev')) prevHref = a.href; if (a.textContent.trim().toLowerCase().startsWith('next')) nextHref = a.href; });
      }
      if (e.key === 'ArrowLeft' && prevHref) { window.location.href = prevHref; }
      if (e.key === 'ArrowRight' && nextHref) { window.location.href = nextHref; }
    });
  }
});