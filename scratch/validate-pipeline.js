const fs = require('fs');

console.log('Validating pipeline upgrade...');

// 1. Check CSS braces in color.css
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

// 2. Check HTML structure for section #how-it-works
const html = fs.readFileSync('web-development.html', 'utf8');
const pipelineMatch = html.match(/<section class="vg-seo-process-section vg-pipeline-section" id="how-it-works">[\s\S]*?<\/section>/);
if (!pipelineMatch) {
  console.error('Could not find #how-it-works pipeline section in HTML!');
  process.exit(1);
}
console.log('Found pipeline section in HTML, length:', pipelineMatch[0].length);

const sectionHtml = pipelineMatch[0];
const divOpens = (sectionHtml.match(/<div(\s|>)/g) || []).length;
const divCloses = (sectionHtml.match(/<\/div>/g) || []).length;
console.log(`Pipeline section divs: open = ${divOpens}, close = ${divCloses}`);
if (divOpens !== divCloses) {
  console.error('Div open/close mismatch in pipeline section!');
  process.exit(1);
}

// Verify sequence: Step 01 to Step 06
const expectedSteps = [
  'Strategy Session',
  'Selecting TechStack',
  'UI/UX Design',
  'Full-Stack Development',
  'QA Testing &amp; Launch',
  'Monthly Growth &amp; Reporting'
];

expectedSteps.forEach((step, idx) => {
  if (!sectionHtml.includes(step)) {
    console.error(`Missing expected step: ${step}`);
    process.exit(1);
  }
  console.log(`Verified Step 0${idx + 1}: ${step}`);
});

// Check deliverable pills
const deliverableCount = (sectionHtml.match(/class="vg-pipeline-deliverable"/g) || []).length;
console.log(`Deliverable pills count: ${deliverableCount} (expected 6)`);
if (deliverableCount !== 6) {
  console.error(`Expected 6 deliverable pills, found ${deliverableCount}`);
  process.exit(1);
}

// Check typo fix
if (sectionHtml.includes('Figma..')) {
  console.error('Found Figma.. typo!');
  process.exit(1);
}
console.log('Confirmed Figma.. typo is fixed.');

// Check redundant emoji images
if (sectionHtml.includes('step1.png') || sectionHtml.includes('assets/img/pricing/')) {
  console.error('Found old emoji image in pipeline section!');
  process.exit(1);
}
console.log('Confirmed no old emoji images remain in pipeline section.');

console.log('All pipeline validations passed 100%!');

