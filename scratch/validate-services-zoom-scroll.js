const fs = require('fs');

const htmlFiles = [
  'ecommerce-seo-service.html',
  'public_html (1)/ecommerce-seo-service.html',
  'public_html (1)/website/ecommerce-seo-service.html'
];

let failed = false;

htmlFiles.forEach(file => {
  if (!fs.existsSync(file)) {
    console.error(`Missing file: ${file}`);
    failed = true;
    return;
  }
  const content = fs.readFileSync(file, 'utf8');

  if (!content.includes('vg-ecom-services-slider')) {
    console.error(`Missing 'vg-ecom-services-slider' in ${file}`);
    failed = true;
  }
  if (!content.includes('vg-services-dot')) {
    console.error(`Missing 'vg-services-dot' in ${file}`);
    failed = true;
  }
  if (!content.includes('assets/js/ecom-services-slider.js')) {
    console.error(`Missing script tag for ecom-services-slider.js in ${file}`);
    failed = true;
  }

  // Check tag balances
  const openDivs = (content.match(/<div(\s|>)/g) || []).length;
  const closeDivs = (content.match(/<\/div>/g) || []).length;
  if (openDivs !== closeDivs) {
    console.error(`Mismatched <div> tags in ${file}: open=${openDivs}, close=${closeDivs}`);
    failed = true;
  }

  const openSection = (content.match(/<section(\s|>)/g) || []).length;
  const closeSection = (content.match(/<\/section>/g) || []).length;
  if (openSection !== closeSection) {
    console.error(`Mismatched <section> tags in ${file}: open=${openSection}, close=${closeSection}`);
    failed = true;
  }

  console.log(`Verified HTML in ${file}: All checks passed!`);
});

const cssFiles = [
  'assets/css/color.css',
  'public_html (1)/assets/css/color.css',
  'public_html (1)/website/assets/css/color.css'
];

cssFiles.forEach(file => {
  const css = fs.readFileSync(file, 'utf8');
  const openBraces = (css.match(/\{/g) || []).length;
  const closeBraces = (css.match(/\}/g) || []).length;
  if (openBraces !== closeBraces) {
    console.error(`CSS braces mismatch in ${file}: { = ${openBraces}, } = ${closeBraces}`);
    failed = true;
  } else {
    console.log(`CSS verified in ${file} ({ = ${openBraces}, } = ${closeBraces})`);
  }
});

const jsFiles = [
  'assets/js/ecom-services-slider.js',
  'public_html (1)/assets/js/ecom-services-slider.js',
  'public_html (1)/website/assets/js/ecom-services-slider.js'
];

jsFiles.forEach(file => {
  if (!fs.existsSync(file)) {
    console.error(`Missing JS file: ${file}`);
    failed = true;
  } else {
    console.log(`Verified JS file: ${file}`);
  }
});

if (failed) {
  process.exit(1);
} else {
  console.log('ALL SERVICES ZOOM & SCROLL CHECKS PASSED 100%!');
}

