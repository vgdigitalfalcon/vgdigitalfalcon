const fs = require('fs');

console.log('Validating AI Laser Scan & Cipher Decrypt Upgrade...');

const files = [
  'ecommerce-seo-service.html',
  'public_html (1)/ecommerce-seo-service.html',
  'public_html (1)/website/ecommerce-seo-service.html'
];

files.forEach(f => {
  const html = fs.readFileSync(f, 'utf8');

  // Verify all 4 target values exist
  const targets = ['250', '3,200', '1.2M+', '3X'];
  targets.forEach(t => {
    if (!html.includes(`data-cipher-target="${t}"`)) {
      console.error(`Missing data-cipher-target="${t}" in ${f}`);
      process.exit(1);
    }
  });

  // Check that 1,200K+ is gone and replaced by 1.2M+
  if (html.includes('1,200K+')) {
    console.error(`Old 1,200K+ still present in ${f}`);
    process.exit(1);
  }

  // Check that laser scanner line and glare elements are present
  const laserLines = (html.match(/class="vg-laser-scanner-line"/g) || []).length;
  if (laserLines !== 4) {
    console.error(`Expected 4 laser scanner lines, found ${laserLines} in ${f}`);
    process.exit(1);
  }

  const glares = (html.match(/class="vg-card-glass-glare"/g) || []).length;
  if (glares !== 4) {
    console.error(`Expected 4 glares, found ${glares} in ${f}`);
    process.exit(1);
  }

  // Check that custom SVG icons exist and old png emojis are removed from the stats section
  const svgs = (html.match(/class="vg-ecom-metric-svg"/g) || []).length;
  if (svgs !== 4) {
    console.error(`Expected 4 custom SVG icons, found ${svgs} in ${f}`);
    process.exit(1);
  }

  if (html.includes('feature-icon/icon-6.png') || html.includes('feature-icon/icon-8.png')) {
    console.error(`Old emoji png still present in ${f}`);
    process.exit(1);
  }

  // Check script inclusion
  if (!html.includes('assets/js/ecom-metric-cipher.js')) {
    console.error(`Missing ecom-metric-cipher.js script tag in ${f}`);
    process.exit(1);
  }

  // Tag balancing
  const tags = ['div', 'section', 'header', 'footer', 'form', 'body', 'html', 'svg'];
  tags.forEach(t => {
    const o = (html.match(new RegExp(`<${t}(\\s|>)`, 'gi')) || []).length;
    const c = (html.match(new RegExp(`</${t}>`, 'gi')) || []).length;
    if (o !== c) {
      console.error(`Tag <${t}> mismatch in ${f}: ${o} vs ${c}`);
      process.exit(1);
    }
  });

  console.log(`Verified ${f} - All checks passed 100%!`);
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

console.log('All Cipher & Laser Upgrade validations passed 100%!');

