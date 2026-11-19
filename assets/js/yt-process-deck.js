/**
 * YouTube SEO Process - 3D Stacked Frosted-Glass Card Deck
 * VG Digital Falcon - Executive 3D Interactive UI Component
 */

(function () {
    'use strict';

    function initYtProcessDeck() {
        var deckWrap = document.getElementById('vgYtProcessDeck');
        if (!deckWrap) return;

        var cards = Array.from(deckWrap.querySelectorAll('.vg-yt-deck-card'));
        var tabs = Array.from(deckWrap.querySelectorAll('.vg-yt-deck-tab'));
        var prevBtn = document.getElementById('vgYtDeckBtnPrev');
        var nextBtn = document.getElementById('vgYtDeckBtnNext');
        var playPauseBtn = document.getElementById('vgYtDeckBtnPlayPause');
        var counterEl = document.getElementById('vgYtDeckActiveNum');
        var progressEl = document.getElementById('vgYtDeckProgressBar');
        var viewport = document.getElementById('vgYtDeckViewport');

        if (!cards.length) return;

        var totalSteps = cards.length;
        var activeIndex = 0;
        var autoCycleInterval = null;
        var isPlaying = true;
        var autoCycleDelay = 5500; // 5.5s per slide
        var isHovered = false;
        var isShuffling = false;

        // Position cards in 3D Stack (Strict Zero-Overlap Architecture)
        function updateDeck(newIndex, direction) {
            if (isShuffling) return;
            isShuffling = true;

            var oldIndex = activeIndex;
            activeIndex = (newIndex + totalSteps) % totalSteps;

            // Update Chapter Tabs
            tabs.forEach(function (tab, idx) {
                var isActive = (idx === activeIndex);
                tab.classList.toggle('active', isActive);
                tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });

            // Update Counter & Progress
            if (counterEl) {
                counterEl.textContent = '0' + (activeIndex + 1);
            }
            if (progressEl) {
                var progressPercent = ((activeIndex + 1) / totalSteps) * 100;
                progressEl.style.width = progressPercent + '%';
            }

            // Shuffle Animation: Depart outgoing card forward/up briefly if moving forward
            var outgoingCard = cards[oldIndex];
            if (direction === 'next' && outgoingCard && oldIndex !== activeIndex) {
                outgoingCard.classList.add('is-departing');
            }

            // Arrange all cards in 3D perspective depth with strict content isolation
            cards.forEach(function (card, idx) {
                var offset = (idx - activeIndex + totalSteps) % totalSteps;
                card.classList.remove('is-active', 'is-peek-1', 'is-peek-2', 'is-hidden-depth');

                if (offset === 0) {
                    // Front Active Card: Solid, crisp, fully visible
                    card.classList.add('is-active');
                    card.style.display = 'block';
                    card.style.zIndex = '20';
                    card.style.opacity = '1';
                    card.style.transform = 'translate3d(0, 0, 0) scale(1)';
                    card.style.pointerEvents = 'auto';
                    card.setAttribute('aria-hidden', 'false');
                } else if (offset === 1) {
                    // Peek Behind 1: Stacked layer rim only (text hidden by CSS)
                    card.classList.add('is-peek-1');
                    card.style.display = 'block';
                    card.style.zIndex = '10';
                    card.style.opacity = '1';
                    card.style.transform = 'translate3d(0, 18px, -45px) scale(0.96)';
                    card.style.pointerEvents = 'auto'; // allow click to jump to next
                    card.setAttribute('aria-hidden', 'true');
                } else if (offset === 2) {
                    // Peek Behind 2: Deeper stacked layer rim only (text hidden by CSS)
                    card.classList.add('is-peek-2');
                    card.style.display = 'block';
                    card.style.zIndex = '5';
                    card.style.opacity = '1';
                    card.style.transform = 'translate3d(0, 34px, -90px) scale(0.92)';
                    card.style.pointerEvents = 'none';
                    card.setAttribute('aria-hidden', 'true');
                } else {
                    // Hidden back of deck: completely hidden from DOM rendering
                    card.classList.add('is-hidden-depth');
                    card.style.display = 'none';
                    card.style.zIndex = '1';
                    card.style.opacity = '0';
                    card.style.transform = 'translate3d(0, 48px, -140px) scale(0.88)';
                    card.style.pointerEvents = 'none';
                    card.setAttribute('aria-hidden', 'true');
                }
            });

            setTimeout(function () {
                if (outgoingCard) outgoingCard.classList.remove('is-departing');
                isShuffling = false;
            }, 450);
        }

        function nextStep() {
            updateDeck(activeIndex + 1, 'next');
        }

        function prevStep() {
            updateDeck(activeIndex - 1, 'prev');
        }

        // Auto Cycle Engine
        function startAutoCycle() {
            stopAutoCycle();
            if (!isPlaying) return;
            autoCycleInterval = setInterval(function () {
                if (!isHovered && isPlaying) {
                    nextStep();
                }
            }, autoCycleDelay);
        }

        function stopAutoCycle() {
            if (autoCycleInterval) {
                clearInterval(autoCycleInterval);
                autoCycleInterval = null;
            }
        }

        function resetAutoCycle() {
            stopAutoCycle();
            startAutoCycle();
        }

        // Tab click listeners
        tabs.forEach(function (tab) {
            tab.addEventListener('click', function (e) {
                e.preventDefault();
                var step = parseInt(tab.getAttribute('data-step'), 10);
                if (!isNaN(step) && step !== activeIndex) {
                    var dir = (step > activeIndex) ? 'next' : 'prev';
                    updateDeck(step, dir);
                    resetAutoCycle();
                }
            });
        });

        // Prev / Next button listeners
        if (prevBtn) {
            prevBtn.addEventListener('click', function (e) {
                e.preventDefault();
                prevStep();
                resetAutoCycle();
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function (e) {
                e.preventDefault();
                nextStep();
                resetAutoCycle();
            });
        }

        // Play / Pause button
        if (playPauseBtn) {
            playPauseBtn.addEventListener('click', function (e) {
                e.preventDefault();
                isPlaying = !isPlaying;
                var icon = playPauseBtn.querySelector('i');
                if (isPlaying) {
                    if (icon) {
                        icon.classList.remove('fa-play');
                        icon.classList.add('fa-pause');
                    }
                    playPauseBtn.setAttribute('aria-label', 'Pause Auto-Play');
                    startAutoCycle();
                } else {
                    if (icon) {
                        icon.classList.remove('fa-pause');
                        icon.classList.add('fa-play');
                    }
                    playPauseBtn.setAttribute('aria-label', 'Resume Auto-Play');
                    stopAutoCycle();
                }
            });
        }

        // Click on Peek Behind 1 to advance
        cards.forEach(function (card, idx) {
            card.addEventListener('click', function (e) {
                if (card.classList.contains('is-peek-1')) {
                    e.preventDefault();
                    nextStep();
                    resetAutoCycle();
                }
            });
        });

        // Pause on Hover
        deckWrap.addEventListener('mouseenter', function () {
            isHovered = true;
        });

        deckWrap.addEventListener('mouseleave', function () {
            isHovered = false;
        });

        // Cursor-Reactive 3D Tilt on Active Card
        if (viewport) {
            viewport.addEventListener('mousemove', function (e) {
                var activeCard = deckWrap.querySelector('.vg-yt-deck-card.is-active');
                if (!activeCard) return;

                var rect = viewport.getBoundingClientRect();
                var x = e.clientX - rect.left;
                var y = e.clientY - rect.top;
                var centerX = rect.width / 2;
                var centerY = rect.height / 2;

                // Subtle tilt (-5deg to +5deg)
                var tiltX = ((y - centerY) / centerY) * -5.5;
                var tiltY = ((x - centerX) / centerX) * 5.5;

                activeCard.style.transform = 'translate3d(0, 0, 10px) rotateX(' + tiltX.toFixed(2) + 'deg) rotateY(' + tiltY.toFixed(2) + 'deg)';

                // Set spotlight coordinates on active card
                activeCard.style.setProperty('--card-mx', x + 'px');
                activeCard.style.setProperty('--card-my', y + 'px');
            }, { passive: true });

            viewport.addEventListener('mouseleave', function () {
                var activeCard = deckWrap.querySelector('.vg-yt-deck-card.is-active');
                if (activeCard) {
                    activeCard.style.transform = 'translate3d(0, 0, 0) rotateX(0deg) rotateY(0deg)';
                }
            }, { passive: true });
        }

        // Mobile Touch Swipe Navigation
        var touchStartX = 0;
        var touchStartY = 0;
        deckWrap.addEventListener('touchstart', function (e) {
            touchStartX = e.changedTouches[0].screenX;
            touchStartY = e.changedTouches[0].screenY;
        }, { passive: true });

        deckWrap.addEventListener('touchend', function (e) {
            var diffX = e.changedTouches[0].screenX - touchStartX;
            var diffY = e.changedTouches[0].screenY - touchStartY;

            // Only trigger if horizontal swipe is dominant and > 45px
            if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 45) {
                if (diffX < 0) {
                    nextStep(); // Swiped left -> Next
                } else {
                    prevStep(); // Swiped right -> Prev
                }
                resetAutoCycle();
            }
        }, { passive: true });

        // Keyboard navigation (ArrowLeft / ArrowRight) when section is in viewport
        window.addEventListener('keydown', function (e) {
            var rect = deckWrap.getBoundingClientRect();
            var inView = (rect.top < window.innerHeight && rect.bottom > 0);
            if (!inView) return;

            if (e.key === 'ArrowRight') {
                nextStep();
                resetAutoCycle();
            } else if (e.key === 'ArrowLeft') {
                prevStep();
                resetAutoCycle();
            }
        });

        // Initialize First Card
        updateDeck(0, 'init');
        startAutoCycle();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initYtProcessDeck);
    } else {
        initYtProcessDeck();
    }
})();
