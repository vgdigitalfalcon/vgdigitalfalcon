/**
 * ============================================================================
 * VG DIGITAL FALCON — 3D SOARING FALCON (HERO BACKGROUND EXCLUSIVE)
 * File: assets/js/seo-hero-falcon-3d.js
 * 
 * Features:
 * 1. 3D Falcon with spread wings floating in the hero background behind text
 * 2. Gentle continuous floating motion (approx 5.4 sec cycle) & articulated wing flexing
 * 3. Mouse cursor parallax tilt (max 10-14 degrees)
 * 4. Dual electric cyan (#38BDF8) & soft neon purple (#6C5CE7 / #A855F7) lighting
 * 5. Performance: Lazy-loaded after main content render (does NOT block LCP)
 * 6. Mobile & Reduced-Motion protection: Automatically disabled < 768px, shows static fallback
 * 7. IntersectionObserver: 0% CPU/GPU overhead when scrolled out of view
 * 8. Fallback: Gracefully falls back to static PNG if WebGL or Three.js fails
 * ============================================================================
 */

(function () {
  'use strict';

  // 1. Guard: Check if Falcon container is present on the page
  const container = document.getElementById('vg-seo-falcon-3d-wrap');
  if (!container) return;

  // 2. Guard: Screen width check (Disable on mobile/tablet < 768px for zero LCP impact)
  if (window.innerWidth < 768) {
    return; // Static fallback image remains active
  }

  // 3. Guard: Reduced motion accessibility check
  if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    return; // Respect user motion preference with static image
  }

  // 4. Guard: WebGL Support check
  function isWebGLSupported() {
    try {
      const canvas = document.createElement('canvas');
      return !!(window.WebGLRenderingContext && (canvas.getContext('webgl') || canvas.getContext('experimental-webgl')));
    } catch (e) {
      return false;
    }
  }

  if (!isWebGLSupported()) {
    return; // Graceful static fallback
  }

  // ==========================================================================
  // LAZY LOAD CONTROLLER (DO NOT BLOCK LCP)
  // ==========================================================================
  function scheduleInit() {
    if ('requestIdleCallback' in window) {
      requestIdleCallback(() => loadThreeAndStart(), { timeout: 2500 });
    } else {
      setTimeout(loadThreeAndStart, 300);
    }
  }

  if (document.readyState === 'complete') {
    scheduleInit();
  } else {
    window.addEventListener('load', scheduleInit, { once: true });
  }

  function loadThreeAndStart() {
    // Check if window.THREE is already available
    if (window.THREE) {
      initThreeFalcon();
      return;
    }

    // Load Three.js asynchronously (local file first, CDN fallback)
    const script = document.createElement('script');
    script.src = 'assets/js/three.min.js';
    script.async = true;
    script.onload = () => initThreeFalcon();
    script.onerror = () => {
      // CDN Fallback if local path fails
      const cdnScript = document.createElement('script');
      cdnScript.src = 'https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js';
      cdnScript.async = true;
      cdnScript.onload = () => initThreeFalcon();
      cdnScript.onerror = () => {
        console.warn('[VG Falcon 3D] Three.js could not be loaded. Displaying static fallback.');
      };
      document.head.appendChild(cdnScript);
    };
    document.head.appendChild(script);
  }

  // ==========================================================================
  // THREE.JS 3D FALCON IMPLEMENTATION
  // ==========================================================================
  function initThreeFalcon() {
    const THREE = window.THREE;
    if (!THREE) return;

    const hero = container.closest('.vg-seo-hero') || document.querySelector('.vg-seo-hero');
    let width = container.clientWidth || 520;
    let height = container.clientHeight || 440;

    // 1. Scene, Camera, Renderer
    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(40, width / height, 0.1, 100);
    camera.position.set(0, 0, 7.5);

    const canvas = document.createElement('canvas');
    canvas.className = 'vg-seo-falcon-canvas';
    container.appendChild(canvas);

    const renderer = new THREE.WebGLRenderer({
      canvas: canvas,
      alpha: true,
      antialias: true,
      powerPreference: 'high-performance'
    });

    const dpr = Math.min(window.devicePixelRatio || 1, 1.75);
    renderer.setPixelRatio(dpr);
    renderer.setSize(width, height);
    renderer.setClearColor(0x000000, 0);

    // 2. Lighting (Cyan & Soft Purple Theme Match)
    const ambientLight = new THREE.AmbientLight(0x100828, 1.8);
    scene.add(ambientLight);

    // Key Light: Electric Cyan (#38BDF8)
    const keyLight = new THREE.DirectionalLight(0x38bdf8, 2.4);
    keyLight.position.set(-4, 5, 5);
    scene.add(keyLight);

    // Fill Light: Soft Neon Violet (#A855F7 / #6C5CE7)
    const fillLight = new THREE.PointLight(0x6c5ce7, 3.4, 25);
    fillLight.position.set(3, -2, 4);
    scene.add(fillLight);

    // Rim/Back Light: Bright Lavender (#C084FC)
    const rimLight = new THREE.PointLight(0xc084fc, 2.6, 20);
    rimLight.position.set(0, 3, -4);
    scene.add(rimLight);

    // 3. Materials
    const bodyMat = new THREE.MeshStandardMaterial({
      color: 0x160c32,
      metalness: 0.85,
      roughness: 0.28,
      emissive: 0x221048,
      emissiveIntensity: 0.35,
      flatShading: true
    });

    const wingPrimaryMat = new THREE.MeshStandardMaterial({
      color: 0x1c0f42,
      metalness: 0.88,
      roughness: 0.22,
      emissive: 0x38bdf8,
      emissiveIntensity: 0.28,
      flatShading: true
    });

    const wingSecondaryMat = new THREE.MeshStandardMaterial({
      color: 0x241254,
      metalness: 0.82,
      roughness: 0.32,
      emissive: 0x6c5ce7,
      emissiveIntensity: 0.38,
      flatShading: true
    });

    const accentMat = new THREE.MeshStandardMaterial({
      color: 0x38bdf8,
      metalness: 0.9,
      roughness: 0.15,
      emissive: 0x00f0ff,
      emissiveIntensity: 0.85
    });

    // 4. Build 3D Falcon Hierarchy
    const falconRoot = new THREE.Group();
    scene.add(falconRoot);

    // Torso / Fuselage
    const torsoGeo = new THREE.ConeGeometry(0.55, 2.2, 7);
    torsoGeo.rotateX(Math.PI / 2);
    torsoGeo.scale(1.1, 0.6, 1.0);
    const torso = new THREE.Mesh(torsoGeo, bodyMat);
    torso.position.set(0, 0, 0);
    falconRoot.add(torso);

    // Chest plate / Sternum (Streamlined aerodynamic ridge)
    const chestGeo = new THREE.ConeGeometry(0.35, 1.4, 5);
    chestGeo.rotateX(Math.PI / 2);
    chestGeo.scale(0.8, 0.45, 0.9);
    const chest = new THREE.Mesh(chestGeo, wingSecondaryMat);
    chest.position.set(0, -0.15, 0.2);
    falconRoot.add(chest);

    // Head
    const headGroup = new THREE.Group();
    headGroup.position.set(0, 0.05, 1.15);
    falconRoot.add(headGroup);

    const headGeo = new THREE.ConeGeometry(0.38, 0.9, 6);
    headGeo.rotateX(Math.PI / 2);
    headGeo.scale(0.9, 0.7, 1.0);
    const head = new THREE.Mesh(headGeo, bodyMat);
    headGroup.add(head);

    // Beak (Curved Raptor Beak in Electric Cyan)
    const beakGeo = new THREE.ConeGeometry(0.16, 0.55, 4);
    beakGeo.rotateX(Math.PI / 2 + 0.35);
    const beak = new THREE.Mesh(beakGeo, accentMat);
    beak.position.set(0, -0.12, 0.48);
    headGroup.add(beak);

    // Glowing Eyes
    const eyeGeo = new THREE.SphereGeometry(0.065, 8, 8);
    const eyeLeft = new THREE.Mesh(eyeGeo, accentMat);
    eyeLeft.position.set(-0.22, 0.04, 0.15);
    headGroup.add(eyeLeft);

    const eyeRight = new THREE.Mesh(eyeGeo, accentMat);
    eyeRight.position.set(0.22, 0.04, 0.15);
    headGroup.add(eyeRight);

    // Tail Feathers (Spread Fan)
    const tailGroup = new THREE.Group();
    tailGroup.position.set(0, 0.08, -1.05);
    falconRoot.add(tailGroup);

    for (let t = -2; t <= 2; t++) {
      const angle = t * 0.14;
      const length = 1.3 - Math.abs(t) * 0.18;
      const tailFeatherGeo = new THREE.BoxGeometry(0.24, 0.03, length);
      tailFeatherGeo.translate(0, 0, -length / 2);
      const tailFeather = new THREE.Mesh(tailFeatherGeo, wingSecondaryMat);
      tailFeather.rotation.y = angle;
      tailFeather.rotation.x = -0.08;
      tailGroup.add(tailFeather);
    }

    // 5. Build Articulated Wings (Left & Right)
    function buildWing(isLeft) {
      const wingGroup = new THREE.Group();
      const dir = isLeft ? -1 : 1;

      // Shoulder pivot
      wingGroup.position.set(dir * 0.45, 0.12, 0.15);

      // Main Wing Arm
      const armGeo = new THREE.BoxGeometry(1.6, 0.06, 0.65);
      armGeo.translate(dir * 0.8, 0, -0.1);
      const arm = new THREE.Mesh(armGeo, bodyMat);
      wingGroup.add(arm);

      // Primary Feather Blades (5 long outer tapered blades)
      const primaryCount = 6;
      for (let i = 0; i < primaryCount; i++) {
        const spanFactor = (i + 1) / primaryCount;
        const bladeLen = 1.6 + spanFactor * 0.85;
        const bladeWidth = 0.22 - spanFactor * 0.04;
        const bladeGeo = new THREE.BoxGeometry(bladeWidth, 0.025, bladeLen);
        bladeGeo.translate(0, 0, -bladeLen / 2);

        const mat = i % 2 === 0 ? wingPrimaryMat : wingSecondaryMat;
        const blade = new THREE.Mesh(bladeGeo, mat);

        blade.position.set(dir * (0.8 + i * 0.42), 0.02 * (isLeft ? 1 : -1), -0.2);
        blade.rotation.y = dir * (0.28 + i * 0.07);
        blade.rotation.z = dir * (0.05 + i * 0.02);
        wingGroup.add(blade);
      }

      // Secondary Feather Blades (Leading edge cyan glow accents)
      const secondaryCount = 4;
      for (let j = 0; j < secondaryCount; j++) {
        const bladeGeo = new THREE.BoxGeometry(0.18, 0.02, 0.95);
        bladeGeo.translate(0, 0, -0.47);
        const blade = new THREE.Mesh(bladeGeo, j === 0 ? accentMat : wingPrimaryMat);
        blade.position.set(dir * (0.35 + j * 0.35), 0.04, 0.12);
        blade.rotation.y = dir * 0.12;
        wingGroup.add(blade);
      }

      return wingGroup;
    }

    const leftWing = buildWing(true);
    const rightWing = buildWing(false);
    falconRoot.add(leftWing);
    falconRoot.add(rightWing);

    // Initial orientation: Soaring majestic glide angled slightly toward viewer
    falconRoot.rotation.x = 0.22;
    falconRoot.rotation.y = -0.15;
    falconRoot.scale.set(1.15, 1.15, 1.15);

    // 6. Smooth Mouse Parallax Tracking
    const mouse = { x: 0, y: 0, targetX: 0, targetY: 0 };

    if (hero) {
      hero.addEventListener('mousemove', (e) => {
        const rect = hero.getBoundingClientRect();
        const normX = (e.clientX - rect.left) / rect.width - 0.5;
        const normY = (e.clientY - rect.top) / rect.height - 0.5;
        // 10 - 14 degrees max tilt
        mouse.targetX = normX * 0.22;
        mouse.targetY = -normY * 0.18;
      }, { passive: true });

      hero.addEventListener('mouseleave', () => {
        mouse.targetX = 0;
        mouse.targetY = 0;
      }, { passive: true });
    }

    // 7. Window Resize Handler
    let resizeTimer = null;
    window.addEventListener('resize', () => {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(() => {
        // Disable if viewport is resized below 768px
        if (window.innerWidth < 768) {
          canvas.style.display = 'none';
          return;
        } else {
          canvas.style.display = 'block';
        }

        width = container.clientWidth || 520;
        height = container.clientHeight || 440;
        camera.aspect = width / height;
        camera.updateProjectionMatrix();
        renderer.setSize(width, height);
      }, 120);
    }, { passive: true });

    // 8. IntersectionObserver (Pause render when off-screen for 60 FPS efficiency)
    let isPaused = false;
    if ('IntersectionObserver' in window && hero) {
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          isPaused = !entry.isIntersecting;
        });
      }, { threshold: 0.05 });
      observer.observe(hero);
    }

    // 9. Animation Render Loop (Continuous float ~5.4s loop, wing flex, parallax)
    let time = 0;
    let animId = null;

    function animate() {
      animId = requestAnimationFrame(animate);
      if (isPaused || window.innerWidth < 768) return;

      time += 0.016;

      // Gentle continuous floating motion (up/down ~5.4 sec loop)
      const floatY = Math.sin(time * 1.15) * 0.28;
      falconRoot.position.y = floatY;

      // Subtle bank & pitch sway
      falconRoot.position.x = Math.cos(time * 0.75) * 0.12;

      // Subtle wing flapping & flex (sinusoidal glide cycles)
      const wingFlex = Math.sin(time * 1.8) * 0.11;
      leftWing.rotation.z = 0.06 + wingFlex;
      leftWing.rotation.y = -0.04 + wingFlex * 0.35;

      rightWing.rotation.z = -0.06 - wingFlex;
      rightWing.rotation.y = 0.04 - wingFlex * 0.35;

      // Tail subtle rudder compensation
      tailGroup.rotation.y = Math.sin(time * 0.9) * 0.06;

      // Head subtle glance
      headGroup.rotation.y = Math.sin(time * 0.6) * 0.08;

      // Mouse Parallax (LERP with damping)
      mouse.x += (mouse.targetX - mouse.x) * 0.045;
      mouse.y += (mouse.targetY - mouse.y) * 0.045;

      falconRoot.rotation.y = -0.15 + mouse.x;
      falconRoot.rotation.x = 0.22 + mouse.y + Math.sin(time * 0.9) * 0.025;
      falconRoot.rotation.z = mouse.x * -0.3 + Math.sin(time * 0.75) * 0.035;

      // Point light orbit glow
      fillLight.position.x = 3 + Math.sin(time) * 1.2;
      rimLight.position.y = 3 + Math.cos(time * 1.2) * 0.8;

      renderer.render(scene, camera);
    }

    animate();

    // Mark 3D as successfully active (cleanly transitions static fallback)
    container.classList.add('is-3d-active');
  }

})();

