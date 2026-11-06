<?php
$page_title = 'Client Requirement Form | VG Digital Falcon';
$admin = false;
require __DIR__ . '/includes/header.php';
?>
<section class="hero">
  <h1>Website Client Requirement Form</h1>
  <p class="lede">FR Holding Inc. discovery form. Tick what you know. Use Not sure for the rest. This tells us whether you want one group website (like sigmapac.com) or separate websites for each company.</p>
</section>

<form method="post" action="submit.php">
  <section class="card">
    <h2>Your details</h2>
    <p class="hint">So our team knows who submitted this form.</p>
    <div class="grid-3">
      <div><label class="field">Full name <span class="req">*</span></label><input type="text" name="contact_name" required></div>
      <div><label class="field">Email <span class="req">*</span></label><input type="email" name="contact_email" required></div>
      <div><label class="field">WhatsApp / phone</label><input type="tel" name="contact_phone"></div>
    </div>
    <label class="field">Role</label>
    <input type="text" name="contact_role" placeholder="Founder / CEO / Manager">
  </section>

  <section class="card">
    <h2>A. Group, domain and ownership</h2>
    <p class="hint">Questions 1–4</p>
    <label class="field">1. Parent company name</label>
    <div class="choices">
      <label class="choice"><input type="radio" name="q1_company" value="Faisal Rehman Holding Inc. / FR Holding Inc." checked> Faisal Rehman Holding Inc. / FR Holding Inc.</label>
      <label class="choice"><input type="radio" name="q1_company" value="Other"> Other</label>
    </div>
    <label class="field">If other, write the exact name</label>
    <input type="text" name="q1_company_other">
    <label class="field">2. Do you already have a group domain?</label>
    <div class="choices">
      <label class="choice"><input type="radio" name="q2_domain_status" value="Yes, and we have full access"> Yes, and we have full access</label>
      <label class="choice"><input type="radio" name="q2_domain_status" value="Yes, but access is with someone else"> Yes, but access is with someone else</label>
      <label class="choice"><input type="radio" name="q2_domain_status" value="No domain yet"> No domain yet</label>
      <label class="choice"><input type="radio" name="q2_domain_status" value="Not sure"> Not sure</label>
    </div>
    <div class="grid-2">
      <div><label class="field">If yes, the domain</label><input type="text" name="q2_domain_name" placeholder="frholding.ca"></div>
      <div><label class="field">Who owns / manages it?</label><input type="text" name="q2_domain_owner"></div>
    </div>
    <label class="field">3. Preferred group domain if you do not have one</label>
    <input type="text" name="q3_preferred_domain" placeholder="frholding.ca / frholdinginc.ca">
    <label class="field">4. Main decision-maker for approvals, content and payments</label>
    <input type="text" name="q4_decision_maker">
  </section>

  <section class="card">
    <h2>B. Businesses under the group</h2>
    <p class="hint">Questions 5–8. Tick every company that should be on the website now.</p>
    <label class="field">5. Businesses to include</label>
    <div class="choices">
      <label class="choice"><input type="checkbox" name="q5_businesses[]" value="FR Auto Spa — Auto Detailing & Automotive Care"> FR Auto Spa — Auto Detailing & Automotive Care</label>
      <label class="choice"><input type="checkbox" name="q5_businesses[]" value="Automotive Services Architects (ASA) — Automotive Services & Solutions"> Automotive Services Architects (ASA)</label>
      <label class="choice"><input type="checkbox" name="q5_businesses[]" value="Roll-On Paint & Renos — Home Improvement & Renovations"> Roll-On Paint & Renos</label>
      <label class="choice"><input type="checkbox" name="q5_businesses[]" value="DCA Data Center Architects — IT, Networking & Data Center"> DCA Data Center Architects</label>
      <label class="choice"><input type="checkbox" name="q5_businesses[]" value="GetFiT 24/7 — Fitness & Wellness"> GetFiT 24/7</label>
      <label class="choice"><input type="checkbox" name="q5_businesses[]" value="Blue Bird Security (BBS) — Security Services"> Blue Bird Security (BBS)</label>
    </div>
    <label class="field">Other / launching soon</label>
    <input type="text" name="q5_other">
    <label class="field">6. For each selected business: public name, city, 1-line offer, phone if different</label>
    <textarea name="q6_business_details"></textarea>
    <label class="field">7. Legal structure</label>
    <div class="choices">
      <label class="choice"><input type="radio" name="q7_legal" value="All under FR Holding Inc. only"> All under FR Holding Inc. only</label>
      <label class="choice"><input type="radio" name="q7_legal" value="Mix: some separate companies, some divisions"> Mix: some separate companies, some divisions</label>
      <label class="choice"><input type="radio" name="q7_legal" value="Each is a separate registered company"> Each is a separate registered company</label>
      <label class="choice"><input type="radio" name="q7_legal" value="Not sure"> Not sure</label>
    </div>
    <label class="field">8. Branding per business</label>
    <div class="choices">
      <label class="choice"><input type="radio" name="q8_branding" value="Yes, fully separate brands"> Yes, fully separate brands</label>
      <label class="choice"><input type="radio" name="q8_branding" value="Same group look, different names only"> Same group look, different names only</label>
      <label class="choice"><input type="radio" name="q8_branding" value="Some have logos, some do not"> Some have logos, some do not</label>
      <label class="choice"><input type="radio" name="q8_branding" value="Need new branding"> Need new branding</label>
    </div>
  </section>

  <section class="card">
    <h2>C. One website or many</h2>
    <p class="hint">Questions 9–12. This matches the sigmapac.com reference the client sent.</p>
    <div class="note">SigmaPac is one domain with a page for each unit. We recommend the same launch: one group website + a dedicated page per company + a separate Google Business Profile per company. Separate domains only if a brand already has enough content, reviews and budget.</div>
    <label class="field">9. What should a visitor experience?</label>
    <div class="choices">
      <label class="choice"><input type="radio" name="q9_architecture" value="Option 1 — One group website (SigmaPac style)"> <div><b>Option 1 — SigmaPac style</b><br>One group website. Homepage shows the group. Each company has its own page. Recommended starting point.</div></label>
      <label class="choice"><input type="radio" name="q9_architecture" value="Option 2 — Group hub + separate websites"> <div><b>Option 2 — Group hub + separate websites</b><br>One group site and a full separate website and domain for each company.</div></label>
      <label class="choice"><input type="radio" name="q9_architecture" value="Option 3 — No group site, only separate websites"> <div><b>Option 3 — No group site</b><br>Only separate websites for each company.</div></label>
      <label class="choice"><input type="radio" name="q9_architecture" value="Not sure. Please recommend."> Not sure. Please recommend.</label>
    </div>
    <label class="field">10. If we use one main domain, how should company URLs look?</label>
    <div class="choices">
      <label class="choice"><input type="radio" name="q10_urls" value="Folder: frholding.ca/auto-spa"> Folder: frholding.ca/auto-spa</label>
      <label class="choice"><input type="radio" name="q10_urls" value="Subdomain: autospa.frholding.ca"> Subdomain: autospa.frholding.ca</label>
      <label class="choice"><input type="radio" name="q10_urls" value="Separate domain later: frautospa.ca"> Separate domain later: frautospa.ca</label>
      <label class="choice"><input type="radio" name="q10_urls" value="Recommend what is best for SEO and cost"> Recommend what is best for SEO and cost</label>
    </div>
    <label class="field">11. Should the first page be a group homepage where the visitor picks a company?</label>
    <div class="choices">
      <label class="choice"><input type="radio" name="q11_homepage" value="Yes"> Yes</label>
      <label class="choice"><input type="radio" name="q11_homepage" value="No — send people straight to a company"> No — send people straight to a company</label>
      <label class="choice"><input type="radio" name="q11_homepage" value="Depends on how they found us"> Depends on how they found us</label>
    </div>
    <label class="field">12. Do you agree with the recommended launch plan?</label>
    <div class="choices">
      <label class="choice"><input type="radio" name="q12_recommend" value="Agree with one group website + company pages + GBP per company"> Agree with one group website + company pages + Google profile per company</label>
      <label class="choice"><input type="radio" name="q12_recommend" value="We still want separate domains from day one"> We still want separate domains from day one</label>
      <label class="choice"><input type="radio" name="q12_recommend" value="Discuss on a call"> Discuss on a call</label>
    </div>
  </section>

  <section class="card">
    <h2>D. Existing websites, domains and traffic</h2>
    <p class="hint">Questions 13–15</p>
    <label class="field">13. List every existing website or domain. If none, write None.</label>
    <textarea name="q13_existing" placeholder="Business | URL | keep / replace / redirect | traffic now?"></textarea>
    <label class="field">14. If old sites exist, what should happen?</label>
    <div class="choices">
      <label class="choice"><input type="radio" name="q14_old_sites" value="Permanent redirect to the matching new page"> Permanent redirect to the matching new page</label>
      <label class="choice"><input type="radio" name="q14_old_sites" value="Keep old site live as well"> Keep old site live as well</label>
      <label class="choice"><input type="radio" name="q14_old_sites" value="Take old site down"> Take old site down</label>
      <label class="choice"><input type="radio" name="q14_old_sites" value="Not sure"> Not sure</label>
    </div>
    <label class="field">15. Access to those domains, hosting and DNS?</label>
    <div class="choices">
      <label class="choice"><input type="radio" name="q15_access" value="Yes, all"> Yes, all</label>
      <label class="choice"><input type="radio" name="q15_access" value="Partial"> Partial</label>
      <label class="choice"><input type="radio" name="q15_access" value="No"> No</label>
      <label class="choice"><input type="radio" name="q15_access" value="Not sure"> Not sure</label>
    </div>
  </section>

  <section class="card">
    <h2>E. Branding, content and proof</h2>
    <p class="hint">Questions 16–18</p>
    <label class="field">16. What can you give us on day one?</label>
    <div class="choices">
      <label class="choice"><input type="checkbox" name="q16_assets[]" value="Group logo"> Group logo</label>
      <label class="choice"><input type="checkbox" name="q16_assets[]" value="Company logos"> Company logos</label>
      <label class="choice"><input type="checkbox" name="q16_assets[]" value="Brand colours / fonts"> Brand colours / fonts</label>
      <label class="choice"><input type="checkbox" name="q16_assets[]" value="Written service lists"> Written service lists</label>
      <label class="choice"><input type="checkbox" name="q16_assets[]" value="Photos"> Photos</label>
      <label class="choice"><input type="checkbox" name="q16_assets[]" value="Videos"> Videos</label>
      <label class="choice"><input type="checkbox" name="q16_assets[]" value="Staff photos"> Staff photos</label>
      <label class="choice"><input type="checkbox" name="q16_assets[]" value="Price lists"> Price lists</label>
      <label class="choice"><input type="checkbox" name="q16_assets[]" value="Nothing yet — write and design for us"> Nothing yet — write and design for us</label>
    </div>
    <label class="field">17. Who writes the website text?</label>
    <div class="choices">
      <label class="choice"><input type="radio" name="q17_copy" value="Client provides all text"> Client provides all text</label>
      <label class="choice"><input type="radio" name="q17_copy" value="We write, you approve"> We write, you approve</label>
      <label class="choice"><input type="radio" name="q17_copy" value="Mix"> Mix</label>
    </div>
    <label class="field">18. Reference websites besides sigmapac.com</label>
    <textarea name="q18_references"></textarea>
  </section>

  <section class="card">
    <h2>F. Pages and features</h2>
    <p class="hint">Questions 19–22</p>
    <label class="field">19. Group / holding pages needed</label>
    <div class="choices">
      <label class="choice"><input type="checkbox" name="q19_group_pages[]" value="Home"> Home</label>
      <label class="choice"><input type="checkbox" name="q19_group_pages[]" value="About the group / founder"> About the group / founder</label>
      <label class="choice"><input type="checkbox" name="q19_group_pages[]" value="All companies"> All companies</label>
      <label class="choice"><input type="checkbox" name="q19_group_pages[]" value="Leadership"> Leadership</label>
      <label class="choice"><input type="checkbox" name="q19_group_pages[]" value="Careers"> Careers</label>
      <label class="choice"><input type="checkbox" name="q19_group_pages[]" value="Group contact"> Group contact</label>
    </div>
    <label class="field">Other group pages</label>
    <input type="text" name="q19_other">
    <label class="field">20. Pages for each company</label>
    <div class="choices">
      <label class="choice"><input type="checkbox" name="q20_company_pages[]" value="Home / overview"> Home / overview</label>
      <label class="choice"><input type="checkbox" name="q20_company_pages[]" value="Services list"> Services list</label>
      <label class="choice"><input type="checkbox" name="q20_company_pages[]" value="Service detail pages"> Service detail pages</label>
      <label class="choice"><input type="checkbox" name="q20_company_pages[]" value="Gallery / before-after"> Gallery / before-after</label>
      <label class="choice"><input type="checkbox" name="q20_company_pages[]" value="About that company"> About that company</label>
      <label class="choice"><input type="checkbox" name="q20_company_pages[]" value="Team"> Team</label>
      <label class="choice"><input type="checkbox" name="q20_company_pages[]" value="Pricing or packages"> Pricing or packages</label>
      <label class="choice"><input type="checkbox" name="q20_company_pages[]" value="FAQ"> FAQ</label>
      <label class="choice"><input type="checkbox" name="q20_company_pages[]" value="Reviews"> Reviews</label>
      <label class="choice"><input type="checkbox" name="q20_company_pages[]" value="Contact / quote"> Contact / quote</label>
      <label class="choice"><input type="checkbox" name="q20_company_pages[]" value="Blog"> Blog</label>
    </div>
    <label class="field">21. Must-have features</label>
    <div class="choices">
      <label class="choice"><input type="checkbox" name="q21_features[]" value="Contact form"> Contact form</label>
      <label class="choice"><input type="checkbox" name="q21_features[]" value="Service enquiry / quote form"> Service enquiry / quote form</label>
      <label class="choice"><input type="checkbox" name="q21_features[]" value="Click-to-call"> Click-to-call</label>
      <label class="choice"><input type="checkbox" name="q21_features[]" value="WhatsApp button"> WhatsApp button</label>
      <label class="choice"><input type="checkbox" name="q21_features[]" value="Google Map / service area"> Google Map / service area</label>
      <label class="choice"><input type="checkbox" name="q21_features[]" value="Photo gallery"> Photo gallery</label>
      <label class="choice"><input type="checkbox" name="q21_features[]" value="Video embed"> Video embed</label>
      <label class="choice"><input type="checkbox" name="q21_features[]" value="Testimonials"> Testimonials</label>
      <label class="choice"><input type="checkbox" name="q21_features[]" value="Google reviews embed"> Google reviews embed</label>
      <label class="choice"><input type="checkbox" name="q21_features[]" value="Online booking / appointments"> Online booking / appointments</label>
      <label class="choice"><input type="checkbox" name="q21_features[]" value="Online payment"> Online payment</label>
      <label class="choice"><input type="checkbox" name="q21_features[]" value="Live chat"> Live chat</label>
      <label class="choice"><input type="checkbox" name="q21_features[]" value="Multi-language"> Multi-language</label>
      <label class="choice"><input type="checkbox" name="q21_features[]" value="Blog / news"> Blog / news</label>
      <label class="choice"><input type="checkbox" name="q21_features[]" value="Social links"> Social links</label>
    </div>
    <label class="field">22. Booking / lead needs by business</label>
    <textarea name="q22_booking"></textarea>
  </section>

  <section class="card">
    <h2>G. Locations, Google and SEO</h2>
    <p class="hint">Questions 23–27</p>
    <div class="grid-2">
      <div><label class="field">23. Primary country</label><input type="text" name="q23_country" placeholder="Canada"></div>
      <div><label class="field">Cities / service areas</label><input type="text" name="q23_cities"></div>
    </div>
    <label class="field">24. Google Business Profile status</label>
    <div class="choices">
      <label class="choice"><input type="radio" name="q24_gbp" value="Yes, all"> Yes, all</label>
      <label class="choice"><input type="radio" name="q24_gbp" value="Some"> Some</label>
      <label class="choice"><input type="radio" name="q24_gbp" value="None"> None</label>
      <label class="choice"><input type="radio" name="q24_gbp" value="Not sure"> Not sure</label>
    </div>
    <label class="field">Google profile links if you have them</label>
    <textarea name="q24_gbp_links"></textarea>
    <label class="field">25. Priority search terms in the customer’s words</label>
    <textarea name="q25_keywords"></textarea>
    <label class="field">26. Google Analytics / GA4 and Search Console?</label>
    <div class="choices">
      <label class="choice"><input type="radio" name="q26_ga" value="Yes"> Yes</label>
      <label class="choice"><input type="radio" name="q26_ga" value="No"> No</label>
      <label class="choice"><input type="radio" name="q26_ga" value="Not sure"> Not sure</label>
    </div>
    <label class="field">27. SEO launch scope</label>
    <div class="choices">
      <label class="choice"><input type="radio" name="q27_seo_scope" value="Basic: fast site, proper titles, locations, maps, forms"> Basic</label>
      <label class="choice"><input type="radio" name="q27_seo_scope" value="Standard: above + GBP setup help + on-page SEO per company"> Standard</label>
      <label class="choice"><input type="radio" name="q27_seo_scope" value="Aggressive: separate domains, content plan, review system, monthly SEO"> Aggressive</label>
    </div>
  </section>

  <section class="card">
    <h2>H. Technical, email, hosting</h2>
    <p class="hint">Questions 28–29</p>
    <label class="field">28. Current hosting</label>
    <input type="text" name="q28_hosting">
    <label class="field">Business emails already in use</label>
    <textarea name="q28_emails"></textarea>
    <label class="field">Must those email addresses stay the same after launch?</label>
    <div class="choices">
      <label class="choice"><input type="radio" name="q28_keep_email" value="Yes, do not break email"> Yes, do not break email</label>
      <label class="choice"><input type="radio" name="q28_keep_email" value="Can change"> Can change</label>
      <label class="choice"><input type="radio" name="q28_keep_email" value="Not sure"> Not sure</label>
    </div>
    <label class="field">29. Who will manage hosting, domain and updates after launch?</label>
    <div class="choices">
      <label class="choice"><input type="radio" name="q29_manage" value="Client team"> Client team</label>
      <label class="choice"><input type="radio" name="q29_manage" value="Our team (retainer)"> Our team (retainer)</label>
      <label class="choice"><input type="radio" name="q29_manage" value="Decide later"> Decide later</label>
    </div>
  </section>

  <section class="card">
    <h2>I. Timeline, budget and next step</h2>
    <p class="hint">Questions 30–33</p>
    <label class="field">30. When do you want the first version live?</label>
    <div class="choices">
      <label class="choice"><input type="radio" name="q30_timeline" value="2–3 weeks (core pages only)"> 2–3 weeks</label>
      <label class="choice"><input type="radio" name="q30_timeline" value="4–6 weeks (group + all selected companies)"> 4–6 weeks</label>
      <label class="choice"><input type="radio" name="q30_timeline" value="7–10 weeks (full content, booking, SEO setup)"> 7–10 weeks</label>
    </div>
    <label class="field">Or a specific date</label>
    <input type="text" name="q30_date" placeholder="YYYY-MM-DD">
    <label class="field">31. Which build should we quote?</label>
    <div class="choices">
      <label class="choice"><input type="radio" name="q31_package" value="Package A — One group website"> <div><b>Package A</b> One group website with a section for each company (SigmaPac style)</div></label>
      <label class="choice"><input type="radio" name="q31_package" value="Package B — Group website + 1–2 priority mini-sites"> <div><b>Package B</b> Group website + 1–2 priority mini-sites</div></label>
      <label class="choice"><input type="radio" name="q31_package" value="Package C — Separate website + domain for every company"> <div><b>Package C</b> Separate website + domain for every company</div></label>
      <label class="choice"><input type="radio" name="q31_package" value="Quote A and C so we can compare"> Quote A and C so we can compare</label>
    </div>
    <label class="field">32. Budget range for design + build</label>
    <div class="choices">
      <label class="choice"><input type="radio" name="q32_budget" value="Under $1,500"> Under $1,500</label>
      <label class="choice"><input type="radio" name="q32_budget" value="$1,500–$3,000"> $1,500–$3,000</label>
      <label class="choice"><input type="radio" name="q32_budget" value="$3,000–$6,000"> $3,000–$6,000</label>
      <label class="choice"><input type="radio" name="q32_budget" value="$6,000+"> $6,000+</label>
      <label class="choice"><input type="radio" name="q32_budget" value="Tell us the right range for the scope we chose"> Tell us the right range</label>
    </div>
    <label class="field">33. Anything we must not miss?</label>
    <textarea name="q33_notes"></textarea>
  </section>

  <div class="actions">
    <span class="lede">Required: name and email only.</span>
    <button class="btn btn-orange" type="submit">Submit requirement form</button>
  </div>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
