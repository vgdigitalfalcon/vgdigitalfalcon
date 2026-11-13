const fs = require('fs');
const path = require('path');

const rootDir = path.resolve(__dirname, '..');

console.log('--- Preparing Ecommerce SEO Dark Cyber Theme ---');

// 1. Append dedicated Ecommerce SEO Dark Cyber styles to assets/css/color.css
const cssPath = path.join(rootDir, 'assets', 'css', 'color.css');
let cssContent = fs.readFileSync(cssPath, 'utf8');

const ecomCssBlock = `
/* ==========================================================================
   ECOMMERCE SEO SERVICE PAGE — DARK CYBER THEME STYLING
   ========================================================================== */
.vg-ecom-stats-section {
  position: relative !important;
  background-color: #07070F !important;
  background: radial-gradient(circle at 50% 20%, rgba(0, 240, 255, 0.04) 0%, rgba(168, 85, 247, 0.03) 40%, #07070F 75%) !important;
  padding: 42px 0 !important;
  border-bottom: 1px solid rgba(168, 85, 247, 0.15) !important;
}

.vg-ecom-counter-card {
  background: rgba(14, 8, 30, 0.78);
  border: 1px solid rgba(168, 85, 247, 0.22);
  border-radius: 16px;
  padding: 24px 20px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);
  transition: all 0.28s cubic-bezier(0.16, 1, 0.3, 1);
  text-align: center;
  position: relative;
  overflow: hidden;
}

.vg-ecom-counter-card:hover {
  transform: translateY(-4px);
  border-color: #00F0FF;
  box-shadow: 0 16px 40px rgba(0, 0, 0, 0.6), 0 0 20px rgba(0, 240, 255, 0.25);
  background: rgba(22, 12, 48, 0.9);
}

.vg-ecom-counter-icon {
  width: 50px;
  height: 50px;
  margin: 0 auto 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.08);
}

.vg-ecom-counter-icon img {
  width: 28px;
  height: 28px;
  object-fit: contain;
}

.vg-ecom-counter-num {
  font-size: 36px;
  font-weight: 800;
  color: #ffffff !important;
  margin: 0 0 4px 0;
  line-height: 1.1;
  text-shadow: 0 0 14px rgba(0, 240, 255, 0.3);
}

.vg-ecom-counter-label {
  font-size: 13px;
  color: #94a3b8;
  margin: 0;
  font-weight: 600;
  letter-spacing: 0.2px;
}

/* Pillar Cards (Why SEO) */
.vg-ecom-pillar-card {
  background: rgba(14, 8, 30, 0.78);
  border: 1px solid rgba(168, 85, 247, 0.22);
  border-radius: 16px;
  padding: 24px 22px;
  height: 100%;
  display: flex;
  flex-direction: column;
  transition: all 0.28s cubic-bezier(0.16, 1, 0.3, 1);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);
  text-align: left;
}

.vg-ecom-pillar-card:hover {
  transform: translateY(-4px);
  border-color: #00F0FF;
  box-shadow: 0 16px 40px rgba(0, 0, 0, 0.6), 0 0 20px rgba(0, 240, 255, 0.25);
  background: rgba(22, 12, 48, 0.9);
}

.vg-pillar-icon {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.08);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  margin-bottom: 8px;
}

/* Service Slider Boxes */
.vg-ecom-service-card {
  background: rgba(14, 8, 32, 0.82) !important;
  border: 1px solid rgba(168, 85, 247, 0.25) !important;
  border-radius: 18px !important;
  padding: 28px 24px !important;
  height: 100%;
  display: flex;
  flex-direction: column;
  box-shadow: 0 12px 35px rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);
  transition: all 0.3s ease;
  text-align: left;
  min-height: 240px;
}

.vg-ecom-service-card:hover {
  transform: translateY(-4px);
  border-color: #00F0FF !important;
  box-shadow: 0 18px 45px rgba(0, 0, 0, 0.65), 0 0 22px rgba(0, 240, 255, 0.25) !important;
  background: rgba(22, 12, 48, 0.92) !important;
}

.vg-ecom-service-title {
  font-size: 19px;
  font-weight: 800;
  color: #ffffff;
  margin-bottom: 12px;
  line-height: 1.3;
}

.vg-ecom-service-desc {
  font-size: 13.8px;
  line-height: 1.65;
  color: #cbd5e1;
  margin: 0;
  text-align: left !important;
}

/* Ecommerce SEO FAQ Items */
.vg-ecom-faq-item {
  background: rgba(14, 8, 32, 0.75) !important;
  border: 1px solid rgba(168, 85, 247, 0.25) !important;
  border-radius: 14px !important;
  margin-bottom: 14px;
  overflow: hidden;
  transition: all 0.25s ease;
}

.vg-ecom-faq-item.is-open,
.vg-ecom-faq-item:hover {
  border-color: #00F0FF !important;
  box-shadow: 0 0 18px rgba(0, 240, 255, 0.18);
}

.vg-ecom-faq-btn {
  background: transparent !important;
  color: #ffffff !important;
  font-size: 16px !important;
  font-weight: 700 !important;
  padding: 16px 20px !important;
  border: none !important;
  box-shadow: none !important;
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  text-align: left;
}

.vg-ecom-faq-btn:focus {
  outline: none !important;
  box-shadow: none !important;
}

.vg-ecom-faq-btn:not(.collapsed) {
  color: #00F0FF !important;
  background: rgba(0, 240, 255, 0.05) !important;
}

.vg-ecom-faq-btn::after {
  filter: invert(1) hue-rotate(180deg);
  transition: transform 0.25s ease;
}

.vg-ecom-faq-body {
  padding: 12px 20px 18px !important;
  color: #cbd5e1 !important;
  font-size: 14px !important;
  line-height: 1.65 !important;
  text-align: left !important;
  border-top: 1px solid rgba(168, 85, 247, 0.15);
}

/* Image Cyber Frames */
.vg-cyber-image-frame {
  position: relative;
  background: rgba(14, 8, 30, 0.6);
  border: 1px solid rgba(168, 85, 247, 0.3);
  border-radius: 20px;
  padding: 12px;
  box-shadow: 0 15px 40px rgba(0, 0, 0, 0.6), 0 0 25px rgba(168, 85, 247, 0.15);
  display: inline-block;
  max-width: 100%;
}

.vg-cyber-image-frame img {
  border-radius: 14px;
  max-width: 100%;
  height: auto;
  display: block;
}
`;

if (!cssContent.includes('ECOMMERCE SEO SERVICE PAGE — DARK CYBER THEME STYLING')) {
  cssContent += '\n' + ecomCssBlock.trim() + '\n';
  fs.writeFileSync(cssPath, cssContent, 'utf8');
  console.log('Appended ecommerce SEO styles to assets/css/color.css');
}

// Mirror color.css
const mirrorCss1 = path.join(rootDir, 'public_html (1)', 'assets', 'css', 'color.css');
const mirrorCss2 = path.join(rootDir, 'public_html (1)', 'website', 'assets', 'css', 'color.css');
if (fs.existsSync(mirrorCss1)) fs.writeFileSync(mirrorCss1, cssContent, 'utf8');
if (fs.existsSync(mirrorCss2)) fs.writeFileSync(mirrorCss2, cssContent, 'utf8');
console.log('Synchronized color.css across mirrors.');

