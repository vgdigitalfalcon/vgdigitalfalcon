/**
 * ============================================================================
 * VG DIGITAL FALCON — COMPACT SPLIT-SCREEN SERVICE SHOWCASE
 * File: assets/js/webdev-services-showcase.js
 * 
 * Features:
 * 1. Default State: No service selected on page load.
 *    Displays an animated futuristic Waving AI Robot character ("Hi!")
 *    with a friendly prompt: "Select a service on the left to explore how we engineer it".
 * 2. Click/Touch-to-Reveal: Clicking or tapping any of the 8 services smoothly
 *    transitions out the robot and displays full technical blueprints, 3 deliverables,
 *    and direct CTA button.
 * 3. Compact 2-Column x 4-Row Grid: Fits all 8 services cleanly in one view on desktop.
 * ============================================================================
 */

(function () {
  'use strict';

  const SERVICES_DATA = {
    'custom-web': {
      id: 'custom-web',
      title: 'Custom Website Development',
      tag: 'Next.js / SSR',
      tagClass: 'tag-cyan',
      icon: 'fa-solid fa-globe',
      accentColor: '#00F0FF',
      accentGradient: 'linear-gradient(135deg, #00F0FF, #38BDF8)',
      overview: 'We engineer bespoke, high-performance websites built on modern frameworks to turn digital visitors into revenue. Every build is architected from scratch with clean code, ultra-responsive layouts, and built-in SEO to maximize conversions.',
      metrics: [
        {
          icon: 'fa-solid fa-gauge-high',
          val: '99+ Lighthouse Score',
          desc: 'Sub-second load times & Green Core Web Vitals on all devices'
        },
        {
          icon: 'fa-solid fa-code-merge',
          val: 'Custom API Integration',
          desc: 'Seamless bridging with Headless CMS, CRMs, and analytics pipelines'
        },
        {
          icon: 'fa-solid fa-mobile-screen',
          val: 'Mobile-First UI/UX',
          desc: 'Tailored conversion funnels designed for maximum mobile retention'
        }
      ],
      whatsappMsg: 'Hii VG Digital Falcon, I would like to get a quote and strategy plan for Custom Website Development. Please share the details.'
    },
    'web-app': {
      id: 'web-app',
      title: 'Web App Development',
      tag: 'React + Node.js',
      tagClass: 'tag-violet',
      icon: 'fa-solid fa-window-restore',
      accentColor: '#A855F7',
      accentGradient: 'linear-gradient(135deg, #A855F7, #C084FC)',
      overview: 'We craft dynamic, full-stack web applications using React and Node.js that automate operations and delight users. Designed for cloud scalability, our custom web apps integrate flawlessly with your backend infrastructure and data pipelines.',
      metrics: [
        {
          icon: 'fa-solid fa-cloud-arrow-up',
          val: '99.9% Cloud Uptime',
          desc: 'Scalable serverless & containerized deployments on AWS / Cloudflare'
        },
        {
          icon: 'fa-solid fa-bolt-lightning',
          val: 'Real-Time Data Sync',
          desc: 'Reactive state management, WebSockets & GraphQL data architecture'
        },
        {
          icon: 'fa-solid fa-user-shield',
          val: 'Enterprise RBAC Auth',
          desc: 'Bank-grade role-based access control & multi-tenant security'
        }
      ],
      whatsappMsg: 'Hii VG Digital Falcon, I would like to discuss Web App Development using React and Node.js. Please share the details.'
    },
    'wordpress': {
      id: 'wordpress',
      title: 'WordPress Development',
      tag: 'Headless / Speed',
      tagClass: 'tag-sky',
      icon: 'fa-brands fa-wordpress',
      accentColor: '#38BDF8',
      accentGradient: 'linear-gradient(135deg, #38BDF8, #60A5FA)',
      overview: 'We deliver enterprise-grade, headless and custom-tailored WordPress solutions engineered for exceptional load speed and top-tier SEO rankings. Gain total editorial independence with customized Gutenberg/ACF workflows without sacrificing front-end security or speed.',
      metrics: [
        {
          icon: 'fa-solid fa-stopwatch',
          val: '< 0.8s Server TTFB',
          desc: 'Advanced Redis object caching & edge-CDN asset optimization'
        },
        {
          icon: 'fa-solid fa-cubes',
          val: 'Zero-Bloat Theme',
          desc: 'Tailored ACF dynamic blocks with zero heavy page builder overhead'
        },
        {
          icon: 'fa-solid fa-shield-virus',
          val: 'Enterprise Hardening',
          desc: 'Cloudflare WAF integration & automated vulnerability shields'
        }
      ],
      whatsappMsg: 'Hii VG Digital Falcon, I would like to discuss WordPress Development. Please share the details.'
    },
    'cms': {
      id: 'cms',
      title: 'CMS System Development',
      tag: 'Custom Admin',
      tagClass: 'tag-amber',
      icon: 'fa-solid fa-sliders',
      accentColor: '#F59E0B',
      accentGradient: 'linear-gradient(135deg, #F59E0B, #FBBF24)',
      overview: 'Gain effortless content management and centralized digital governance with custom CMS architectures built around your exact operational workflows. We develop secure, role-based admin portals that allow your marketing team to launch campaigns and update content in seconds.',
      metrics: [
        {
          icon: 'fa-solid fa-wand-magic-sparkles',
          val: 'Instant Publishing',
          desc: 'Intuitive drag-and-drop editorial governance with live previews'
        },
        {
          icon: 'fa-solid fa-language',
          val: 'Multi-Language / i18n',
          desc: 'Localized multilingual content delivery across global territories'
        },
        {
          icon: 'fa-solid fa-magnifying-glass-chart',
          val: 'Built-In Schema & SEO',
          desc: 'Automated structured data, canonical tags & OpenGraph metadata'
        }
      ],
      whatsappMsg: 'Hii VG Digital Falcon, I would like to discuss CMS System Development. Please share the details.'
    },
    'ecommerce': {
      id: 'ecommerce',
      title: 'E-Commerce Website Development',
      tag: 'High Conversion',
      tagClass: 'tag-rose',
      icon: 'fa-solid fa-cart-shopping',
      accentColor: '#F43F5E',
      accentGradient: 'linear-gradient(135deg, #F43F5E, #FB7185)',
      overview: 'We build conversion-obsessed online storefronts across Shopify, Magento, and WooCommerce that handle high-volume sales with zero friction. From frictionless 1-click checkouts to custom product configurators, our e-commerce platforms turn browsing into immediate buying.',
      metrics: [
        {
          icon: 'fa-solid fa-chart-line',
          val: '+35% Checkout Lift',
          desc: 'Streamlined frictionless checkout flow with Apple Pay & Google Pay'
        },
        {
          icon: 'fa-solid fa-boxes-packing',
          val: 'Omnichannel Sync',
          desc: 'Automated real-time inventory, ERP, and multi-warehouse sync'
        },
        {
          icon: 'fa-solid fa-shield-halved',
          val: 'PCI-DSS Compliance',
          desc: 'End-to-end tokenized payments with zero fraud exposure'
        }
      ],
      whatsappMsg: 'Hii VG Digital Falcon, I would like to discuss E-commerce Website Development. Please share the details.'
    },
    'erp': {
      id: 'erp',
      title: 'Custom ERP Development',
      tag: 'Automated Ops',
      tagClass: 'tag-indigo',
      icon: 'fa-solid fa-network-wired',
      accentColor: '#818CF8',
      accentGradient: 'linear-gradient(135deg, #818CF8, #A78BFA)',
      overview: 'Transform your enterprise operations with tailored ERP systems that unify inventory, logistics, HR, and financial reporting into one real-time dashboard. Automate repetitive workflows, eliminate data silos, and scale operations globally with mission-critical precision.',
      metrics: [
        {
          icon: 'fa-solid fa-robot',
          val: 'Automated Operations',
          desc: 'End-to-end automation across procurement, inventory & payroll'
        },
        {
          icon: 'fa-solid fa-chart-pie',
          val: 'Real-Time KPI Suite',
          desc: 'Executive analytics dashboards with automated scheduled exports'
        },
        {
          icon: 'fa-solid fa-arrows-split-up-and-left',
          val: 'REST & GraphQL Bridge',
          desc: 'Seamless bridging with 3rd-party SaaS, banking & legacy systems'
        }
      ],
      whatsappMsg: 'Hii VG Digital Falcon, I would like to discuss Custom ERP Development. Please share the details.'
    },
    'shopify': {
      id: 'shopify',
      title: 'Shopify Store Development',
      tag: 'Liquid / 95+ Vitals',
      tagClass: 'tag-emerald',
      icon: 'fa-brands fa-shopify',
      accentColor: '#10B981',
      accentGradient: 'linear-gradient(135deg, #10B981, #34D399)',
      overview: 'We design and build bespoke, high-converting Shopify stores with customized Liquid themes, custom app integrations, and sub-second Core Web Vitals. Engineered to elevate your brand presence and maximize Average Order Value (AOV) across global markets.',
      metrics: [
        {
          icon: 'fa-solid fa-gauge',
          val: '95+ Core Web Vitals',
          desc: 'Custom-coded Liquid architecture with zero third-party app bloat'
        },
        {
          icon: 'fa-solid fa-bag-shopping',
          val: 'High AOV Features',
          desc: 'Slide-out dynamic carts, smart product bundles & cross-sells'
        },
        {
          icon: 'fa-solid fa-earth-americas',
          val: 'Shopify Markets Ready',
          desc: 'Multi-currency, localized tax rates & multi-language expansion'
        }
      ],
      whatsappMsg: 'Hii VG Digital Falcon, I would like to discuss Shopify Store Development. Please share the details.'
    },
    'maintenance': {
      id: 'maintenance',
      title: 'Web Maintenance & Updates',
      tag: '24/7 Security',
      tagClass: 'tag-purple',
      icon: 'fa-solid fa-screwdriver-wrench',
      accentColor: '#C084FC',
      accentGradient: 'linear-gradient(135deg, #C084FC, #E879F9)',
      overview: 'Keep your mission-critical web platforms airtight, ultra-fast, and continuously optimized with 24/7 engineering support. Our proactive maintenance plans include weekly security patches, continuous speed tuning, automated daily backups, and uptime monitoring.',
      metrics: [
        {
          icon: 'fa-solid fa-shield-cat',
          val: '24/7 Security Shield',
          desc: 'Continuous automated malware scanning & real-time threat mitigation'
        },
        {
          icon: 'fa-solid fa-heart-pulse',
          val: 'Weekly Health Audits',
          desc: 'Scheduled database optimization, core updates & link checking'
        },
        {
          icon: 'fa-solid fa-headset',
          val: 'Under 1-Hour SLA',
          desc: 'Dedicated rapid-response senior engineers on standby'
        }
      ],
      whatsappMsg: 'Hii VG Digital Falcon, I would like to discuss Website Maintenance & Updates. Please share the details.'
    }
  };

  // Helper to render Default State (High-End 3D/Lottie-Style Waving AI Robot Mascot inspired by dtapex.com)
  function renderDefaultRobot(previewEl, onReset) {
    if (!previewEl) return;

    previewEl.style.opacity = '0';
    previewEl.style.transform = 'translateY(6px)';

    setTimeout(() => {
      previewEl.innerHTML = `
        <div class="vg-robot-showcase-view">
          <div class="vg-robot-flex-wrap">
            <!-- 3D Futuristic AI Robot Mascot (Reference: dtapex.com) -->
            <div class="vg-robot-character-col">
              <div class="dtapex-robot-stage">
                <!-- Ambient Cyber Glow Aura Behind Mascot -->
                <div class="dtapex-robot-halo"></div>

                <!-- Floating Mascot Speech Bubble ("Hi!") -->
                <div class="dtapex-speech-bubble">
                  <span class="dtapex-wave-hand">👋</span> <strong>"Hi!"</strong>
                </div>

                <!-- Sleek 3D Articulated Robot Character SVG -->
                <svg class="dtapex-robot-svg" viewBox="0 0 240 280" width="180" height="210" xmlns="http://www.w3.org/2000/svg" aria-label="Futuristic 3D AI Robot Mascot">
                  <defs>
                    <!-- Cyan Glow Filter -->
                    <filter id="dtapex-cyan-glow" x="-30%" y="-30%" width="160%" height="160%">
                      <feGaussianBlur stdDeviation="4.5" result="blur" />
                      <feComposite in="SourceGraphic" in2="blur" operator="over" />
                    </filter>

                    <!-- Violet Glow Filter -->
                    <filter id="dtapex-violet-glow" x="-30%" y="-30%" width="160%" height="160%">
                      <feGaussianBlur stdDeviation="5.5" result="blur" />
                      <feComposite in="SourceGraphic" in2="blur" operator="over" />
                    </filter>

                    <!-- Volumetric Pearlescent Metallic Chassis Gradient (3D Sphere Lighting) -->
                    <radialGradient id="dtapex-pearl-white" cx="35%" cy="30%" r="70%">
                      <stop offset="0%" stop-color="#FFFFFF" />
                      <stop offset="35%" stop-color="#F1F5F9" />
                      <stop offset="70%" stop-color="#CBD5E1" />
                      <stop offset="95%" stop-color="#94A3B8" />
                      <stop offset="100%" stop-color="#64748B" />
                    </radialGradient>

                    <!-- Cyber Titanium Torso Plates -->
                    <linearGradient id="dtapex-cyber-armor" x1="0%" y1="0%" x2="100%" y2="100%">
                      <stop offset="0%" stop-color="#2D1B69" />
                      <stop offset="50%" stop-color="#160D38" />
                      <stop offset="100%" stop-color="#070314" />
                    </linearGradient>

                    <!-- Curved Glass Visor 3D Specular Sheen -->
                    <radialGradient id="dtapex-visor-glass" cx="40%" cy="30%" r="75%">
                      <stop offset="0%" stop-color="#1E1242" />
                      <stop offset="55%" stop-color="#0E0722" />
                      <stop offset="100%" stop-color="#04010A" />
                    </radialGradient>

                    <linearGradient id="dtapex-glass-sheen" x1="15%" y1="0%" x2="85%" y2="100%">
                      <stop offset="0%" stop-color="rgba(255, 255, 255, 0.65)" />
                      <stop offset="30%" stop-color="rgba(0, 240, 255, 0.25)" />
                      <stop offset="70%" stop-color="transparent" />
                    </linearGradient>

                    <!-- Glowing Gyroscopic Reactor Core -->
                    <radialGradient id="dtapex-reactor-glow" cx="50%" cy="50%" r="50%">
                      <stop offset="0%" stop-color="#FFFFFF" />
                      <stop offset="35%" stop-color="#00F0FF" />
                      <stop offset="75%" stop-color="#A855F7" />
                      <stop offset="100%" stop-color="transparent" />
                    </radialGradient>

                    <!-- Magnetic Ion Thruster Flame -->
                    <linearGradient id="dtapex-ion-flame-grad" x1="0%" y1="0%" x2="0%" y2="100%">
                      <stop offset="0%" stop-color="#00F0FF" stop-opacity="0.95" />
                      <stop offset="60%" stop-color="#A855F7" stop-opacity="0.6" />
                      <stop offset="100%" stop-color="#00F0FF" stop-opacity="0" />
                    </linearGradient>
                  </defs>

                  <!-- Ground Soft Kinetic Shadow -->
                  <ellipse class="dtapex-robot-shadow" cx="120" cy="265" rx="52" ry="7.5" fill="rgba(0, 240, 255, 0.24)" filter="url(#dtapex-cyan-glow)" />

                  <!-- Continuous Hover Floating Group (60fps) -->
                  <g class="dtapex-robot-floater">
                    
                    <!-- Ion Anti-Gravity Thruster Glow & Stream -->
                    <g class="dtapex-thruster-group">
                      <polygon points="106,220 134,220 125,248 115,248" fill="url(#dtapex-ion-flame-grad)" class="dtapex-ion-flame" />
                      <ellipse cx="120" cy="221" rx="16" ry="4" fill="#00F0FF" filter="url(#dtapex-cyan-glow)" />
                      <ellipse cx="120" cy="220" rx="9" ry="2.2" fill="#FFFFFF" />
                    </g>

                    <!-- Cybernetic Torso & Lower Chassis Armor -->
                    <g id="dtapex-torso-group">
                      <!-- Lower Body Chassis Cup -->
                      <path d="M 92 188 C 92 216, 148 216, 148 188 Z" fill="url(#dtapex-cyber-armor)" stroke="#38BDF8" stroke-width="1.4" />
                      
                      <!-- Main Upper Torso (Volumetric Rounded Shield) -->
                      <rect x="76" y="126" width="88" height="74" rx="28" fill="url(#dtapex-pearl-white)" stroke="rgba(255,255,255,0.7)" stroke-width="1.8" />
                      
                      <!-- Side Carbon Accents -->
                      <path d="M 80 148 Q 72 165 80 182" fill="none" stroke="#A855F7" stroke-width="3" stroke-linecap="round" />
                      <path d="M 160 148 Q 168 165 160 182" fill="none" stroke="#A855F7" stroke-width="3" stroke-linecap="round" />

                      <!-- Chest Gyroscopic Reactor Enclosure -->
                      <rect x="94" y="142" width="52" height="46" rx="18" fill="url(#dtapex-cyber-armor)" stroke="#00F0FF" stroke-width="1.6" />
                      
                      <!-- Rotating Gyroscopic Outer Ring -->
                      <circle cx="120" cy="165" r="17" fill="none" stroke="#00F0FF" stroke-width="1.6" stroke-dasharray="8 4" class="dtapex-reactor-ring" />
                      <!-- Violet Inner Ring -->
                      <circle cx="120" cy="165" r="12" fill="none" stroke="#A855F7" stroke-width="1.4" />
                      <!-- Pulsing Energetic Core -->
                      <circle cx="120" cy="165" r="8" fill="url(#dtapex-reactor-glow)" class="dtapex-reactor-pulse" />
                    </g>

                    <!-- Left Arm (Articulated at Side, Resting in Balance) -->
                    <g id="dtapex-left-arm">
                      <!-- Shoulder Joint -->
                      <circle cx="68" cy="142" r="11" fill="url(#dtapex-pearl-white)" stroke="#38BDF8" stroke-width="1.2" />
                      <circle cx="68" cy="142" r="4.5" fill="#160D38" />
                      <!-- Bicep & Forearm -->
                      <path d="M 66 150 Q 52 174 62 196" fill="none" stroke="url(#dtapex-pearl-white)" stroke-width="8" stroke-linecap="round" />
                      <path d="M 66 150 Q 52 174 62 196" fill="none" stroke="#00F0FF" stroke-width="1.5" stroke-linecap="round" opacity="0.6" />
                      <!-- Left Hand Sphere with Cyan Tip -->
                      <circle cx="63" cy="199" r="6.5" fill="url(#dtapex-pearl-white)" />
                      <circle cx="63" cy="201" r="2.8" fill="#00F0FF" />
                    </g>

                    <!-- 3D Spherical Cyber Helmet / Head Unit -->
                    <g id="dtapex-head-group">
                      <!-- Crown Antenna Beacon Mast -->
                      <line x1="120" y1="52" x2="120" y2="28" stroke="#00F0FF" stroke-width="3" stroke-linecap="round" />
                      <circle cx="120" cy="24" r="7.5" fill="#A855F7" filter="url(#dtapex-violet-glow)" />
                      <circle cx="120" cy="24" r="3.2" fill="#FFFFFF" />

                      <!-- Left & Right Cyber Ear Headset Pods with Neon Rings -->
                      <!-- Left Ear Pod -->
                      <rect x="58" y="70" width="10" height="28" rx="5" fill="#160D38" stroke="#00F0FF" stroke-width="1.4" />
                      <line x1="63" y1="74" x2="63" y2="94" stroke="#00F0FF" stroke-width="2" stroke-linecap="round" />
                      <!-- Right Ear Pod -->
                      <rect x="172" y="70" width="10" height="28" rx="5" fill="#160D38" stroke="#00F0FF" stroke-width="1.4" />
                      <line x1="177" y1="74" x2="177" y2="94" stroke="#00F0FF" stroke-width="2" stroke-linecap="round" />

                      <!-- Main Helmet Dome (Glossy White 3D Sphere) -->
                      <rect x="66" y="46" width="108" height="78" rx="36" fill="url(#dtapex-pearl-white)" stroke="rgba(255,255,255,0.85)" stroke-width="2" />

                      <!-- Aerodynamic Curved Glass Visor (Deep Obsidian with Cyan Border) -->
                      <rect x="76" y="58" width="88" height="52" rx="24" fill="url(#dtapex-visor-glass)" stroke="#00F0FF" stroke-width="1.8" />
                      
                      <!-- Visor Gloss Reflection Sheen Curve -->
                      <path d="M 86 64 Q 120 54 154 64 Q 120 72 86 64 Z" fill="url(#dtapex-glass-sheen)" />

                      <!-- Blinking Expressive Cyan Eyes & White Pupils -->
                      <g class="dtapex-robot-eyes">
                        <!-- Left Eye -->
                        <ellipse cx="102" cy="84" rx="7.5" ry="9.5" fill="#00F0FF" filter="url(#dtapex-cyan-glow)" />
                        <circle cx="104.5" cy="81.5" r="2.8" fill="#FFFFFF" />
                        <!-- Right Eye -->
                        <ellipse cx="138" cy="84" rx="7.5" ry="9.5" fill="#00F0FF" filter="url(#dtapex-cyan-glow)" />
                        <circle cx="140.5" cy="81.5" r="2.8" fill="#FFFFFF" />
                      </g>

                      <!-- Soft Friendly Violet Blush Accents -->
                      <circle cx="92" cy="97" r="3.6" fill="rgba(168, 85, 247, 0.7)" filter="url(#dtapex-violet-glow)" />
                      <circle cx="148" cy="97" r="3.6" fill="rgba(168, 85, 247, 0.7)" filter="url(#dtapex-violet-glow)" />
                    </g>

                    <!-- Right Arm: Sleek Articulated WAVING Hand (dtapex.com style) -->
                    <g class="dtapex-waving-arm">
                      <!-- Shoulder Joint (Pivot Center: 172, 142) -->
                      <circle cx="172" cy="142" r="11" fill="url(#dtapex-pearl-white)" stroke="#38BDF8" stroke-width="1.2" />
                      <circle cx="172" cy="142" r="4.5" fill="#160D38" />

                      <!-- Raised Forearm angled up in friendly wave -->
                      <path d="M 172 138 Q 200 120 196 92" fill="none" stroke="url(#dtapex-pearl-white)" stroke-width="8.5" stroke-linecap="round" />
                      <path d="M 172 138 Q 200 120 196 92" fill="none" stroke="#00F0FF" stroke-width="1.5" stroke-linecap="round" opacity="0.6" />

                      <!-- Articulated Wrist & Palm Sphere -->
                      <circle cx="196" cy="86" r="8" fill="url(#dtapex-pearl-white)" stroke="#00F0FF" stroke-width="1.4" />
                      <circle cx="196" cy="86" r="3.5" fill="#160D38" />

                      <!-- 4 Articulated Waving Cyber Fingers with Glowing Tips -->
                      <!-- Thumb -->
                      <path d="M 189 86 Q 180 82 178 77" fill="none" stroke="url(#dtapex-pearl-white)" stroke-width="2.8" stroke-linecap="round" />
                      <circle cx="178" cy="77" r="1.8" fill="#00F0FF" />
                      <!-- Index Finger -->
                      <path d="M 193 80 Q 190 70 188 62" fill="none" stroke="url(#dtapex-pearl-white)" stroke-width="2.8" stroke-linecap="round" />
                      <circle cx="188" cy="62" r="1.8" fill="#00F0FF" />
                      <!-- Middle Finger -->
                      <path d="M 197 79 Q 198 68 200 59" fill="none" stroke="url(#dtapex-pearl-white)" stroke-width="2.8" stroke-linecap="round" />
                      <circle cx="200" cy="59" r="1.8" fill="#00F0FF" />
                      <!-- Pinky Finger -->
                      <path d="M 201 82 Q 208 73 212 66" fill="none" stroke="url(#dtapex-pearl-white)" stroke-width="2.8" stroke-linecap="round" />
                      <circle cx="212" cy="66" r="1.8" fill="#00F0FF" />
                    </g>
                  </g>
                </svg>
              </div>
            </div>

            <!-- Greeting Prompt & Blueprint Overview -->
            <div class="vg-robot-intro-col">
              <div class="vg-robot-pill-badge mb-2">
                <span class="vg-live-pulse-dot" style="background: #00F0FF; box-shadow: 0 0 10px #00F0FF;"></span>
                <span>AI Solution Architect • Ready to Assist</span>
              </div>
              <h3 class="vg-robot-intro-title">Select a service on the left to explore how we engineer it</h3>
              <p class="vg-robot-intro-sub">
                Click or tap any of the 8 services on the left to inspect our custom tech blueprints, performance benchmarks, and deployment deliverables.
              </p>

              <!-- 3 Interactive Feature Metric Badges -->
              <div class="vg-robot-features-row">
                <div class="vg-robot-feature-chip">
                  <i class="fa-solid fa-gauge-high" style="color: #00F0FF;"></i>
                  <span>99+ Speed Vitals</span>
                </div>
                <div class="vg-robot-feature-chip">
                  <i class="fa-solid fa-code" style="color: #A855F7;"></i>
                  <span>Clean Scalable Code</span>
                </div>
                <div class="vg-robot-feature-chip">
                  <i class="fa-solid fa-shield-halved" style="color: #10B981;"></i>
                  <span>100% Code Ownership</span>
                </div>
              </div>

              <div class="vg-robot-action-prompt">
                <i class="fa-solid fa-arrow-pointer me-2" style="color: #00F0FF;"></i>
                <span>Click any service topic to reveal full architecture specs</span>
              </div>
            </div>
          </div>
        </div>
      `;

      previewEl.style.opacity = '1';
      previewEl.style.transform = 'translateY(0)';
    }, 120);
  }

  // Render Selected Service Detail View
  function renderServiceDetail(service, previewEl, onReset) {
    if (!service || !previewEl) return;

    const encodedMsg = encodeURIComponent(service.whatsappMsg);
    const waLink = `https://wa.me/918695776922?text=${encodedMsg}`;

    const metricsHtml = service.metrics.map(m => `
      <div class="vg-metric-tile">
        <div class="vg-metric-icon">
          <i class="${m.icon}" style="color: ${service.accentColor};"></i>
        </div>
        <div class="vg-metric-content">
          <span class="vg-metric-val">${m.val}</span>
          <span class="vg-metric-desc">${m.desc}</span>
        </div>
      </div>
    `).join('');

    previewEl.style.opacity = '0';
    previewEl.style.transform = 'translateY(6px)';

    setTimeout(() => {
      previewEl.innerHTML = `
        <div class="vg-preview-header">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
              <div class="vg-preview-icon-box" style="background: ${service.accentColor}18; border-color: ${service.accentColor}40; box-shadow: 0 0 16px ${service.accentColor}25;">
                <i class="${service.icon}" style="color: ${service.accentColor};"></i>
              </div>
              <div>
                <span class="vg-service-tag ${service.tagClass} mb-1 d-inline-block">
                  <i class="fa-solid fa-bolt me-1"></i> ${service.tag}
                </span>
                <h3 class="vg-preview-title">${service.title}</h3>
              </div>
            </div>
            <div class="d-flex align-items-center gap-2">
              <div class="vg-preview-badge-status">
                <span class="vg-live-pulse-dot" style="background: ${service.accentColor}; box-shadow: 0 0 10px ${service.accentColor};"></span>
                <span>Enterprise Ready</span>
              </div>
              <button type="button" class="vg-reset-robot-btn" title="Back to AI Architect Guide" aria-label="Reset selection">
                <i class="fa-solid fa-rotate-left"></i>
              </button>
            </div>
          </div>
        </div>

        <div class="vg-preview-body">
          <p class="vg-preview-overview">${service.overview}</p>

          <div class="vg-preview-deliverables-wrap">
            <div class="vg-deliverables-title">
              <i class="fa-solid fa-circle-check me-2" style="color: #00F0FF;"></i> Key Deliverables &amp; Performance Standards
            </div>
            <div class="vg-metrics-grid">
              ${metricsHtml}
            </div>
          </div>
        </div>

        <div class="vg-preview-footer">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <a href="${waLink}" class="vg-preview-cta-btn" target="_blank" rel="noopener" aria-label="Discuss ${service.title} on WhatsApp">
              <span>Discuss This Solution</span>
              <i class="fa-solid fa-arrow-right ms-2"></i>
            </a>
            <div class="vg-preview-guarantee">
              <i class="fa-solid fa-shield-halved me-1" style="color: #10B981;"></i>
              <span>Production-Grade Code • Zero Lock-in</span>
            </div>
          </div>
        </div>
      `;

      // Attach reset button
      const resetBtn = previewEl.querySelector('.vg-reset-robot-btn');
      if (resetBtn && onReset) {
        resetBtn.addEventListener('click', (e) => {
          e.stopPropagation();
          onReset();
        });
      }

      previewEl.style.opacity = '1';
      previewEl.style.transform = 'translateY(0)';
    }, 120);
  }

  function initShowcase() {
    const listContainer = document.querySelector('.vg-service-nav-list');
    const previewEl = document.getElementById('vg-service-preview-card');
    if (!listContainer || !previewEl) return;

    const serviceKeys = Object.keys(SERVICES_DATA);
    let activeKey = null; // DEFAULT STATE: No service auto-selected!

    // Build the 8 interactive selector items in Left Column (tight 2-col x 4-row layout)
    listContainer.innerHTML = serviceKeys.map((key) => {
      const s = SERVICES_DATA[key];
      return `
        <div class="vg-service-nav-item" data-service-key="${s.id}" role="tab" aria-selected="false" tabindex="0">
          <div class="vg-nav-item-left">
            <div class="vg-nav-icon-wrap" style="--item-color: ${s.accentColor};">
              <i class="${s.icon}"></i>
            </div>
            <div class="vg-nav-text-wrap">
              <h4 class="vg-nav-title">${s.title}</h4>
              <span class="vg-service-tag ${s.tagClass}"><i class="fa-solid fa-bolt me-1"></i> ${s.tag}</span>
            </div>
          </div>
          <div class="vg-nav-chevron">
            <i class="fa-solid fa-chevron-right"></i>
          </div>
        </div>
      `;
    }).join('');

    const navItems = listContainer.querySelectorAll('.vg-service-nav-item');

    function resetToDefault() {
      activeKey = null;
      navItems.forEach(item => {
        item.classList.remove('is-active');
        item.setAttribute('aria-selected', 'false');
      });
      renderDefaultRobot(previewEl, resetToDefault);
    }

    function selectService(key) {
      // Toggle off if clicking the currently active service
      if (activeKey === key) {
        resetToDefault();
        return;
      }

      activeKey = key;
      navItems.forEach(item => {
        const isMatch = item.getAttribute('data-service-key') === key;
        item.classList.toggle('is-active', isMatch);
        item.setAttribute('aria-selected', isMatch ? 'true' : 'false');
      });

      renderServiceDetail(SERVICES_DATA[key], previewEl, resetToDefault);
    }

    // Attach Click and Touch events (no hover forced override to keep default state clean)
    navItems.forEach(item => {
      const key = item.getAttribute('data-service-key');

      // Click or tap to select
      item.addEventListener('click', (e) => {
        e.preventDefault();
        selectService(key);
      });

      // Keyboard accessibility
      item.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          selectService(key);
        }
      });
    });

    // Initial render: DEFAULT STATE — Animated Waving AI Robot!
    renderDefaultRobot(previewEl, resetToDefault);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initShowcase);
  } else {
    initShowcase();
  }
})();
