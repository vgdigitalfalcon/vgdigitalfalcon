const fs = require('fs');

const sliderCss = `

/* ==========================================================================
   SERVICES OVERVIEW: AUTOMATIC ZOOM & SMOOTH AUTO-SCROLL
   ========================================================================== */
.vg-seo-services-section {
  position: relative;
  overflow: hidden !important;
}

.vg-ecom-services-slider {
  padding: 35px 0 50px !important;
  overflow: visible !important;
}

.vg-ecom-services-slider .swiper-wrapper {
  align-items: center;
}

/* Inactive / Side Slides (Zoomed Out State) */
.vg-ecom-services-slider .swiper-slide {
  transform: scale(0.92);
  opacity: 0.62;
  filter: blur(0.4px);
  transition: transform 0.65s cubic-bezier(0.16, 1, 0.3, 1),
              opacity 0.65s ease,
              filter 0.65s ease !important;
  height: auto;
  display: flex;
}

/* Centered / Active Slide (AUTOMATIC ZOOM STATE) */
.vg-ecom-services-slider .swiper-slide.swiper-slide-active {
  transform: scale(1.08) translateY(-8px) !important;
  opacity: 1 !important;
  filter: none !important;
  z-index: 10 !important;
}

/* Zoomed Card Aesthetic Upgrade */
.vg-ecom-services-slider .swiper-slide.swiper-slide-active .vg-ecom-service-card {
  border-color: #00F0FF !important;
  background: rgba(22, 12, 48, 0.95) !important;
  box-shadow: 0 22px 55px rgba(0, 0, 0, 0.75),
              0 0 30px rgba(0, 240, 255, 0.32),
              0 0 65px rgba(168, 85, 247, 0.2) !important;
  position: relative;
}

/* Subtle Aurora Halo on Active Zoomed Card */
.vg-ecom-services-slider .swiper-slide.swiper-slide-active .vg-ecom-service-card::after {
  content: '';
  position: absolute;
  inset: -1.5px;
  border-radius: 19px;
  padding: 1.5px;
  background: linear-gradient(135deg, rgba(0, 240, 255, 0.7), rgba(168, 85, 247, 0.4), rgba(0, 240, 255, 0.2));
  -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  -webkit-mask-composite: xor;
  mask-composite: exclude;
  pointer-events: none;
  animation: vgActiveCardHalo 3.5s ease-in-out infinite;
}

@keyframes vgActiveCardHalo {
  0%, 100% { opacity: 0.65; }
  50% { opacity: 1; }
}

/* Active Zoomed Title & Icon Highlighting */
.vg-ecom-services-slider .swiper-slide.swiper-slide-active .vg-ecom-service-title {
  color: #ffffff !important;
  text-shadow: 0 0 14px rgba(0, 240, 255, 0.45);
}

.vg-ecom-services-slider .swiper-slide.swiper-slide-active .vg-ecom-service-card i {
  transform: scale(1.2);
  filter: drop-shadow(0 0 10px currentColor);
  transition: transform 0.4s ease, filter 0.4s ease;
}

/* Interactive Hover Response */
.vg-ecom-services-slider .swiper-slide:hover {
  opacity: 0.95;
  filter: none;
}

/* Enhanced Pagination Dots with Auto-Scroll Progress */
.vg-services-dot {
  margin-top: 25px;
  text-align: center;
}

.vg-services-dot .swiper-pagination-bullet {
  width: 22px;
  height: 6px;
  border-radius: 20px;
  background: rgba(255, 255, 255, 0.22);
  display: inline-block;
  margin: 0 5px;
  cursor: pointer;
  border: 1px solid rgba(255, 255, 255, 0.08);
  transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
  opacity: 1;
}

.vg-services-dot .swiper-pagination-bullet:hover {
  background: rgba(0, 240, 255, 0.45);
}

.vg-services-dot .swiper-pagination-bullet.swiper-pagination-bullet-active {
  width: 50px;
  background: linear-gradient(90deg, #00F0FF, #A855F7);
  box-shadow: 0 0 14px rgba(0, 240, 255, 0.6);
  border-color: #00F0FF;
}
`;

// 1. Append CSS to color.css and mirrors
const cssPaths = [
  'assets/css/color.css',
  'public_html (1)/assets/css/color.css',
  'public_html (1)/website/assets/css/color.css'
];

cssPaths.forEach(cssPath => {
  if (fs.existsSync(cssPath)) {
    let content = fs.readFileSync(cssPath, 'utf8');
    if (!content.includes('vg-ecom-services-slider')) {
      content += sliderCss;
      fs.writeFileSync(cssPath, content, 'utf8');
      console.log(`Appended slider CSS to ${cssPath}`);
    } else {
      console.log(`Slider CSS already present in ${cssPath}`);
    }
  }
});

// 2. Update HTML files
const htmlPaths = [
  'ecommerce-seo-service.html',
  'public_html (1)/ecommerce-seo-service.html',
  'public_html (1)/website/ecommerce-seo-service.html'
];

htmlPaths.forEach(htmlPath => {
  if (fs.existsSync(htmlPath)) {
    let content = fs.readFileSync(htmlPath, 'utf8');

    // Replace <div class="swiper testimonial-slider"> with <div class="swiper vg-ecom-services-slider">
    content = content.replace(
      '<div class="swiper testimonial-slider">',
      '<div class="swiper vg-ecom-services-slider">'
    );

    // Replace pagination container
    content = content.replace(
      '<div class="swiper-dot mt-4 text-center">',
      '<div class="vg-services-dot mt-4 text-center">'
    );

    // Add script tag if missing
    if (!content.includes('assets/js/ecom-services-slider.js')) {
      content = content.replace(
        '<script src="assets/js/main.js"></script>',
        '<script src="assets/js/main.js"></script>\n        <script src="assets/js/ecom-services-slider.js"></script>'
      );
    }

    fs.writeFileSync(htmlPath, content, 'utf8');
    console.log(`Updated HTML in ${htmlPath}`);
  }
});

