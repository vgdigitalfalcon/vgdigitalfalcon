const fs = require('fs');

// 1. Update HTML files
const htmlFiles = [
  'ecommerce-seo-service.html',
  'public_html (1)/ecommerce-seo-service.html',
  'public_html (1)/website/ecommerce-seo-service.html'
];

htmlFiles.forEach(file => {
  if (fs.existsSync(file)) {
    let content = fs.readFileSync(file, 'utf8');
    const oldCommentRegex = /<!-- Interactive 3D Search & Backlink Node Network Canvas -->\s*<!-- Interactive 3D Holographic Product-to-Conversion Growth Vortex Canvas -->/g;
    if (oldCommentRegex.test(content)) {
      content = content.replace(oldCommentRegex, '<!-- Executive Fortune-500 Aurora Mesh Wave & Architectural Perspective Grid Canvas -->');
      fs.writeFileSync(file, content, 'utf8');
      console.log('Updated canvas comment in ' + file);
    }
  }
});

// 2. Update CSS in color.css files
const cssFiles = [
  'assets/css/color.css',
  'public_html (1)/assets/css/color.css',
  'public_html (1)/website/assets/css/color.css'
];

cssFiles.forEach(file => {
  if (fs.existsSync(file)) {
    let content = fs.readFileSync(file, 'utf8');
    const oldBg = 'background: radial-gradient(circle at 50% 15%, rgba(124, 58, 237, 0.28) 0%, rgba(10, 3, 25, 0.98) 65%, #070212 100%), #070212 !important;';
    const newBg = 'background: radial-gradient(circle at 50% 15%, rgba(124, 58, 237, 0.16) 0%, rgba(9, 6, 18, 0.98) 65%, #07070F 100%), #07070F !important;';
    if (content.includes(oldBg)) {
      content = content.replace(oldBg, newBg);
      fs.writeFileSync(file, content, 'utf8');
      console.log('Updated hero background in ' + file);
    }
  }
});

