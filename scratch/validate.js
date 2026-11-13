const fs = require('fs');

console.log('Validating files...');

// 1. Check CSS braces
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

// 2. Check HTML structure for section #services
const html = fs.readFileSync('web-development.html', 'utf8');
const servicesMatch = html.match(/<section class="vg-split-services-section" id="services">[\s\S]*?<\/section>/);
if (!servicesMatch) {
  console.error('Could not find #services section in HTML!');
  process.exit(1);
}
console.log('Found #services section in HTML, length:', servicesMatch[0].length);

// Check div open/close inside #services
const sectionHtml = servicesMatch[0];
const divOpens = (sectionHtml.match(/<div(\s|>)/g) || []).length;
const divCloses = (sectionHtml.match(/<\/div>/g) || []).length;
console.log(`Section #services divs: open = ${divOpens}, close = ${divCloses}`);
if (divOpens !== divCloses) {
  console.error('Div open/close mismatch in #services!');
  process.exit(1);
}

// Check row classes in #services
if (!sectionHtml.includes('vg-split-showcase-row')) {
  console.error('Missing vg-split-showcase-row!');
  process.exit(1);
}
if (!sectionHtml.includes('col-lg-6 col-xl-6')) {
  console.error('Missing col-lg-6 col-xl-6!');
  process.exit(1);
}

console.log('All validations passed successfully!');

