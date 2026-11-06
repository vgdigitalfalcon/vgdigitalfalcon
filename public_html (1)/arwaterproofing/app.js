(() => {
  "use strict";

  const menuToggle = document.getElementById("menuToggle");
  const mobileMenu = document.getElementById("mobileMenu");
  const installButton = document.getElementById("installButton");
  const toast = document.getElementById("toast");
  const year = document.getElementById("year");
  let deferredInstallPrompt = null;
  let toastTimer = null;

  if (year) year.textContent = String(new Date().getFullYear());

  function showToast(message) {
    if (!toast) return;
    toast.textContent = message;
    toast.classList.add("show");
    window.clearTimeout(toastTimer);
    toastTimer = window.setTimeout(() => toast.classList.remove("show"), 2800);
  }

  if (menuToggle && mobileMenu) {
    menuToggle.addEventListener("click", () => {
      const isExpanded = menuToggle.getAttribute("aria-expanded") === "true";
      menuToggle.setAttribute("aria-expanded", String(!isExpanded));
      menuToggle.setAttribute("aria-label", isExpanded ? "Open navigation" : "Close navigation");
      mobileMenu.hidden = isExpanded;
    });

    mobileMenu.querySelectorAll("a").forEach((link) => {
      link.addEventListener("click", () => {
        mobileMenu.hidden = true;
        menuToggle.setAttribute("aria-expanded", "false");
        menuToggle.setAttribute("aria-label", "Open navigation");
      });
    });

    document.addEventListener("click", (event) => {
      if (!mobileMenu.hidden && !mobileMenu.contains(event.target) && !menuToggle.contains(event.target)) {
        mobileMenu.hidden = true;
        menuToggle.setAttribute("aria-expanded", "false");
        menuToggle.setAttribute("aria-label", "Open navigation");
      }
    });
  }

  window.addEventListener("beforeinstallprompt", (event) => {
    event.preventDefault();
    deferredInstallPrompt = event;
    if (installButton) installButton.hidden = false;
  });

  if (installButton) {
    installButton.addEventListener("click", async () => {
      if (!deferredInstallPrompt) {
        showToast("To install, open your browser menu and choose Install app or Add to Home Screen.");
        return;
      }
      deferredInstallPrompt.prompt();
      const choice = await deferredInstallPrompt.userChoice;
      if (choice && choice.outcome === "accepted") showToast("Thanks for installing A.R. Waterproofing.");
      deferredInstallPrompt = null;
      installButton.hidden = true;
    });
  }

  window.addEventListener("appinstalled", () => {
    if (installButton) installButton.hidden = true;
    showToast("A.R. Waterproofing has been installed.");
  });

  if ("serviceWorker" in navigator && (location.protocol === "https:" || location.hostname === "localhost" || location.hostname === "127.0.0.1")) {
    window.addEventListener("load", () => {
      navigator.serviceWorker.register("./sw.js").catch((error) => {
        console.warn("Service worker registration failed:", error);
      });
    });
  }

  // Download the proprietor's contact card and a self-contained company profile.
  const saveContactButton = document.getElementById("saveContactButton");
  const downloadProfileButton = document.getElementById("shareContactButton");
  const bottomSave = document.getElementById("bottomSaveContactButton");
  const bottomDownloadProfile = document.getElementById("bottomShareContactButton");

  const vCard = [
    "BEGIN:VCARD",
    "VERSION:3.0",
    "FN:C. Rajendran",
    "N:Rajendran;C.;;;",
    "ORG:A.R. Waterproofing",
    "TITLE:Founder",
    "TEL;TYPE=CELL,VOICE:+919841044994",
    "TEL;TYPE=WORK,VOICE:+918680070461",
    "EMAIL:rgn.1972@gmail.com",
    "URL:https://arwaterproofing.com/Rajendran/",
    "ADR;TYPE=WORK:;;9/3 Pushpa Nagar Main Road;Nungambakkam;Chennai;Tamil Nadu;600034",
    "NOTE:Waterproofing and construction services",
    "END:VCARD"
  ].join("\r\n");

  function downloadBlob(filename, content, type) {
    const blob = new Blob([content], { type });
    const url = URL.createObjectURL(blob);
    const anchor = document.createElement("a");
    anchor.href = url;
    anchor.download = filename;
    document.body.appendChild(anchor);
    anchor.click();
    anchor.remove();
    window.setTimeout(() => URL.revokeObjectURL(url), 1500);
  }

  if (saveContactButton) {
    saveContactButton.addEventListener("click", () => {
      downloadBlob("C-Rajendran-AR-Waterproofing.vcf", vCard, "text/vcard;charset=utf-8");
      showToast("Contact card downloaded. Open it to save Rajendran's contact.");
    });
  }

  if (bottomSave && saveContactButton) {
    bottomSave.addEventListener("click", () => saveContactButton.click());
  }

  function downloadCompanyProfile() {
    const profileHtml = `<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>C. Rajendran | A.R. Waterproofing Profile</title>
<style>
*{box-sizing:border-box}body{margin:0;padding:28px;background:#f5f3fb;color:#251c3d;font:15px/1.6 Arial,sans-serif}
.card{max-width:760px;margin:auto;background:#fff;border-radius:24px;overflow:hidden;box-shadow:0 15px 45px #27134a15}
.top{background:#55b7b9;color:#fff;padding:30px;text-align:center}.top img{width:150px;height:auto;max-width:60%;margin:0 auto 16px;object-fit:contain}.top h1{margin:0;font-size:26px}.top p{margin:5px 0 0}.body{padding:28px}.eyebrow{color:#5422c8;font-size:11px;letter-spacing:1.5px;font-weight:bold}.body h2{margin:5px 0 14px;font-size:22px}.details{display:grid;grid-template-columns:140px 1fr;gap:10px 14px;margin:20px 0}.label{color:#766d88;font-size:13px}.value{font-weight:600;overflow-wrap:anywhere}.cta{display:inline-block;background:#5422c8;color:white;text-decoration:none;padding:12px 17px;border-radius:10px;margin:6px 8px 0 0;font-weight:bold}.note{color:#766d88;font-size:12px;margin-top:25px;border-top:1px solid #eee;padding-top:15px}
@media(max-width:520px){body{padding:10px}.top{padding:24px 16px}.body{padding:20px}.details{grid-template-columns:1fr;gap:3px}.value{margin-bottom:10px}}
</style></head><body><article class="card">
<header class="top"><img src="${new URL("assets/ar-logo.png", document.baseURI).href}" alt="A.R. Waterproofing logo"><h1>A.R. Waterproofing</h1><p>Built on Trust. Protected for Life.</p></header>
<section class="body"><span class="eyebrow">DIGITAL COMPANY PROFILE</span><h2>C. Rajendran</h2><p>Founder · A.R. Waterproofing</p>
<div class="details">
<span class="label">Mobile</span><span class="value">+91 98410 44994</span>
<span class="label">Alternate contact</span><span class="value">+91 86800 70461</span>
<span class="label">Email</span><span class="value">rgn.1972@gmail.com</span>
<span class="label">Address</span><span class="value">9/3, Pushpa Nagar Main Road, Nungambakkam, Chennai 600 034, Tamil Nadu</span>
<span class="label">Services</span><span class="value">Waterproofing, grouting, construction works, structural repairs, renovation and related building solutions.</span>
<span class="label">Website</span><span class="value">arwaterproofing.com/Rajendran/</span>
</div>
<a class="cta" href="tel:+919841044994">Call Rajendran</a><a class="cta" href="https://wa.me/919841044994">WhatsApp</a>
<p class="note">Keep this profile for A.R. Waterproofing contact details. Created from the company’s digital profile.</p>
</section></article></body></html>`;
    downloadBlob("AR-Waterproofing-Rajendran-Profile.html", profileHtml, "text/html;charset=utf-8");
    showToast("Company profile downloaded.");
  }

  if (downloadProfileButton) downloadProfileButton.addEventListener("click", downloadCompanyProfile);
  if (bottomDownloadProfile && downloadProfileButton) {
    bottomDownloadProfile.addEventListener("click", () => downloadProfileButton.click());
  }

  // Service cards open a detail dialog with WhatsApp and callback options.
  const serviceDialog = document.getElementById("serviceDialog");
  const serviceDialogImage = document.getElementById("serviceDialogImage");
  const serviceDialogTitle = document.getElementById("serviceDialogTitle");
  const serviceDialogDescription = document.getElementById("serviceDialogDescription");
  const serviceWhatsAppLink = document.getElementById("serviceWhatsAppLink");

  let selectedService = "";

  document.querySelectorAll(".service-card").forEach((card) => {
    card.setAttribute("tabindex", "0");
    card.setAttribute("role", "button");
    const titleEl = card.querySelector("h3");
    const descEl = card.querySelector("p");
    const imageEl = card.querySelector("img");
    const openService = () => {
      if (!serviceDialog || !titleEl) return;
      selectedService = titleEl.textContent.trim();
      serviceDialogTitle.textContent = selectedService;
      serviceDialogDescription.textContent = descEl ? descEl.textContent.trim() : "Contact A.R. Waterproofing to discuss this service and your site requirements.";
      if (serviceDialogImage && imageEl) {
        serviceDialogImage.src = imageEl.currentSrc || imageEl.src;
        serviceDialogImage.alt = imageEl.alt || selectedService;
      }
      if (serviceWhatsAppLink) {
        const message = "Hello A.R. Waterproofing, I would like to enquire about " + selectedService + ". Please contact me about my site requirement.";
        serviceWhatsAppLink.href = "https://wa.me/919841044994?text=" + encodeURIComponent(message);
      }
      if (typeof serviceDialog.showModal === "function") serviceDialog.showModal();
    };
    card.addEventListener("click", openService);
    card.addEventListener("keydown", (event) => {
      if (event.key === "Enter" || event.key === " ") {
        event.preventDefault();
        openService();
      }
    });
  });


  // Project cards open a photo viewer with previous/next navigation.
  const galleryDialog = document.getElementById("galleryDialog");
  const galleryImage = document.getElementById("galleryDialogImage");
  const galleryTitle = document.getElementById("galleryDialogTitle");
  const galleryCategory = document.getElementById("galleryDialogCategory");
  const projectCards = Array.from(document.querySelectorAll(".project-card"));
  let activeProjectIndex = 0;

  function showProject(index) {
    if (!projectCards.length || !galleryImage) return;
    activeProjectIndex = (index + projectCards.length) % projectCards.length;
    const card = projectCards[activeProjectIndex];
    const img = card.querySelector("img");
    const title = card.querySelector("h3");
    const category = card.querySelector(".project-overlay span");
    galleryImage.src = img ? (img.currentSrc || img.src) : "";
    galleryImage.alt = img ? img.alt : "A.R. Waterproofing project";
    if (galleryTitle) galleryTitle.textContent = title ? title.textContent.trim() : "Project showcase";
    if (galleryCategory) galleryCategory.textContent = category ? category.textContent.trim() : "";
  }

  projectCards.forEach((card, index) => {
    card.setAttribute("tabindex", "0");
    card.setAttribute("role", "button");
    const openProject = () => {
      showProject(index);
      if (galleryDialog && typeof galleryDialog.showModal === "function") galleryDialog.showModal();
    };
    card.addEventListener("click", openProject);
    card.addEventListener("keydown", (event) => {
      if (event.key === "Enter" || event.key === " ") {
        event.preventDefault();
        openProject();
      }
    });
  });

  const galleryPrev = document.querySelector(".gallery-prev");
  const galleryNext = document.querySelector(".gallery-next");
  if (galleryPrev) galleryPrev.addEventListener("click", () => showProject(activeProjectIndex - 1));
  if (galleryNext) galleryNext.addEventListener("click", () => showProject(activeProjectIndex + 1));

  document.querySelectorAll("[data-close-dialog]").forEach((button) => {
    button.addEventListener("click", () => {
      const dialog = document.getElementById(button.getAttribute("data-close-dialog"));
      if (dialog && typeof dialog.close === "function") dialog.close();
    });
  });

  // Close dialogs with Escape is native; clicking the backdrop also closes them.
  [serviceDialog, galleryDialog].forEach((dialog) => {
    if (!dialog) return;
    dialog.addEventListener("click", (event) => {
      if (event.target === dialog) dialog.close();
    });
  });

  // Subtle scroll reveals and count-up statistics, respecting reduced-motion preferences.
  const reduceMotion = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  if (!reduceMotion && "IntersectionObserver" in window) {
    const revealObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add("is-visible");
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12 });
    document.querySelectorAll(".section-heading, .service-card, .property-card, .about-image, .about-copy, .project-card, .why-grid article, .review-callout, .contact-card").forEach((element, index) => {
      element.classList.add("js-reveal");
      element.style.transitionDelay = (index % 4) * 65 + "ms";
      revealObserver.observe(element);
    });
  }

  const counters = document.querySelectorAll(".count-up");
  const animateCounter = (element) => {
    if (element.dataset.animated === "true") return;
    element.dataset.animated = "true";
    const target = Number(element.dataset.count || "0");
    const suffix = element.dataset.suffix || "";
    const duration = 1300;
    const startTime = performance.now();
    const frame = (now) => {
      const progress = Math.min((now - startTime) / duration, 1);
      const eased = 1 - Math.pow(1 - progress, 3);
      const value = Math.round(target * eased);
      element.textContent = value.toLocaleString("en-IN") + (progress === 1 ? suffix : "");
      if (progress < 1) requestAnimationFrame(frame);
      else element.style.animation = "count-pop .35s ease both";
    };
    requestAnimationFrame(frame);
  };
  if (reduceMotion) {
    counters.forEach((element) => {
      element.textContent = Number(element.dataset.count || "0").toLocaleString("en-IN") + (element.dataset.suffix || "");
    });
  } else if ("IntersectionObserver" in window) {
    const counterObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          animateCounter(entry.target);
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.5 });
    counters.forEach((element) => counterObserver.observe(element));
  } else {
    counters.forEach(animateCounter);
  }

  // Show the supplied-logo loading screen briefly on each app launch.
  const appSplash = document.getElementById("appSplash");
  if (appSplash) {
    const dismissSplash = () => {
      window.setTimeout(() => {
        appSplash.classList.add("is-hidden");
        window.setTimeout(() => appSplash.remove(), 450);
      }, 550);
    };
    if (document.readyState === "complete") dismissSplash();
    else window.addEventListener("load", dismissSplash, { once: true });
    window.setTimeout(dismissSplash, 2200);
  }

})();
