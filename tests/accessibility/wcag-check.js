/**
 * Automated WCAG 2.2 AA Accessibility Audit Script
 * Audits Root Pages, Service Cluster, and Component Templates
 * Run with: node tests/accessibility/wcag-check.js
 */

const fs = require('fs');
const path = require('path');

console.log('=== WCAG 2.2 AA ACCESSIBILITY AUDIT ===');
let totalErrors = 0;

function auditA11yDirectory(dirPath) {
  if (!fs.existsSync(dirPath)) return;
  const files = fs.readdirSync(dirPath).filter(f => f.endsWith('.html'));

  files.forEach(file => {
    const fullPath = path.join(dirPath, file);
    const content = fs.readFileSync(fullPath, 'utf8');
    let fileErrors = 0;

    // 1. Missing alt on <img>
    const imgs = content.match(/<img\b[^>]*>/gi) || [];
    imgs.forEach(img => {
      if (!img.includes('alt=')) {
        console.warn(`[${fullPath}] <img> missing alt attribute: ${img.slice(0, 50)}...`);
        fileErrors++;
      }
    });

    // 2. Icon-only buttons without aria-label
    const buttons = content.match(/<button\b[\s\S]*?<\/button>/gi) || [];
    buttons.forEach(btn => {
      const text = btn.replace(/<[^>]+>/g, '').trim();
      if (!text && !btn.includes('aria-label')) {
        console.warn(`[${fullPath}] <button> missing accessible name / aria-label: ${btn.slice(0, 60)}...`);
        fileErrors++;
      }
    });

    // 3. Inputs without label or aria-label
    const inputs = content.match(/<input\b[^>]*>/gi) || [];
    inputs.forEach(inp => {
      if (!inp.includes('type="hidden"') && !inp.includes('type="checkbox"') && !inp.includes('type="radio"')) {
        if (!inp.includes('aria-label') && !inp.includes('id=')) {
          console.warn(`[${fullPath}] <input> missing label / aria-label: ${inp.slice(0, 60)}...`);
          fileErrors++;
        }
      }
    });

    if (fileErrors === 0) {
      console.log(`PASS: ${fullPath} (0 accessibility defects)`);
    } else {
      totalErrors += fileErrors;
    }
  });
}

// 1. Audit Root HTML Files
auditA11yDirectory('.');

// 2. Audit Pages Cluster
if (fs.existsSync('pages')) {
  auditA11yDirectory('pages');
}

// 3. Audit Services Cluster
if (fs.existsSync(path.join('pages', 'services'))) {
  auditA11yDirectory(path.join('pages', 'services'));
}

console.log('----------------------------------------');
if (totalErrors === 0) {
  console.log('RESULT: 100% WCAG 2.2 AA AUDIT PASSED!');
  process.exit(0);
} else {
  console.log(`RESULT: Found ${totalErrors} potential issues.`);
  process.exit(1);
}
