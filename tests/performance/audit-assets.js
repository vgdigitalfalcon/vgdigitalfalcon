/**
 * Performance & Asset Audit Script
 * Run with: node tests/performance/audit-assets.js
 */

const fs = require('fs');
const path = require('path');

console.log('=== FRONTEND PERFORMANCE ASSET AUDIT ===');

function getFiles(dir, matchExts) {
  let results = [];
  const list = fs.readdirSync(dir);
  list.forEach(file => {
    const fullPath = path.join(dir, file);
    const stat = fs.statSync(fullPath);
    if (stat && stat.isDirectory()) {
      if (!file.startsWith('.') && file !== 'node_modules' && file !== 'public_html (1)') {
        results = results.concat(getFiles(fullPath, matchExts));
      }
    } else {
      const ext = path.extname(file).toLowerCase();
      if (matchExts.includes(ext)) {
        results.push({ path: fullPath, size: stat.size });
      }
    }
  });
  return results;
}

// 1. Audit Images > 500KB
const images = getFiles('assets/img', ['.png', '.jpg', '.jpeg', '.webp']);
const oversizedImages = images.filter(img => img.size > 500 * 1024);
console.log(`Audited ${images.length} images in assets/img:`);
console.log(`  Oversized (>500KB): ${oversizedImages.length}`);
if (oversizedImages.length > 0) {
  oversizedImages.forEach(img => {
    console.log(`  - ${(img.size / 1024 / 1024).toFixed(2)} MB: ${img.path}`);
  });
}

// 2. Audit CSS Files
const cssFiles = getFiles('assets/css', ['.css']);
console.log(`\nAudited ${cssFiles.length} CSS files in assets/css:`);
cssFiles.forEach(f => {
  console.log(`  - ${(f.size / 1024).toFixed(1)} KB: ${f.path}`);
});

// 3. Audit JS Files
const jsFiles = getFiles('assets/js', ['.js']);
console.log(`\nAudited ${jsFiles.length} JS files in assets/js:`);
jsFiles.forEach(f => {
  console.log(`  - ${(f.size / 1024).toFixed(1)} KB: ${f.path}`);
});

console.log('\nAudit complete.');

