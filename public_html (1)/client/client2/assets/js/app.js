document.querySelectorAll('[data-confirm]').forEach(el => {
  el.addEventListener('submit', e => {
    if (!confirm('Delete this record?')) e.preventDefault();
  });
});
