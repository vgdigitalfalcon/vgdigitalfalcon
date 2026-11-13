const fs = require('fs');

const htmlFiles = [
  'ecommerce-seo-service.html',
  'public_html (1)/ecommerce-seo-service.html',
  'public_html (1)/website/ecommerce-seo-service.html'
];

let failed = false;

htmlFiles.forEach(file => {
  if (!fs.existsSync(file)) {
    console.error(`File missing: ${file}`);
    failed = true;
    return;
  }
  const content = fs.readFileSync(file, 'utf8');

  // Check copy fixes
  if (!content.includes('As a global performance-driven ecommerce SEO agency')) {
    console.error(`Missing 'As a global performance-driven ecommerce SEO agency' in ${file}`);
    failed = true;
  }
  if (content.includes('best ecommerce SEO agency in the USA')) {
    console.error(`Old USA copy still found in ${file}`);
    failed = true;
  }
  if (!content.includes('scales sustainably. Avoid the trap')) {
    console.error(`Missing 'scales sustainably. Avoid the trap' in ${file}`);
    failed = true;
  }
  if (/\bvoid the trap/i.test(content)) {
    console.error(`Old 'void the trap' still found in ${file}`);
    failed = true;
  }

  // Check card 1
  if (!content.includes('vg-pillar-serp-card') || !content.includes('vg-serp-hero-listing') || !content.includes('Pure Mulberry Silk Sleepcase')) {
    console.error(`Card 1 elements missing in ${file}`);
    failed = true;
  }

  // Check card 2
  if (!content.includes('vg-pillar-speed-card') || !content.includes('vg-speed-gauge-svg') || !content.includes('vg-gauge-num')) {
    console.error(`Card 2 elements missing in ${file}`);
    failed = true;
  }

  // Check card 3
  if (!content.includes('vg-pillar-auth-card') || !content.includes('vg-network-svg') || !content.includes('vg-shield-crest')) {
    console.error(`Card 3 elements missing in ${file}`);
    failed = true;
  }

  // Check script tag
  if (!content.includes('assets/js/ecom-why-animations.js')) {
    console.error(`Script tag missing in ${file}`);
    failed = true;
  }

  // Check tag balances
  const openDivs = (content.match(/<div(\s|>)/g) || []).length;
  const closeDivs = (content.match(/<\/div>/g) || []).length;
  if (openDivs !== closeDivs) {
    console.error(`Mismatched <div> tags in ${file}: open=${openDivs}, close=${closeDivs}`);
    failed = true;
  }

  const openSvg = (content.match(/<svg(\s|>)/g) || []).length;
  const closeSvg = (content.match(/<\/svg>/g) || []).length;
  if (openSvg !== closeSvg) {
    console.error(`Mismatched <svg> tags in ${file}: open=${openSvg}, close=${closeSvg}`);
    failed = true;
  }

  console.log(`Verified ${file} - all checks passed!`);
});

const cssFiles = [
  'assets/css/color.css',
  'public_html (1)/assets/css/color.css',
  'public_html (1)/website/assets/css/color.css'
];

cssFiles.forEach(cssFile => {
  const css = fs.readFileSync(cssFile, 'utf8');
  const openBraces = (css.match(/\{/g) || []).length;
  const closeBraces = (css.match(/\}/g) || []).length;
  if (openBraces !== closeBraces) {
    console.error(`CSS braces mismatch in ${cssFile}: { = ${openBraces}, } = ${closeBraces}`);
    failed = true;
  } else {
    console.log(`CSS verified in ${cssFile} ({ = ${openBraces}, } = ${closeBraces})`);
  }
});

if (failed) {
  process.exit(1);
} else {
  console.log('ALL WHY SECTION CHECKS PASSED 100%!');
}
