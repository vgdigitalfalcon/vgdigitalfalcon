/**
 * Automated Internal Link and Asset Integrity Checker
 * Audits Root Pages, Service Cluster, and Asset Paths
 * Run with: node tests/functional/link-checker.js
 */

const fs = require('fs');
const path = require('path');

console.log('=== FUNCTIONAL INTERNAL LINK & ASSET AUDIT ===');
let brokenLinks = 0;

function auditDirectory(dirPath, relativeBase) {
  if (!fs.existsSync(dirPath)) return;
  const files = fs.readdirSync(dirPath).filter(f => f.endsWith('.html'));

  files.forEach(file => {
    const fullPath = path.join(dirPath, file);
    const content = fs.readFileSync(fullPath, 'utf8');
    let fileIssues = 0;

    // Check hrefs
    const hrefs = content.match(/href=["']([^"']+)["']/gi) || [];
    hrefs.forEach(h => {
      const raw = h.replace(/^href=["']|["']$/gi, '').trim();
      if (!raw || raw.startsWith('#') || raw.startsWith('http') || raw.startsWith('mailto:') || raw.startsWith('tel:') || raw.startsWith('javascript:')) {
        return;
      }
      const cleanPath = raw.split('#')[0].split('?')[0];
      const resolved = path.resolve(relativeBase, cleanPath);
      if (cleanPath && !fs.existsSync(resolved)) {
        console.warn(`[${path.join(dirPath, file)}] Broken internal link: ${raw} -> resolved: ${resolved}`);
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
      const resolved = path.resolve(relativeBase, cleanPath);
      if (cleanPath && !fs.existsSync(resolved)) {
        console.warn(`[${path.join(dirPath, file)}] Missing asset src: ${raw} -> resolved: ${resolved}`);
        fileIssues++;
      }
    });

    if (fileIssues === 0) {
      console.log(`PASS: ${path.join(dirPath, file)} (all links & static assets verified)`);
    } else {
      brokenLinks += fileIssues;
    }
  });
}

// 1. Audit Root HTML Files
auditDirectory('.', '.');

// 2. Audit Pages Cluster (if present)
if (fs.existsSync('pages')) {
  auditDirectory('pages', 'pages');
}

// 3. Audit Services Cluster (if present)
if (fs.existsSync(path.join('pages', 'services'))) {
  auditDirectory(path.join('pages', 'services'), path.join('pages', 'services'));
}

console.log('----------------------------------------');
if (brokenLinks === 0) {
  console.log('RESULT: 100% INTERNAL LINKS & ASSETS VALIDATED!');
  process.exit(0);
} else {
  console.log(`RESULT: Found ${brokenLinks} broken references.`);
  process.exit(1);
}
