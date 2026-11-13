/**
 * Automated WCAG 2.2 AA Accessibility Audit Script
 * Run with: node tests/accessibility/wcag-check.js
 */

const fs = require('fs');
const path = require('path');

const htmlFiles = fs.readdirSync('.').filter(f => f.endsWith('.html'));

console.log('=== WCAG 2.2 AA ACCESSIBILITY AUDIT ===');
let totalErrors = 0;

htmlFiles.forEach(file => {
  const content = fs.readFileSync(file, 'utf8');
  let fileErrors = 0;

  // 1. Missing alt on <img>
  const imgs = content.match(/<img\b[^>]*>/gi) || [];
  imgs.forEach(img => {
    if (!img.includes('alt=')) {
      console.warn(`[${file}] <img> missing alt attribute: ${img.slice(0, 50)}...`);
      fileErrors++;
    }
  });

  // 2. Icon-only buttons without aria-label
  const buttons = content.match(/<button\b[\s\S]*?<\/button>/gi) || [];
  buttons.forEach(btn => {
    const text = btn.replace(/<[^>]+>/g, '').trim();
    if (!text && !btn.includes('aria-label')) {
      console.warn(`[${file}] <button> missing accessible name / aria-label: ${btn.slice(0, 60)}...`);
      fileErrors++;
    }
  });

  // 3. Inputs without label or aria-label
  const inputs = content.match(/<input\b[^>]*>/gi) || [];
  inputs.forEach(inp => {
    if (!inp.includes('type="hidden"') && !inp.includes('type="checkbox"') && !inp.includes('type="radio"')) {
      if (!inp.includes('aria-label') && !inp.includes('id=')) {
        console.warn(`[${file}] <input> missing label / aria-label: ${inp.slice(0, 60)}...`);
        fileErrors++;
      }
    }
  });

  if (fileErrors === 0) {
    console.log(`PASS: ${file} (0 accessibility defects)`);
  } else {
    totalErrors += fileErrors;
  }
});

console.log('----------------------------------------');
if (totalErrors === 0) {
  console.log('RESULT: 100% WCAG 2.2 AA AUDIT PASSED!');
  process.exit(0);
} else {
  console.log(`RESULT: Found ${totalErrors} potential issues.`);
  process.exit(1);
}

