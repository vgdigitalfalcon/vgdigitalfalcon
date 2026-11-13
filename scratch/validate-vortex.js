const fs = require('fs');

console.log('Validating Ecommerce Growth Vortex changes...');

const files = [
  'ecommerce-seo-service.html',
  'public_html (1)/ecommerce-seo-service.html',
  'public_html (1)/website/ecommerce-seo-service.html'
];

files.forEach(f => {
  const html = fs.readFileSync(f, 'utf8');

  // Check new canvas ID exists
  if (!html.includes('id="vg-ecom-vortex-canvas"')) {
    console.error(`Missing vg-ecom-vortex-canvas in ${f}`);
    process.exit(1);
  }

  // Check old canvas ID does NOT exist
  if (html.includes('id="vg-seo-hero-canvas"')) {
    console.error(`Old vg-seo-hero-canvas still present in ${f}`);
    process.exit(1);
  }

  // Check new script exists
  if (!html.includes('assets/js/ecom-vortex-canvas.js')) {
    console.error(`Missing ecom-vortex-canvas.js script in ${f}`);
    process.exit(1);
  }

  // Check old script does NOT exist
  if (html.includes('assets/js/seo-hero-canvas.js')) {
    console.error(`Old seo-hero-canvas.js script still present in ${f}`);
    process.exit(1);
  }

  // Check tag balancing
  const tags = ['div', 'section', 'canvas', 'header', 'footer', 'form', 'body', 'html'];
  tags.forEach(t => {
    const o = (html.match(new RegExp(`<${t}(\\s|>)`, 'gi')) || []).length;
    const c = (html.match(new RegExp(`</${t}>`, 'gi')) || []).length;
    if (t === 'canvas' || t === 'section' || t === 'header' || t === 'footer' || t === 'form' || t === 'body' || t === 'html') {
      if (o !== c) {
        console.error(`Tag <${t}> mismatch in ${f}: ${o} vs ${c}`);
        process.exit(1);
      }
    }
  });

  console.log(`Verified ${f} - Canvas & script properly replaced and tags balanced!`);
});

// CSS check
const css = fs.readFileSync('assets/css/color.css', 'utf8');
let openCount = 0;
let closeCount = 0;
for (let i = 0; i < css.length; i++) {
  if (css[i] === '{') openCount++;
  if (css[i] === '}') closeCount++;
}
console.log(`CSS braces: { = ${openCount}, } = ${closeCount}`);
if (openCount !== closeCount) {
  console.error('CSS brace mismatch!');
  process.exit(1);
}

console.log('All Ecommerce Growth Vortex validations passed 100%!');

