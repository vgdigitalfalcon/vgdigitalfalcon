const fs = require('fs');

console.log('Validating ecommerce-seo-service.html...');

const html = fs.readFileSync('ecommerce-seo-service.html', 'utf8');

// 1. Tag Balancing Checks
const tagsToCheck = ['div', 'section', 'header', 'footer', 'form', 'body', 'html'];
tagsToCheck.forEach(tag => {
  const openRegex = new RegExp(`<${tag}(\\s|>)`, 'gi');
  const closeRegex = new RegExp(`</${tag}>`, 'gi');
  const opens = (html.match(openRegex) || []).length;
  const closes = (html.match(closeRegex) || []).length;
  console.log(`Tag <${tag}>: open = ${opens}, close = ${closes}`);
  if (opens !== closes) {
    console.error(`Mismatch for tag <${tag}>!`);
    process.exit(1);
  }
});

// 2. Original Content Verification
const expectedStrings = [
  'Dominate Rankings',
  '10X Your Sales',
  'Rank #1 for high-value product keywords, drive qualified traffic',
  'Shopify, WooCommerce, and Amazon sellers worldwide',
  'Get a Free Ecommerce SEO Audit Worth $99!',
  'youtubeSeoForm',
  'process_form.php',
  'Your Name:',
  'Website URL:',
  'WhatsApp Number:',
  'Get My Custom SEO Plan',
  'Ecommerce Brands Served',
  '#1 Ranking Keywords',
  'Monthly Organic Clicks',
  'Increase in Sales',
  'data-target="250"',
  'data-target="3200"',
  'data-target="1200"',
  'data-target="3"',
  'Our Ecommerce SEO Service',
  'Grow Your Online Store with',
  'Best Ecommerce SEO Agency',
  'VG Digital Falcon is the best ecommerce SEO agency, delivering data-driven strategies',
  'Shopify, WooCommerce, Magento, or a custom CMS',
  'Ranking your products at the top of Google',
  'Increasing your visibility in competitive niches',
  'Driving higher conversions through SEO for ecommerce',
  'Why Does Your Online Store Need a Trusted',
  'Ecommerce SEO Partner?',
  'Search Engine Optimization is not optional, it’s essential',
  'Product page SEO optimization services.',
  'Technical SEO for Shopify and WooCommerce',
  'Ethical, white-hat link building.',
  'Try Our Expert Ecommerce SEO Services',
  'Ecommerce SEO Services Overview',
  'Local SEO Strategies',
  'Content Plans for Growth',
  'High-Quality Link Building',
  'Product Page SEO',
  'Technical SEO Checks',
  'Keyword for Ecommerce Stores',
  'Competitor SEO Insights',
  'Ecommerce SEO FAQ',
  'What is Ecommerce SEO, and why is it important for my store?',
  'How long does it take to see results from Ecommerce SEO?',
  'Will Ecommerce SEO work for a new online store?',
  'Can Ecommerce SEO actually increase sales?',
  'Is Ecommerce SEO a one-time job or ongoing process?',
  'contact@vgdigitalfalcon.com',
  '+91 8695776922',
  '© All Copyright 2026 by'
];

expectedStrings.forEach(str => {
  if (!html.includes(str)) {
    console.error(`Missing expected content: "${str}"`);
    process.exit(1);
  }
});
console.log(`Verified all ${expectedStrings.length} core content strings are 100% intact!`);

// 3. CSS check
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

console.log('All Ecommerce SEO validations passed 100%!');

