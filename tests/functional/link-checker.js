/**
 * Automated Internal Link and Asset Integrity Checker
 * Run with: node tests/functional/link-checker.js
 */

const fs = require('fs');
const path = require('path');

const htmlFiles = fs.readdirSync('.').filter(f => f.endsWith('.html'));

console.log('=== FUNCTIONAL INTERNAL LINK & ASSET AUDIT ===');
let brokenLinks = 0;

htmlFiles.forEach(file => {
  const content = fs.readFileSync(file, 'utf8');
  let fileIssues = 0;

  // Check hrefs
  const hrefs = content.match(/href=["']([^"']+)["']/gi) || [];
  hrefs.forEach(h => {
    const raw = h.replace(/^href=["']|["']$/gi, '').trim();
    if (!raw || raw.startsWith('#') || raw.startsWith('http') || raw.startsWith('mailto:') || raw.startsWith('tel:') || raw.startsWith('javascript:')) {
      return;
    }
    const cleanPath = raw.split('#')[0].split('?')[0];
    if (cleanPath && !fs.existsSync(cleanPath)) {
      console.warn(`[${file}] Broken internal link: ${raw}`);
      fileIssues++;
    }
  });

  // Check src assets
  const srcs = content.match(/src=["']([^"']+)["']/gi) || [];
  srcs.forEach(s => {
    const raw = s.replace(/^src=["']|["']$/gi, '').trim();
    if (!raw || raw.startsWith('data:') || raw.startsWith('http') || raw.startsWith('//')) {
      return;
    }
    const cleanPath = raw.split('?')[0];
    if (cleanPath && !fs.existsSync(cleanPath)) {
      console.warn(`[${file}] Missing asset src: ${raw}`);
      fileIssues++;
    }
  });

  if (fileIssues === 0) {
    console.log(`PASS: ${file} (all links & static assets verified)`);
  } else {
    brokenLinks += fileIssues;
  }
});

console.log('----------------------------------------');
if (brokenLinks === 0) {
  console.log('RESULT: 100% INTERNAL LINKS & ASSETS VALIDATED!');
  process.exit(0);
} else {
  console.log(`RESULT: Found ${brokenLinks} broken references.`);
  process.exit(1);
}

