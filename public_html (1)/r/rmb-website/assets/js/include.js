/* ==========================================================================
   include.js
   Loads partials/header.html and partials/footer.html into any page that
   contains <div id="site-header"></div> and <div id="site-footer"></div>.

   NOTE: fetch() requires the site to be served over http(s), not opened via
   file://. Run a local server from the project root, e.g.:
     python3 -m http.server 8000
   then visit http://localhost:8000/index.html
   ========================================================================== */

async function loadPartial(url, targetId) {
  const target = document.getElementById(targetId);
  if (!target) return;
  try {
    const res = await fetch(url);
    if (!res.ok) throw new Error(`Failed to load ${url}: ${res.status}`);
    target.innerHTML = await res.text();
  } catch (err) {
    console.error(err);
    target.innerHTML = `<!-- ${url} failed to load. Serve this site via a local server (see include.js). -->`;
  }
}

function setActiveNavLink() {
  const page = document.body.dataset.page;
  if (!page) return;
  const link = document.querySelector(`.rmb-nav [data-nav="${page}"]`);
  if (link) link.classList.add('active');
}

document.addEventListener('DOMContentLoaded', async () => {
  await Promise.all([
    loadPartial('partials/header.html', 'site-header'),
    loadPartial('partials/footer.html', 'site-footer'),
  ]);
  setActiveNavLink();

  // Close mobile navbar after clicking a link
  document.querySelectorAll('#rmbNavbar .nav-link, #rmbNavbar .dropdown-item').forEach(link => {
    link.addEventListener('click', () => {
      const navbar = document.getElementById('rmbNavbar');
      if (navbar.classList.contains('show')) {
        bootstrap.Collapse.getOrCreateInstance(navbar).hide();
      }
    });
  });

  // Let other page scripts know header/footer are ready
  document.dispatchEvent(new CustomEvent('partialsLoaded'));
});
