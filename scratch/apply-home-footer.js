const fs = require('fs');

const homeFooterHtml = `        <!-- ==========================================================================
             FOOTER (DEEP OBSIDIAN BRAND LOCKUP - HOME PAGE FOOTER)
             ========================================================================== -->
        <footer class="vg-footer">
            <div class="container">
                <div class="vg-footer-grid">
                    <!-- Col 1: Brand Info -->
                    <div class="vg-footer-about">
                        <a href="index.html" class="vg-brand-wrap mb-3" aria-label="VG Digital Falcon Homepage">
                            <img src="assets/img/logo/dflogo-white.png?v=2" alt="VG Digital Falcon Logo" class="vg-header-brand-logo vg-footer-brand-logo">
                        </a>
                        <p>
                            We help businesses grow smarter, faster and stronger with AI-powered digital marketing and performance systems.
                        </p>
                        <div class="vg-footer-socials">
                            <a href="https://www.facebook.com/" target="_blank" rel="noopener" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                            <a href="https://www.instagram.com/vgdigitalfalcon/" target="_blank" rel="noopener" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                            <a href="https://www.linkedin.com/company/vgdigitalfalcon/" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
                            <a href="https://www.youtube.com/" target="_blank" rel="noopener" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
                        </div>
                    </div>

                    <!-- Col 2: Company -->
                    <div class="vg-footer-col">
                        <h5>Company</h5>
                        <ul>
                            <li><a href="about-us.html">About Us</a></li>
                            <li><a href="#faq">FAQs</a></li>
                            <li><a href="index.html#insights">Blog &amp; Insights</a></li>
                            <li><a href="index.html#case-studies">Case Studies</a></li>
                            <li><a href="contact.html">Contact Us</a></li>
                        </ul>
                    </div>

                    <!-- Col 3: Services -->
                    <div class="vg-footer-col">
                        <h5>Services</h5>
                        <ul>
                            <li><a href="seo-service.html">SEO</a></li>
                            <li><a href="ecommerce-seo-service.html">Ecommerce SEO</a></li>
                            <li><a href="performance-marketing.html">Google Ads</a></li>
                            <li><a href="performance-marketing.html">Meta Ads</a></li>
                            <li><a href="performance-marketing.html">Performance Marketing</a></li>
                            <li><a href="personal-branding-service.html">Content Marketing</a></li>
                            <li><a href="web-development.html">Website Development</a></li>
                            <li><a href="index.html#services">AI Automation</a></li>
                        </ul>
                    </div>

                    <!-- Col 4: Industries -->
                    <div class="vg-footer-col">
                        <h5>Industries</h5>
                        <ul>
                            <li><a href="#">Healthcare</a></li>
                            <li><a href="#">Education</a></li>
                            <li><a href="#">Real Estate</a></li>
                            <li><a href="ecommerce-seo-service.html">E-commerce</a></li>
                            <li><a href="#">Manufacturing</a></li>
                            <li><a href="#">Retail</a></li>
                        </ul>
                    </div>

                    <!-- Col 5: Resources & Newsletter -->
                    <div class="vg-footer-col">
                        <h5>Newsletter</h5>
                        <p style="color: #cbd5e1; font-size: 13px; line-height: 1.5;">Get the latest marketing insights &amp; AI growth updates.</p>
                        <form action="#" method="POST" class="vg-newsletter-box">
                            <input type="email" placeholder="Enter your email" aria-label="Enter your email" required>
                            <button type="submit" aria-label="Subscribe to newsletter"><i class="fa-regular fa-paper-plane"></i></button>
                        </form>
                    </div>
                </div>

                <!-- Footer Bottom -->
                <div class="vg-footer-bottom">
                    <div>
                        © 2026 VG Digital Falcon. All Rights Reserved.
                    </div>
                    <div>
                        <a href="index.html">Privacy Policy</a>
                        <a href="index.html">Terms &amp; Conditions</a>
                    </div>
                </div>
            </div>
        </footer>`;

const files = [
  'ecommerce-seo-service.html',
  'public_html (1)/ecommerce-seo-service.html',
  'public_html (1)/website/ecommerce-seo-service.html'
];

files.forEach(file => {
  if (fs.existsSync(file)) {
    let content = fs.readFileSync(file, 'utf8');
    
    // Find footer block from <!-- === FOOTER SECTION ... to </footer>
    const footerRegex = /<!--\s*={5,}\s*FOOTER SECTION[\s\S]*?<\/footer>/i;
    if (footerRegex.test(content)) {
      content = content.replace(footerRegex, homeFooterHtml.trim());
      fs.writeFileSync(file, content, 'utf8');
      console.log(`Updated footer to home page footer in ${file}`);
    } else {
      console.error(`Could not match footerRegex in ${file}`);
      // Fallback matching <footer class="footer-section... </footer>
      const fallbackRegex = /<footer class="footer-section[\s\S]*?<\/footer>/i;
      if (fallbackRegex.test(content)) {
        content = content.replace(fallbackRegex, homeFooterHtml.trim());
        fs.writeFileSync(file, content, 'utf8');
        console.log(`Updated footer via fallback regex in ${file}`);
      } else {
        console.error(`Fallback regex also failed in ${file}`);
      }
    }
  }
});

