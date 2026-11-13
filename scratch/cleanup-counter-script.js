const fs = require('fs');
const path = require('path');

const rootDir = path.resolve(__dirname, '..');

const files = [
  path.join(rootDir, 'ecommerce-seo-service.html'),
  path.join(rootDir, 'public_html (1)', 'ecommerce-seo-service.html'),
  path.join(rootDir, 'public_html (1)', 'website', 'ecommerce-seo-service.html')
];

files.forEach(f => {
  if (!fs.existsSync(f)) return;
  let html = fs.readFileSync(f, 'utf8');

  // Replace old counter script block with clean drawer and FAQ scripts
  const oldScriptRegex = /<!-- Preserved Counter Script -->[\s\S]*?const counters = document\.querySelectorAll\('\.counter'\);[\s\S]*?counters\.forEach\(counter => \{[\s\S]*?\}\);\s*/;
  if (oldScriptRegex.test(html)) {
    html = html.replace(oldScriptRegex, '<!-- Mobile Drawer & FAQ Accordion Interactions -->\n        <script>\n        document.addEventListener(\'DOMContentLoaded\', function() {\n');
    fs.writeFileSync(f, html, 'utf8');
    console.log('Cleaned up legacy counter script in:', f);
  }
});

console.log('Done cleaning legacy counter script.');

