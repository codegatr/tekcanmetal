  </main>
  <footer class="adm-foot">
    <div>© <?= date('Y') ?> Tekcan Metal — Yönetim Paneli</div>
    <div>Powered by <a href="https://codega.com.tr" target="_blank">Codega</a> v<?= h(TM_VERSION) ?></div>
  </footer>
</div>

<script>
(function(){
  // Sidebar toggle (mobile)
  const sb = document.getElementById('admSidebar');
  const btn = document.getElementById('admMenuBtn');
  if (btn && sb) {
    btn.addEventListener('click', () => sb.classList.toggle('open'));
    document.addEventListener('click', (e) => {
      if (window.innerWidth > 1024) return;
      if (!sb.contains(e.target) && !btn.contains(e.target)) sb.classList.remove('open');
    });
  }

  // Confirm dialogs
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', e => {
      if (!confirm(el.dataset.confirm || 'Onaylıyor musunuz?')) e.preventDefault();
    });
  });

  // Auto-close flash
  document.querySelectorAll('.adm-flash').forEach(f => {
    setTimeout(() => f.style.opacity = '0', 4000);
    setTimeout(() => f.remove(), 4500);
  });
})();
</script>
</body>
</html>
