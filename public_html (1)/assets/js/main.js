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

        //>> Sidebar Toggle Js Start <<//
        $(".offcanvas__close,.offcanvas__overlay").on("click", function() {
            $(".offcanvas__info").removeClass("info-open");
            $(".offcanvas__overlay").removeClass("overlay-open");
        });
        $(".sidebar__toggle").on("click", function() {
            $(".offcanvas__info").addClass("info-open");
            $(".offcanvas__overlay").addClass("overlay-open");
        });
        // Auto-close mobile drawer when navigating to in-page hash links
        $(".offcanvas__info a").on("click", function() {
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
        
        //>> Counterup Start <<//
        $(".count").counterUp({
            delay: 15,
            time: 4000,
        });

        //>> Wow Animation Start <<//
        new WOW().init();

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

        //>> CountDown Start <<//
        let targetDate = new Date("2024-07-12 00:00:00").getTime();
        const countdownInterval = setInterval(function () {
            let currentDate = new Date().getTime();
            let remainingTime = targetDate - currentDate;

            if (remainingTime <= 0) {
                clearInterval(countdownInterval);
                // Display a message or perform any action when the countdown timer reaches zero
                $("#countdown-container").text("Countdown has ended!");
            } else {
                let days = Math.floor(remainingTime / (1000 * 60 * 60 * 24));
                let hours = Math.floor(
                    (remainingTime % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)
                );
                let minutes = Math.floor(
                    (remainingTime % (1000 * 60 * 60)) / (1000 * 60)
                );
                let seconds = Math.floor((remainingTime % (1000 * 60)) / 1000);

                // Pad single-digit values with leading zeros
                $("#day").text(days.toString().padStart(2, "0"));
                $("#hour").text(hours.toString().padStart(2, "0"));
                $("#min").text(minutes.toString().padStart(2, "0"));
                $("#sec").text(seconds.toString().padStart(2, "0"));
            }
        }, 1000);
        

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

        //>> Interactive Challenges Cards Flip/Highlight <<//
        $(".vg-challenge-card").on("click", function() {
            const $card = $(this);
            const isCurrentlyActive = $card.hasClass("active-solution");
            $(".vg-challenge-card").removeClass("active-solution");
            $(".vg-challenge-card .vg-solution-tag").html('<i class="fa-solid fa-sparkles text-warning me-1"></i> AI Fix: Click to Reveal');
            if (!isCurrentlyActive) {
                $card.addClass("active-solution");
                $card.find(".vg-solution-tag").html('<i class="fa-solid fa-circle-check text-success me-1"></i> AI Fix Active');
            }
        });

        //>> Smooth Anchor Scroll with Sticky Header Clearance <<//
        $('a[href^="#"]').on("click", function(e) {
            const href = this.getAttribute("href");
            if (href && href.startsWith("#") && href.length > 1) {
                const target = $(href);
                if (target.length) {
                    e.preventDefault();
                    $("html, body").stop().animate({
                        scrollTop: target.offset().top - 100
                    }, 700, "swing");
                }
            }
        });

        //>> Flying Falcon Interactive Flight & Physics <<//
        const $falcon = $("#vg-flying-falcon");
        const $hero = $(".vg-hero-section");
        let falconX = 80, falconY = 110;
        let targetX = 80, targetY = 110;
        let isHeroHovered = false;

        if ($falcon.length && $hero.length) {
            $hero.on("mousemove", function(e) {
                const heroOffset = $hero.offset();
                targetX = e.pageX - heroOffset.left - 60;
                targetY = e.pageY - heroOffset.top - 45;

                if (!isHeroHovered) {
                    isHeroHovered = true;
                    $falcon.removeClass("autonomous-flight").addClass("interactive-flight");
                }
            });

            $hero.on("mouseleave", function() {
                isHeroHovered = false;
                $falcon.removeClass("interactive-flight").addClass("autonomous-flight");
                $falcon.css("transform", "");
            });

            function updateFalconFlight() {
                if (isHeroHovered) {
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
                }
                requestAnimationFrame(updateFalconFlight);
            }
            requestAnimationFrame(updateFalconFlight);

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
        let activeTechDomain = "ai-automation";

        const idePresets = {
            "ai-automation": {
                spec: "Autonomous AI Agents • LLM Workflows • Bots",
                stack: "OpenAI • Python • Node.js • WhatsApp API"
            },
            "performance-marketing": {
                spec: "Multi-Channel Paid Ads • Algorithmic ROAS",
                stack: "Google Ads • Meta Ads • GA4 • Semrush"
            },
            "web-engineering": {
                spec: "High-Performance Web Apps • Custom E-Commerce",
                stack: "React • Node.js • Laravel • Cloudflare"
            },
            "lead-funnels": {
                spec: "Omni-Channel Lead Capture • Instant CRM Sync",
                stack: "WhatsApp API • HubSpot • Zapier • Meta Ads"
            },
            "roi-attribution": {
                spec: "Closed-Loop Revenue Tracking • Predictive LTV",
                stack: "GA4 • Server-Side CAPI • Python Analytics"
            }
        };

        function applyTechDomain(domain) {
            if (!domain) return;
            $techCards.each(function() {
                const cats = ($(this).attr("data-category") || "").split(" ");
                if (cats.indexOf(domain) !== -1) {
                    $(this).removeClass("is-dimmed-tech").addClass("is-active-tech");
                } else {
                    $(this).removeClass("is-active-tech").addClass("is-dimmed-tech");
                }
            });

            const preset = idePresets[domain];
            if (preset) {
                const $spec = $("#ide-spec");
                const $stack = $("#ide-stack");
                if ($spec.length) $spec.text('"' + preset.spec + '"');
                if ($stack.length) $stack.text('"' + preset.stack + '"');
            }
        }

        if ($techPills.length && $techCards.length) {
            // Apply default active domain on page load
            applyTechDomain(activeTechDomain);

            // Hover preview: temporarily illuminate domain tech stack
            $techPills.on("mouseenter", function() {
                const domain = $(this).attr("data-domain");
                if (domain) {
                    $techPills.removeClass("hover-preview");
                    $(this).addClass("hover-preview");
                    applyTechDomain(domain);
                }
            }).on("mouseleave", function() {
                $techPills.removeClass("hover-preview");
                applyTechDomain(activeTechDomain);
            });

            // Click: permanently activate selected domain tab
            $techPills.on("click", function() {
                const domain = $(this).attr("data-domain");
                if (domain) {
                    activeTechDomain = domain;
                    $techPills.removeClass("active");
                    $(this).addClass("active");
                    applyTechDomain(activeTechDomain);
                }
            });
        }

        //>> Magnetic CTA Buttons <<//
        $(".vg-btn-primary, .vg-btn-glass, .vg-btn-whatsapp").addClass("vg-btn-magnetic").on("mousemove", function(e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left - rect.width / 2;
            const y = e.clientY - rect.top - rect.height / 2;
            $(this).css("transform", "translate(" + (x * 0.2) + "px, " + (y * 0.25) + "px)");
        }).on("mouseleave", function() {
            $(this).css("transform", "translate(0px, 0px)");
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
                $(".vg-service-card").fadeIn(300);
            } else {
                $(".vg-service-card").hide();
                $('.vg-service-card[data-category="' + filter + '"]').fadeIn(300);
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
            // Animate loader off screen
            $(".preloader").addClass('loaded');                    
            $(".preloader").delay(600).fadeOut();                       
        });
    }

    loader();
   

})(jQuery); // End jQuery

