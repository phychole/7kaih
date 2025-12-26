<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function(){
  const btn = document.getElementById('sidebarToggle');
  const backdrop = document.getElementById('sidebarBackdrop');

  function isMobile(){ return window.matchMedia('(max-width: 992px)').matches; }

  btn?.addEventListener('click', () => {
    if (isMobile()) document.body.classList.toggle('sidebar-open');
    else document.body.classList.toggle('sidebar-collapsed');
  });

  backdrop?.addEventListener('click', () => {
    document.body.classList.remove('sidebar-open');
  });

  document.querySelectorAll('.menu-toggle').forEach(btn => {
    btn.addEventListener('click', function(){
      const sub = document.getElementById(this.dataset.target);
      if(!sub) return;
      sub.style.display = (sub.style.display === 'block') ? 'none' : 'block';
    });
  });

  document.querySelectorAll('.submenu').forEach(sub => {
    if (sub.querySelector('.active')) sub.style.display = 'block';
  });

  window.addEventListener('resize', () => {
    if (!isMobile()) document.body.classList.remove('sidebar-open');
  });
})();
</script>
</body>
</html>
