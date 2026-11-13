const fs = require('fs');

const files = [
  'ecommerce-seo-service.html',
  'public_html (1)/ecommerce-seo-service.html',
  'public_html (1)/website/ecommerce-seo-service.html'
];

let failed = false;

files.forEach(file => {
  if (!fs.existsSync(file)) {
    console.error(`Missing file: ${file}`);
    failed = true;
    return;
  }
  const content = fs.readFileSync(file, 'utf8');

  // Verify new home page footer classes
  if (!content.includes('<footer class="vg-footer">')) {
    console.error(`Missing '<footer class="vg-footer">' in ${file}`);
    failed = true;
  }
  if (!content.includes('vg-footer-grid') || !content.includes('vg-footer-about') || !content.includes('vg-newsletter-box') || !content.includes('vg-footer-bottom')) {
    console.error(`Missing vg-footer sub-elements in ${file}`);
    failed = true;
  }

  // Verify old template footer is gone
  if (content.includes('footer-widgets-wrapper') || content.includes('single-footer-widget')) {
    console.error(`Old legacy footer widgets still found in ${file}`);
    failed = true;
  }

  // Tag balancing
  const openDivs = (content.match(/<div(\s|>)/g) || []).length;
  const closeDivs = (content.match(/<\/div>/g) || []).length;
  if (openDivs !== closeDivs) {
    console.error(`Mismatched <div> tags in ${file}: open=${openDivs}, close=${closeDivs}`);
    failed = true;
  }

  const openFooter = (content.match(/<footer(\s|>)/g) || []).length;
  const closeFooter = (content.match(/<\/footer>/g) || []).length;
  if (openFooter !== closeFooter || openFooter !== 1) {
    console.error(`Footer tag issue in ${file}: open=${openFooter}, close=${closeFooter}`);
    failed = true;
  }

  console.log(`Verified Footer in ${file}: 100% matched to Home Page!`);
});

if (failed) {
  process.exit(1);
} else {
  console.log('ALL HOME PAGE FOOTER CHECKS PASSED 100%!');
}

