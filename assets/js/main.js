/*-----------------------------------------------------------------

Template Name: Web hosting & WHMCS Html Template 
Author:  Gramentheme
Author URI: https://themeforest.net/user/gramentheme/portfolio
Version: 1.0.0
Description: Web hosting & WHMCS Html Template <

-------------------------------------------------------------------
CSS TABLE OF CONTENTS
-------------------------------------------------------------------

01. header
02. animated text with swiper slider
03. magnificPopup
04. counter up
05. wow animation
06. nice select
07. swiper slider
08. search popup
09. mousecursor 
09. preloader 


------------------------------------------------------------------*/

(function($) {
    "use strict";

    $(document).ready( function() {

        //>> Mobile Menu Js Start <<//
        $('#mobile-menu').meanmenu({
            meanMenuContainer: '.mobile-menu',
            meanScreenWidth: "1199",
            meanDisplay: "flex",
            meanExpand: ['<i class="far fa-plus"></i>'],
        });

        //>> Sidebar Toggle Js Start (Click + Keyboard Accessible) <<//
        $(".offcanvas__close,.offcanvas__overlay").on("click", function() {
            $(".offcanvas__info").removeClass("info-open");
            $(".offcanvas__overlay").removeClass("overlay-open");
            $(".sidebar__toggle").trigger("focus");
        });
        $(".sidebar__toggle").attr({ "role": "button", "tabindex": "0", "aria-label": "Toggle navigation menu" }).on("click", function() {
            $(".offcanvas__info").addClass("info-open");
            $(".offcanvas__overlay").addClass("overlay-open");
            $(".offcanvas__close").trigger("focus");
        }).on("keydown", function(e) {
            if (e.key === "Enter" || e.key === " ") {
                e.preventDefault();
                $(this).trigger("click");
            }
        });

        // Close offcanvas drawer on Escape key and restore focus
        $(document).on("keydown", function(e) {
            if (e.key === "Escape" && $(".offcanvas__info").hasClass("info-open")) {
                $(".offcanvas__info").removeClass("info-open");
                $(".offcanvas__overlay").removeClass("overlay-open");
                $(".sidebar__toggle").trigger("focus");
            }
        });
        // Auto-close mobile drawer when navigating to in-page hash links (exclude dropdown toggles)
        $(".offcanvas__info a").on("click", function(e) {
            // Never auto-close drawer or refresh on dropdown parent toggle or dummy links
            if ($(this).hasClass("vg-mobile-parent-link") || $(this).closest(".vg-mobile-parent-link").length || $(this).attr("href") === "javascript:void(0)" || $(this).attr("href") === "#") {
                return;
            }
            if (!$(this).hasClass("mean-expand")) {
                $(".offcanvas__info").removeClass("info-open");
                $(".offcanvas__overlay").removeClass("overlay-open");
            }
        });

        //>> Body Overlay Js Start <<//
        $(".body-overlay").on("click", function() {
            $(".offcanvas__area").removeClass("offcanvas-opened");
            $(".df-search-area").removeClass("opened");;
            $(".body-overlay").removeClass("opened");
        });

        //>> Sticky Header handled in unified optimized scroll listener below <<//

        //>> Video Popup Start <<//
        $(".img-popup").magnificPopup({
            type: "image",
            gallery: {
                enabled: true,
            },
        });

        $('.video-popup').magnificPopup({
            type: 'iframe',
            callbacks: {
            }
        });
        
        //>> Counterup Start (Fast, lively count-up) <<//
        $(".count").counterUp({
            delay: 15,
            time: 1200,
        });

        //>> Wow Animation Start (Instant on-scroll trigger & zero polling) <<//
        new WOW({
            boxClass: 'wow',
            animateClass: 'animated',
            offset: 60,
            mobile: true,
            live: false
        }).init();

        //>> Nice Select Start <<//
        $('select').niceSelect();

        //>> Brand Slider Start <<//
        if($('.brand-slider').length > 0) {
            const brandSlider = new Swiper(".brand-slider", {
                spaceBetween: 30,
                speed: 2000,
                loop: true,
                autoplay: {
                    delay: 1000,
                    disableOnInteraction: false,
                },
                breakpoints: {
                    1199: {
                        slidesPerView: 6,
                    },
                    991: {
                        slidesPerView: 5,
                    },
                    767: {
                        slidesPerView: 4,
                    },
                    575: {
                        slidesPerView: 3,
                    },
                    400: {
                        slidesPerView: 2,
                    },
                    0: {
                        slidesPerView: 1,
                    },
                },
            });
        }

        if($('.brand-slider-2').length > 0) {
            const brandSlider2 = new Swiper(".brand-slider-2", {
                spaceBetween: 20,
                speed: 2000,
                loop: true,
                autoplay: {
                    delay: 1000,
                    disableOnInteraction: false,
                },
                breakpoints: {
                    1199: {
                        slidesPerView: 5,
                    },
                    991: {
                        slidesPerView: 4,
                    },
                    767: {
                        slidesPerView: 3,
                    },
                    575: {
                        slidesPerView: 3,
                    },
                    400: {
                        slidesPerView: 2,
                    },
                    0: {
                        slidesPerView: 1,
                    },
                },
            });
        }

        //>> Testimonial Slider Start <<//
        if($('.testimonial-slider').length > 0) {
            const testimonialSlider = new Swiper(".testimonial-slider", {
                spaceBetween: 20,
                speed: 3000,
                loop: true,
                centeredSlides: true,
                autoplay: {
                    delay: 2000,
                    disableOnInteraction: false,
                },
                pagination: {
                    el: ".dot",
                    clickable: true,
                },
                navigation: {
                    nextEl: ".array-prev",
                    prevEl: ".array-next",
                },
                breakpoints: {
                    1199: {
                        slidesPerView: 3,
                    },
                    991: {
                        slidesPerView: 2,
                    },
                    767: {
                        slidesPerView: 2,
                    },
                    575: {
                        slidesPerView: 1,
                    },
                    0: {
                        slidesPerView: 1,
                    },
                },
            });
        }

        if($('.testimonial-slider-2').length > 0) {
            const testimonialSlider2 = new Swiper(".testimonial-slider-2", {
                spaceBetween: 30,
                speed: 2000,
                loop: true,
                autoplay: {
                    delay: 1000,
                    disableOnInteraction: false,
                },
                navigation: {
                    nextEl: ".array-prev",
                    prevEl: ".array-next",
                },
            });
        }

        //>> Team Slider Start <<//
        if($('.team-slider').length > 0) {
            const teamSlider = new Swiper(".team-slider", {
                spaceBetween: 30,
                speed: 3000,
                loop: true,
                autoplay: {
                    delay: 2000,
                    disableOnInteraction: false,
                },
                navigation: {
                    nextEl: ".array-prev",
                    prevEl: ".array-next",
                },
                breakpoints: {
                    1199: {
                        slidesPerView: 4,
                    },
                    991: {
                        slidesPerView: 3,
                    },
                    767: {
                        slidesPerView: 2,
                    },
                    575: {
                        slidesPerView: 1,
                    },
                    0: {
                        slidesPerView: 1,
                    },
                },
            });
        }


        

        //>> Search Popup Start <<//
        const $searchWrap = $(".search-wrap");
        const $navSearch = $(".nav-search");
        const $searchClose = $("#search-close");

        $(".search-trigger").on("click", function (e) {
            e.preventDefault();
            $searchWrap.animate({ opacity: "toggle" }, 500);
            $navSearch.add($searchClose).addClass("open");
        });

        $(".search-close").on("click", function (e) {
            e.preventDefault();
            $searchWrap.animate({ opacity: "toggle" }, 500);
            $navSearch.add($searchClose).removeClass("open");
        });

        function closeSearch() {
            $searchWrap.fadeOut(200);
            $navSearch.add($searchClose).removeClass("open");
        }

        $(document.body).on("click", function (e) {
            closeSearch();
        });

        $(".search-trigger, .main-search-input").on("click", function (e) {
            e.stopPropagation();
        });

         //>> Normal Standard Browser Cursor Active (Custom Mouse Cursor Disabled) <<//
         function mousecursor() {
             // Standard browser cursor preserved for optimal precision & smooth interaction
         }
         $(function() {
             // mousecursor disabled
         });


        //>> Optimized Unified Smooth Scroll Listener (Zero Layout Thrash & 60-120fps) <<//
        let scrollTicking = false;
        let cachedDocHeight = 1;
        function updateDocHeight() {
            cachedDocHeight = Math.max(document.documentElement.scrollHeight - window.innerHeight, 1);
        }
        updateDocHeight();
        window.addEventListener("resize", updateDocHeight, { passive: true });

        window.addEventListener("scroll", function() {
            if (!scrollTicking) {
                window.requestAnimationFrame(function() {
                    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                    const progress = (scrollTop / cachedDocHeight) * 100;
                    
                    const progressBar = document.getElementById("scroll-progress-bar");
                    if (progressBar) {
                        progressBar.style.width = Math.min(progress, 100) + "%";
                    }

                    const stickyHeader = document.getElementById("header-sticky");
                    if (stickyHeader) {
                        if (scrollTop > 250) stickyHeader.classList.add("sticky");
                        else stickyHeader.classList.remove("sticky");
                    }

                    const vgHeader = document.querySelector(".vg-header");
                    if (vgHeader) {
                        if (scrollTop > 80) vgHeader.classList.add("header-scrolled");
                        else vgHeader.classList.remove("header-scrolled");
                    }

                    const mascot = document.getElementById("vg-global-mascot");
                    if (mascot) {
                        if (scrollTop > 350) mascot.classList.add("mascot-visible");
                        else mascot.classList.remove("mascot-visible");
                    }
                    scrollTicking = false;
                });
                scrollTicking = true;
            }
        }, { passive: true });

        //>> Smooth Anchor Navigation with Proper Fixed Header Offset <<//
        $(document).on("click", 'a[href^="#"]:not([href="#"]):not([data-bs-toggle])', function(e) {
            const targetId = $(this).attr("href");
            if (!targetId || targetId === "#") return;
            const target = $(targetId);
            if (target.length) {
                e.preventDefault();
                const offset = 85;
                const targetPosition = target.offset().top - offset;
                window.scrollTo({
                    top: targetPosition,
                    behavior: "smooth"
                });
                if (window.history && window.history.pushState) {
                    window.history.pushState(null, null, targetId);
                }
            }
        });

        //>> AI Growth Engine Interactive Nodes <<//
        $(".vg-node").on("mouseenter", function() {
            const targetBeam = $(this).data("beam");
            if (targetBeam) {
                $("#" + targetBeam).css({
                    "stroke": "#f472b6",
                    "stroke-width": "3px",
                    "filter": "drop-shadow(0 0 10px #f472b6)"
                });
            }
            $(".vg-engine-core").css({
                "box-shadow": "0 0 65px rgba(244, 114, 182, 0.8), inset 0 0 35px rgba(168, 85, 247, 0.6)",
                "border-color": "#f472b6"
            });
        }).on("mouseleave", function() {
            const targetBeam = $(this).data("beam");
            if (targetBeam) {
                $("#" + targetBeam).css({
                    "stroke": "rgba(168, 85, 247, 0.45)",
                    "stroke-width": "1.5px",
                    "filter": "none"
                });
            }
            $(".vg-engine-core").css({
                "box-shadow": "0 0 40px rgba(147, 51, 234, 0.65), inset 0 0 25px rgba(168, 85, 247, 0.35)",
                "border-color": "rgba(192, 132, 252, 0.6)"
            });
        });

        //>> Interactive Challenges Cards Flip/Highlight with Mobile Auto-Collapse <<//
        let challengeMobileTimer = null;

        function closeAllChallengeCards() {
            if (challengeMobileTimer) {
                clearTimeout(challengeMobileTimer);
                challengeMobileTimer = null;
            }
            const $activeCards = $(".vg-challenge-card.active-solution");
            if ($activeCards.length) {
                $activeCards.removeClass("active-solution");
                $activeCards.find(".vg-solution-tag").html('<i class="fa-solid fa-sparkles text-warning me-1"></i> AI Fix: Click to Reveal');
            }
        }

        $(".vg-challenge-card").on("click", function(e) {
            // Prevent event from bubbling to document tap-outside listener
            e.stopPropagation();

            const $card = $(this);
            const isCurrentlyActive = $card.hasClass("active-solution");
            const isMobile = window.innerWidth < 768;

            // Clear any active mobile auto-close timer
            if (challengeMobileTimer) {
                clearTimeout(challengeMobileTimer);
                challengeMobileTimer = null;
            }

            if (isCurrentlyActive) {
                // If already active, toggle off immediately
                $card.removeClass("active-solution");
                $card.find(".vg-solution-tag").html('<i class="fa-solid fa-sparkles text-warning me-1"></i> AI Fix: Click to Reveal');
                $card.blur();
            } else {
                // Accordion behavior: close any other open challenge cards
                $(".vg-challenge-card").removeClass("active-solution");
                $(".vg-challenge-card .vg-solution-tag").html('<i class="fa-solid fa-sparkles text-warning me-1"></i> AI Fix: Click to Reveal');

                // Activate the selected card
                $card.addClass("active-solution");
                $card.find(".vg-solution-tag").html('<i class="fa-solid fa-circle-check text-success me-1"></i> AI Fix Active');

                // Mobile view only (< 768px): automatically collapse after 4.5 seconds
                if (isMobile) {
                    challengeMobileTimer = setTimeout(function() {
                        if ($card.hasClass("active-solution")) {
                            $card.removeClass("active-solution");
                            $card.find(".vg-solution-tag").html('<i class="fa-solid fa-sparkles text-warning me-1"></i> AI Fix: Click to Reveal');
                        }
                        challengeMobileTimer = null;
                    }, 4500);
                }
            }
        });

        // Mobile view (< 768px): automatically close open challenge card when tapping outside
        $(document).on("click", function(e) {
            if (window.innerWidth < 768) {
                if (!$(e.target).closest(".vg-challenge-card").length) {
                    closeAllChallengeCards();
                }
            }
        });

        //>> Flying Falcon Interactive Flight & Physics (Optimized RAF Lifecycle) <<//
        const $falcon = $("#vg-flying-falcon");
        const $hero = $(".vg-hero-section");
        let falconX = 80, falconY = 110;
        let targetX = 80, targetY = 110;
        let isHeroHovered = false;
        let falconRafId = null;

        if ($falcon.length && $hero.length) {
            function updateFalconFlight() {
                if (!isHeroHovered) {
                    falconRafId = null;
                    return;
                }
                const dx = targetX - falconX;
                const dy = targetY - falconY;
                falconX += dx * 0.08;
                falconY += dy * 0.08;

                const bankAngle = Math.max(-28, Math.min(28, dx * 0.18));
                $falcon.css({
                    "left": falconX + "px",
                    "top": falconY + "px",
                    "transform": "rotate(" + bankAngle + "deg)"
                });

                falconRafId = requestAnimationFrame(updateFalconFlight);
            }

            function startFalconFlight() {
                if (!falconRafId) {
                    falconRafId = requestAnimationFrame(updateFalconFlight);
                }
            }

            function stopFalconFlight() {
                if (falconRafId) {
                    cancelAnimationFrame(falconRafId);
                    falconRafId = null;
                }
            }

            $hero.on("mousemove", function(e) {
                const heroOffset = $hero.offset();
                targetX = e.pageX - heroOffset.left - 60;
                targetY = e.pageY - heroOffset.top - 45;

                if (!isHeroHovered) {
                    isHeroHovered = true;
                    $falcon.removeClass("autonomous-flight").addClass("interactive-flight");
                    startFalconFlight();
                }
            });

            $hero.on("mouseleave", function() {
                isHeroHovered = false;
                $falcon.removeClass("interactive-flight").addClass("autonomous-flight");
                $falcon.css("transform", "");
                stopFalconFlight();
            });

            document.addEventListener("visibilitychange", function() {
                if (document.hidden) {
                    stopFalconFlight();
                }
            });

            // Falcon Click Sonic Boost & Burst
            $falcon.on("click", function(e) {
                e.stopPropagation();
                const $ring = $('<div class="sonic-boost-ring"></div>');
                $falcon.append($ring);
                $falcon.addClass("falcon-sonic-speed");
                setTimeout(function() {
                    $ring.remove();
                    $falcon.removeClass("falcon-sonic-speed");
                }, 850);
            });
        }

        //>> 3D Perspective Card Tilt & Holographic Sheen <<//
        $(".vg-challenge-card, .vg-service-card, .vg-case-card, .vg-team-card").addClass("vg-3d-tilt vg-holo-sheen").on("mousemove", function(e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            const rotateX = -((y - centerY) / centerY) * 8;
            const rotateY = ((x - centerX) / centerX) * 8;

            $(this).css({
                "transform": "perspective(900px) rotateX(" + rotateX + "deg) rotateY(" + rotateY + "deg) scale3d(1.025, 1.025, 1.025)",
                "--mouse-x": x + "px",
                "--mouse-y": y + "px"
            });
        }).on("mouseleave", function() {
            $(this).css({
                "transform": "perspective(900px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)"
            });
        });

        //>> Tech Stack Domain Filtering (Click & Hover Interaction) <<//
        const $techPills = $(".vg-code-discipline-pills .vg-code-pill");
        const $techCards = $(".vg-square-tech-card");
        const $codeDisplay = $("#vg-ide-code-display");
        let activeTechDomain = null; // Default is null: all tech stack icons normal!

        const defaultIdeCode = `<span class="c-comment"># AI-Powered Digital Marketing &amp; Growth Engine</span>
<span class="c-kw">class</span> <span class="c-class">VGDigitalFalcon</span>(<span class="c-parent">AIGrowthPartner</span>):
    <span class="c-prop">specialization</span>   = <span class="c-str">"AI Marketing • Performance Ads • Automation"</span>
    <span class="c-prop">core_stack</span>       = <span class="c-str">"React • Python • Node.js • Meta &amp; Google Ads"</span>
    <span class="c-prop">avg_roas</span>         = <span class="c-str">"4.8x"</span>  <span class="c-comment"># Proven client scaling</span>
    <span class="c-prop">lead_latency</span>     = <span class="c-str">"&lt; 30s"</span>  <span class="c-comment"># Automated CRM sync</span>
    <span class="c-prop">client_retention</span> = <span class="c-str">"95%"</span>

    <span class="c-kw">def</span> <span class="c-func">scale_business</span>(<span class="c-self">self</span>, <span class="c-param">client</span>):
        <span class="c-kw">return</span> <span class="c-var">predictable_revenue</span> + <span class="c-var">10x_growth</span>`;

        const ideCodePresets = {
            "ai-automation": `<span class="c-comment"># Autonomous AI Agents &amp; Intelligent Workflows</span>
<span class="c-kw">class</span> <span class="c-class">VGDigitalFalcon</span>(<span class="c-parent">AIGrowthPartner</span>):
    <span class="c-prop">specialization</span>     = <span class="c-str">"Autonomous AI Agents • LLM Workflows • Bots"</span>
    <span class="c-prop">core_stack</span>         = <span class="c-str">"OpenAI • Python • Node.js • WhatsApp API"</span>
    <span class="c-prop">lead_response</span>      = <span class="c-str">"&lt; 30s"</span>  <span class="c-comment"># 24/7 intelligent agent sync</span>
    <span class="c-prop">workflow_uptime</span>    = <span class="c-str">"99.98%"</span>  <span class="c-comment"># Self-healing microservices</span>
    <span class="c-prop">manual_hours_saved</span> = <span class="c-str">"85%"</span>

    <span class="c-kw">def</span> <span class="c-func">automate_growth</span>(<span class="c-self">self</span>, <span class="c-param">pipeline</span>):
        <span class="c-kw">return</span> <span class="c-var">autonomous_agents</span>.<span class="c-func">scale</span>(<span class="c-param">pipeline</span>)`,

            "performance-marketing": `<span class="c-comment"># Multi-Channel Paid Ads &amp; Algorithmic ROAS Engine</span>
<span class="c-kw">class</span> <span class="c-class">VGDigitalFalcon</span>(<span class="c-parent">AIGrowthPartner</span>):
    <span class="c-prop">specialization</span>     = <span class="c-str">"Multi-Channel Paid Ads • Algorithmic ROAS"</span>
    <span class="c-prop">core_stack</span>         = <span class="c-str">"Google Ads • Meta Ads • GA4 • Semrush"</span>
    <span class="c-prop">avg_roas</span>           = <span class="c-str">"4.8x - 8.6x"</span>  <span class="c-comment"># Proven client scaling</span>
    <span class="c-prop">cpa_reduction</span>      = <span class="c-str">"-40%"</span>  <span class="c-comment"># Algorithmic bidding optimization</span>
    <span class="c-prop">creative_velocity</span>  = <span class="c-str">"50+ variations/48h"</span>

    <span class="c-kw">def</span> <span class="c-func">maximize_ad_spend</span>(<span class="c-self">self</span>, <span class="c-param">campaign</span>):
        <span class="c-kw">return</span> <span class="c-var">algorithmic_bidding</span>.<span class="c-func">optimize_roas</span>(<span class="c-param">campaign</span>)`,

            "web-engineering": `<span class="c-comment"># Sub-Second Speed &amp; High-Performance Web Engineering</span>
<span class="c-kw">class</span> <span class="c-class">VGDigitalFalcon</span>(<span class="c-parent">AIGrowthPartner</span>):
    <span class="c-prop">specialization</span>     = <span class="c-str">"High-Performance Web Apps • Custom E-Commerce"</span>
    <span class="c-prop">core_stack</span>         = <span class="c-str">"React • Node.js • Laravel • Cloudflare"</span>
    <span class="c-prop">page_speed_fcp</span>     = <span class="c-str">"0.8s"</span>  <span class="c-comment"># Ultra-fast TTFB edge caching</span>
    <span class="c-prop">lighthouse_score</span>   = <span class="c-str">"98/100"</span>  <span class="c-comment"># Core Web Vitals optimized</span>
    <span class="c-prop">uptime_sla</span>         = <span class="c-str">"99.99%"</span>

    <span class="c-kw">def</span> <span class="c-func">deploy_web_engine</span>(<span class="c-self">self</span>, <span class="c-param">platform</span>):
        <span class="c-kw">return</span> <span class="c-var">zero_downtime_deploy</span>(<span class="c-param">platform</span>) + <span class="c-var">sub_second_loads</span>`,

            "lead-funnels": `<span class="c-comment"># Omni-Channel Lead Funnels &amp; Real-Time CRM Automation</span>
<span class="c-kw">class</span> <span class="c-class">VGDigitalFalcon</span>(<span class="c-parent">AIGrowthPartner</span>):
    <span class="c-prop">specialization</span>     = <span class="c-str">"Omni-Channel Lead Capture • Instant CRM Sync"</span>
    <span class="c-prop">core_stack</span>         = <span class="c-str">"WhatsApp API • HubSpot • Zapier • Meta Ads"</span>
    <span class="c-prop">funnel_conv_rate</span>   = <span class="c-str">"18.4%"</span>  <span class="c-comment"># High-intent buyer routing</span>
    <span class="c-prop">crm_sync_latency</span>   = <span class="c-str">"&lt; 15s"</span>  <span class="c-comment"># Instant webhook automation</span>
    <span class="c-prop">dropoff_reduction</span>  = <span class="c-str">"-55%"</span>

    <span class="c-kw">def</span> <span class="c-func">route_inbound_prospect</span>(<span class="c-self">self</span>, <span class="c-param">lead</span>):
        <span class="c-kw">return</span> <span class="c-var">instant_whatsapp_sync</span>(<span class="c-param">lead</span>) + <span class="c-var">direct_calendar_book</span>`,

            "roi-attribution": `<span class="c-comment"># Server-Side CAPI &amp; Closed-Loop Revenue Attribution</span>
<span class="c-kw">class</span> <span class="c-class">VGDigitalFalcon</span>(<span class="c-parent">AIGrowthPartner</span>):
    <span class="c-prop">specialization</span>     = <span class="c-str">"Closed-Loop Revenue Tracking • Predictive LTV"</span>
    <span class="c-prop">core_stack</span>         = <span class="c-str">"GA4 • Server-Side CAPI • Python Analytics"</span>
    <span class="c-prop">data_accuracy</span>      = <span class="c-str">"99.4%"</span>  <span class="c-comment"># Server-side cookieless tracking</span>
    <span class="c-prop">revenue_tracked</span>    = <span class="c-str">"₹50+ Cr"</span>  <span class="c-comment"># Real-time multi-touch GA4</span>
    <span class="c-prop">roas_visibility</span>    = <span class="c-str">"100% Live"</span>

    <span class="c-kw">def</span> <span class="c-func">verify_roi_data</span>(<span class="c-self">self</span>, <span class="c-param">journey</span>):
        <span class="c-kw">return</span> <span class="c-var">server_side_attribution</span>.<span class="c-func">audit_revenue</span>(<span class="c-param">journey</span>)`
        };

        function resetTechDomain() {
            $techCards.removeClass("is-active-tech is-dimmed-tech");
            if ($codeDisplay.length) {
                $codeDisplay.html(defaultIdeCode);
            }
        }

        function applyTechDomain(domain) {
            if (!domain) {
                resetTechDomain();
                return;
            }
            $techCards.each(function() {
                const cats = ($(this).attr("data-category") || "").split(" ");
                if (cats.indexOf(domain) !== -1) {
                    $(this).removeClass("is-dimmed-tech").addClass("is-active-tech");
                } else {
                    $(this).removeClass("is-active-tech").addClass("is-dimmed-tech");
                }
            });

            const codeHtml = ideCodePresets[domain];
            if (codeHtml && $codeDisplay.length) {
                $codeDisplay.html(codeHtml);
            }
        }

        if ($techPills.length && $techCards.length) {
            // Default state: all icons remain visible with normal opacity (no tab active)
            resetTechDomain();

            // Hover preview: temporarily highlight corresponding domain tech stack & dim remaining
            $techPills.on("mouseenter", function() {
                const domain = $(this).attr("data-domain");
                if (domain) {
                    $techPills.removeClass("hover-preview");
                    $(this).addClass("hover-preview");
                    applyTechDomain(domain);
                }
            }).on("mouseleave", function() {
                $techPills.removeClass("hover-preview");
                // If a tab was clicked, restore it; otherwise reset all icons back to normal!
                if (activeTechDomain) {
                    applyTechDomain(activeTechDomain);
                } else {
                    resetTechDomain();
                }
            });

            // Click: toggle domain tab selection on/off
            $techPills.on("click", function() {
                const domain = $(this).attr("data-domain");
                if (!domain) return;

                if (activeTechDomain === domain) {
                    // Clicking the active tab again toggles it off and resets all icons back to normal
                    activeTechDomain = null;
                    $techPills.removeClass("active hover-preview");
                    resetTechDomain();
                } else {
                    // Highlight selected domain tab and filter icons
                    activeTechDomain = domain;
                    $techPills.removeClass("active");
                    $(this).addClass("active");
                    applyTechDomain(activeTechDomain);
                }
            });
        }

        //>> Magnetic CTA Buttons (Throttled with RAF & Cached Dimensions) <<//
        $(".vg-btn-primary, .vg-btn-glass, .vg-btn-whatsapp").addClass("vg-btn-magnetic").each(function() {
            const btn = this;
            let btnRect = null;
            let btnRaf = null;

            btn.addEventListener("mouseenter", function() {
                btnRect = btn.getBoundingClientRect();
            }, { passive: true });

            btn.addEventListener("mousemove", function(e) {
                if (!btnRect) btnRect = btn.getBoundingClientRect();
                if (btnRaf) return;
                const clientX = e.clientX;
                const clientY = e.clientY;
                btnRaf = requestAnimationFrame(function() {
                    if (btnRect) {
                        const x = clientX - btnRect.left - btnRect.width / 2;
                        const y = clientY - btnRect.top - btnRect.height / 2;
                        btn.style.transform = "translate(" + (x * 0.2) + "px, " + (y * 0.25) + "px)";
                    }
                    btnRaf = null;
                });
            }, { passive: true });

            btn.addEventListener("mouseleave", function() {
                if (btnRaf) {
                    cancelAnimationFrame(btnRaf);
                    btnRaf = null;
                }
                btnRect = null;
                btn.style.transform = "translate(0px, 0px)";
            }, { passive: true });
        });

        //>> Inject Floating Cosmic Particles in Hero <<//
        if ($(".vg-cosmic-particles").length) {
            const $particlesContainer = $(".vg-cosmic-particles");
            for (let i = 0; i < 22; i++) {
                const size = Math.random() * 4 + 2;
                const top = Math.random() * 95;
                const left = Math.random() * 98;
                const duration = Math.random() * 6 + 6;
                const delay = Math.random() * 5;
                const $p = $('<div class="cosmic-particle"></div>').css({
                    "width": size + "px",
                    "height": size + "px",
                    "top": top + "%",
                    "left": left + "%",
                    "animation-duration": duration + "s",
                    "animation-delay": delay + "s"
                });
                $particlesContainer.append($p);
            }
        }

        //>> Global Interactive Flying Mascot Companion <<//
        const $globalMascot = $("#vg-global-mascot");
        const falconTips = [
            "🦅 Scale with AI: Generate 300% More Leads!",
            "⚡ Did you know? We influenced ₹50+ Crores revenue!",
            "🚀 Click me to see a loop-the-loop flight stunt!",
            "💬 Click WhatsApp below to chat with an AI growth expert!",
            "🎯 SEO + Meta Ads = Peak ROI for your business!"
        ];
        let tipIdx = 0;

        if ($globalMascot.length) {
            $globalMascot.on("click", function(e) {
                e.preventDefault();
                const $bird = $(this).find(".vg-mascot-falcon");
                $bird.addClass("is-looping");
                setTimeout(function() {
                    $bird.removeClass("is-looping");
                }, 1100);

                tipIdx = (tipIdx + 1) % falconTips.length;
                $(this).find(".vg-mascot-bubble").text(falconTips[tipIdx]);
                $(this).addClass("active-bubble");
                setTimeout(function() {
                    $globalMascot.removeClass("active-bubble");
                }, 3000);
            });
        }

        //>> Interactive AI Growth & ROI Calculator <<//
        const $budgetSlider = $("#roi-budget-slider");
        const $budgetDisplay = $("#roi-budget-val");
        const $leadsVal = $("#roi-leads-val");
        const $revenueVal = $("#roi-revenue-val");
        const $roasVal = $("#roi-roas-val");

        if ($budgetSlider.length) {
            function updateROICalculations(budget) {
                const min = Number($budgetSlider.attr("min")) || 20000;
                const max = Number($budgetSlider.attr("max")) || 500000;
                const pct = Math.max(0, Math.min(100, ((budget - min) / (max - min)) * 100));
                $budgetSlider.css("--slider-pct", pct + "%");
                $budgetDisplay.text("₹" + Number(budget).toLocaleString("en-IN") + " / month");

                const leads = Math.round(budget * 0.0028);
                const revenueLakhs = (budget * 5.4 / 100000).toFixed(1);
                const roas = (4.5 + (budget / 500000) * 2.2).toFixed(1);

                $leadsVal.text("+" + leads + " Leads");
                $revenueVal.text("₹" + revenueLakhs + " Lakhs");
                $roasVal.text(roas + "x ROAS");
            }

            $budgetSlider.on("input change", function() {
                updateROICalculations($(this).val());
            });
            updateROICalculations($budgetSlider.val());
        }

        //>> Interactive Service Filter Tabs <<//
        $(".vg-service-filter-btn").on("click", function() {
            $(".vg-service-filter-btn").removeClass("active");
            $(this).addClass("active");
            const filter = $(this).data("filter");

            if (filter === "all") {
                $(".vg-service-card").css("display", "flex").hide().fadeIn(300);
            } else {
                $(".vg-service-card").hide();
                $('.vg-service-card[data-category="' + filter + '"]').css("display", "flex").hide().fadeIn(300);
            }
        });

        //>> Animated Progress Bars in About Us <<//
        if ($(".vg-progress-fill").length) {
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        const targetWidth = $(entry.target).data("width");
                        $(entry.target).css("width", targetWidth + "%");
                    }
                });
            }, { threshold: 0.2 });

            $(".vg-progress-fill").each(function() {
                observer.observe(this);
            });
        }

    }); // End Document Ready Function

    function loader() {
        $(window).on('load', function() {
            // Animate loader off screen immediately
            $(".preloader").addClass('loaded').fadeOut(250);
        });
    }

    loader();
   

})(jQuery); // End jQuery

