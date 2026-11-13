const fs = require('fs');

const jsFiles = [
  'assets/js/ecom-vortex-canvas.js',
  'public_html (1)/assets/js/ecom-vortex-canvas.js',
  'public_html (1)/website/assets/js/ecom-vortex-canvas.js'
];

let failed = false;

jsFiles.forEach(file => {
  if (!fs.existsSync(file)) {
    console.error(`Missing script file: ${file}`);
    failed = true;
    return;
  }
  const code = fs.readFileSync(file, 'utf8');

  // Verify removed elements
  if (code.includes('buildProductCubes') || code.includes('drawCube')) {
    console.error(`Wireframe cubes still found in ${file}`);
    failed = true;
  }
  if (code.includes('+10X')) {
    console.error(`Floating '+10X' text still found in ${file}`);
    failed = true;
  }
  if (code.includes('drawSpiralTracks')) {
    console.error(`Spiral tracks still found in ${file}`);
    failed = true;
  }

  // Verify new executive aurora & perspective grid
  if (!code.includes('drawPerspectiveGrid') || !code.includes('drawAuroraMeshWave') || !code.includes('renderFlowingRibbon')) {
    console.error(`Executive Aurora / Grid functions missing in ${file}`);
    failed = true;
  }

  console.log(`Verified JS: ${file} is clean and executive.`);
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

const htmlFiles = [
  'ecommerce-seo-service.html',
  'public_html (1)/ecommerce-seo-service.html',
  'public_html (1)/website/ecommerce-seo-service.html'
];

htmlFiles.forEach(file => {
  const html = fs.readFileSync(file, 'utf8');
  if (!html.includes('vg-ecom-vortex-canvas')) {
    console.error(`Canvas ID missing in ${file}`);
    failed = true;
  }
  if (!html.includes('assets/js/ecom-vortex-canvas.js')) {
    console.error(`Script reference missing in ${file}`);
    failed = true;
  }
  console.log(`Verified HTML: ${file}`);
});

if (failed) {
  process.exit(1);
} else {
  console.log('ALL EXECUTIVE HERO CHECKS PASSED 100%!');
}

