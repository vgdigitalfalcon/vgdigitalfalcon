/**
 * =============================================================================
 * VG DIGITAL - YOUTUBE RETENTION & VIEWS GROWTH GRAPH 3D TERRAIN
 * =============================================================================
 * Pure 3D WebGL background for youtube-seo-agency.html powered by Three.js.
 *
 * Core Features:
 * 1. 3D Perspective Analytics Grid Floor on deep obsidian #0a0614 base with
 *    subtle rgba(124, 58, 237, 0.14) grid lines & THREE.FogExp2 depth fade.
 * 2. Glowing Upward Views-Growth Curve (THREE.CatmullRomCurve3 + Dual-Tone Tube):
 *    Cyber Cyan (#22d3ee) to Electric Violet (#7c3aed) gradient glow with
 *    luminous additive bloom tube and vertical holographic area-chart curtain.
 * 3. 4 Floating Milestone Nodes (1K, 10K, 100K, 1M):
 *    Glowing spherical nodes along the ascending curve with animated pulse rings
 *    and crisp glassmorphic floating pill labels.
 * 4. Smooth Scroll-Linked Cinematic Camera Journey:
 *    Window scroll is smoothly lerped to advance the camera along the upward
 *    views breakout trajectory.
 * 5. 60fps Performance Safeguards: devicePixelRatio capped at 2, mobile LOD,
 *    tab visibility pauses, zero GC churn in render loop.
 *
 * NOTE: The Falcon bird mesh is strictly kept exclusively on the Home Page
 * (index.html via peregrine-falcon-flight.js) and is NOT loaded or rendered here.
 * =============================================================================
 */

(function (window, document) {
  'use strict';

  window.VGYtGrowthTerrain = {
    initialized: false,
    instance: null,
    init: initYtGrowthTerrain
  };

  // Backwards compatibility alias
  window.VGFalconFlight = window.VGYtGrowthTerrain;

  function initYtGrowthTerrain(userOptions) {
    if (window.VGYtGrowthTerrain.initialized && window.VGYtGrowthTerrain.instance) {
      return window.VGYtGrowthTerrain.instance;
    }

    if (typeof THREE === 'undefined') {
      console.warn('[VGYtGrowthTerrain] Three.js not found. 3D background disabled.');
      return null;
    }

    var heroBanner = document.querySelector('.vg-yt-hero-section') || document.getElementById('youtube-seo-hero');
    var container = document.getElementById('yt-retention-canvas-container');
    var canvas = document.getElementById('vg-yt-flight-canvas');

    if (!canvas) {
      canvas = document.createElement('canvas');
      canvas.id = 'vg-yt-flight-canvas';
      canvas.className = 'vg-yt-flight-canvas';
      if (container) {
        container.appendChild(canvas);
      } else if (heroBanner) {
        heroBanner.insertBefore(canvas, heroBanner.firstChild);
      } else {
        document.body.insertBefore(canvas, document.body.firstChild);
      }
    } else if (heroBanner && !container && canvas.parentElement !== heroBanner) {
      heroBanner.insertBefore(canvas, heroBanner.firstChild);
    }

    canvas.style.cssText = 'position: absolute !important; inset: 0 !important; top: 0 !important; left: 0 !important; width: 100% !important; height: 100% !important; z-index: 0 !important; pointer-events: none !important; display: block !important; margin: 0 !important; padding: 0 !important; overflow: hidden !important;';

    var isMobile = window.innerWidth < 768;

    var options = Object.assign({
      canvasId: 'vg-yt-flight-canvas',
      baseColor: 0x0a0614,
      fogDensity: 0.016,
      gridWidth: 160,
      gridLength: 650,
      gridSpacing: 7.0,
      scrollMultiplier: 260
    }, userOptions || {});

    // --- State & Variables ---
    var renderer, scene, camera;
    var analyticsGridGroup;
    var growthCurve, mainTubeMesh, glowTubeMesh, curtainMesh;
    var milestoneNodes = [];
    var ambientDataParticles;
    var animationFrameId = null;
    var isRunning = true;
    var isIntersecting = true;
    var clock = new THREE.Clock();

    var perfFrameCount = 0;
    var targetScrollProgress = 0;
    var currentScrollProgress = 0;

    // ---------------------------------------------------------------------------
    // 1. SCENE, RENDERER & CAMERA INITIALIZATION
    // ---------------------------------------------------------------------------
    try {
      renderer = new THREE.WebGLRenderer({
        canvas: canvas,
        antialias: !isMobile,
        alpha: true,
        powerPreference: 'high-performance'
      });
    } catch (e) {
      console.warn('[VGYtGrowthTerrain] WebGL init failed:', e);
      return null;
    }

    scene = new THREE.Scene();
    scene.background = new THREE.Color(options.baseColor);
    scene.fog = new THREE.FogExp2(options.baseColor, options.fogDensity);

    var initialSize = getBannerSize();
    camera = new THREE.PerspectiveCamera(50, initialSize.width / initialSize.height, 0.5, 950);
    camera.position.set(-2.0, 3.8, 22.0);

    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, isMobile ? 1.0 : 1.25));
    renderer.setSize(initialSize.width, initialSize.height);
    camera.aspect = initialSize.width / initialSize.height;
    camera.updateProjectionMatrix();
    renderer.setClearColor(options.baseColor, 1.0);
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.25;

    // Studio & Analytics Cyber Lights
    var ambientLight = new THREE.AmbientLight(0x180f30, 1.8);
    scene.add(ambientLight);

    var cyanLeadLight = new THREE.DirectionalLight(0x22d3ee, 2.2);
    cyanLeadLight.position.set(-25, 35, 10);
    scene.add(cyanLeadLight);

    var violetBackLight = new THREE.DirectionalLight(0x7c3aed, 2.8);
    violetBackLight.position.set(30, 45, -80);
    scene.add(violetBackLight);

    // ---------------------------------------------------------------------------
    // 2. 3D PERSPECTIVE ANALYTICS GRID FLOOR (rgba(124, 58, 237, 0.14))
    // ---------------------------------------------------------------------------
    analyticsGridGroup = new THREE.Group();
    var floorY = -7.2;

    var gridHalfW = options.gridWidth * 0.5;
    var gridTotalZ = options.gridLength;
    var step = options.gridSpacing;

    var gridVertices = [];
    var gridColors = [];

    var baseGridCol = new THREE.Color(0x7c3aed);
    var cyanGridCol = new THREE.Color(0x22d3ee);

    // Longitudinal Z lines (parallel forward lines)
    for (var x = -gridHalfW; x <= gridHalfW; x += step) {
      var isCenterLine = Math.abs(x) < step * 0.6;
      var cCol = isCenterLine ? cyanGridCol : baseGridCol;

      gridVertices.push(x, floorY, 40.0);
      gridVertices.push(x, floorY, -gridTotalZ);

      var a = isCenterLine ? 0.35 : 0.14;
      gridColors.push(cCol.r * a, cCol.g * a, cCol.b * a);
      gridColors.push(cCol.r * a * 0.1, cCol.g * a * 0.1, cCol.b * a * 0.1);
    }

    // Transverse X lines (cross interval lines)
    for (var z = 40.0; z >= -gridTotalZ; z -= step) {
      var isMajorInterval = Math.round(z) % (step * 5) === 0;
      var tCol = isMajorInterval ? cyanGridCol : baseGridCol;

      gridVertices.push(-gridHalfW, floorY, z);
      gridVertices.push(gridHalfW, floorY, z);

      var tA = isMajorInterval ? 0.28 : 0.12;
      gridColors.push(tCol.r * tA * 0.2, tCol.g * tA * 0.2, tCol.b * tA * 0.2);
      gridColors.push(tCol.r * tA, tCol.g * tA, tCol.b * tA);
    }

    var gridGeom = new THREE.BufferGeometry();
    gridGeom.setAttribute('position', new THREE.Float32BufferAttribute(gridVertices, 3));
    gridGeom.setAttribute('color', new THREE.Float32BufferAttribute(gridColors, 3));

    var gridMat = new THREE.LineBasicMaterial({
      vertexColors: true,
      transparent: true,
      blending: THREE.AdditiveBlending,
      depthWrite: false
    });

    var gridLines = new THREE.LineSegments(gridGeom, gridMat);
    analyticsGridGroup.add(gridLines);
    scene.add(analyticsGridGroup);

    // ---------------------------------------------------------------------------
    // 3. GLOWING UPWARD VIEWS-GROWTH CURVE (THREE.CatmullRomCurve3 + Tubes)
    // ---------------------------------------------------------------------------
    var curvePoints = [
      new THREE.Vector3(-14.0, -6.6, 25.0),   // Baseline Seed
      new THREE.Vector3(-9.2,  -5.6, -30.0),  // Milestone 1: 1K Views (Hook / CTR)
      new THREE.Vector3(-3.2,  -3.6, -95.0),  // Milestone 2: 10K Views (Velocity)
      new THREE.Vector3(4.5,   -0.4, -190.0), // Milestone 3: 100K Views (Browse Feature)
      new THREE.Vector3(11.2,   3.5, -315.0), // Milestone 4: 1M Views (Viral Surge)
      new THREE.Vector3(16.0,   5.8, -460.0), // Scale Horizon
      new THREE.Vector3(18.5,   7.0, -620.0)  // Deep Retention Orbit
    ];

    growthCurve = new THREE.CatmullRomCurve3(curvePoints, false, 'catmullrom', 0.2);

    // Main Core Tube Geometry
    var tubeSegs = isMobile ? 140 : 260;
    var mainTubeGeom = new THREE.TubeGeometry(growthCurve, tubeSegs, 0.32, 14, false);

    // Dual-tone Gradient Vertex Colors (#22d3ee Cyber Cyan -> #7c3aed Electric Violet)
    var vCount = mainTubeGeom.attributes.position.count;
    var tubeColors = new Float32Array(vCount * 3);
    var posAttr = mainTubeGeom.attributes.position;
    var startCol = new THREE.Color(0x22d3ee);
    var endCol = new THREE.Color(0x7c3aed);

    for (var vi = 0; vi < vCount; vi++) {
      var pZ = posAttr.getZ(vi);
      var progress = Math.min(Math.max((25.0 - pZ) / 480.0, 0.0), 1.0);
      var col = startCol.clone().lerp(endCol, Math.pow(progress, 0.85));

      tubeColors[vi * 3] = col.r;
      tubeColors[vi * 3 + 1] = col.g;
      tubeColors[vi * 3 + 2] = col.b;
    }

    mainTubeGeom.setAttribute('color', new THREE.BufferAttribute(tubeColors, 3));

    var mainTubeMat = new THREE.MeshStandardMaterial({
      vertexColors: true,
      roughness: 0.18,
      metalness: 0.82,
      emissive: 0x22d3ee,
      emissiveIntensity: 0.55
    });

    mainTubeMesh = new THREE.Mesh(mainTubeGeom, mainTubeMat);
    scene.add(mainTubeMesh);

    // Outer Neon Glow Tube (Soft Additive Light Bloom)
    var glowTubeGeom = new THREE.TubeGeometry(growthCurve, isMobile ? 90 : 160, 0.76, 10, false);
    glowTubeGeom.setAttribute('color', new THREE.BufferAttribute(new Float32Array(tubeColors), 3));

    var glowTubeMat = new THREE.MeshBasicMaterial({
      vertexColors: true,
      transparent: true,
      opacity: 0.32,
      blending: THREE.AdditiveBlending,
      side: THREE.DoubleSide,
      depthWrite: false
    });

    glowTubeMesh = new THREE.Mesh(glowTubeGeom, glowTubeMat);
    scene.add(glowTubeMesh);

    // ---------------------------------------------------------------------------
    // 4. TRANSLUCENT VERTICAL AREA-CHART CURTAIN UNDERNEATH
    // ---------------------------------------------------------------------------
    (function () {
      var sampleCount = isMobile ? 100 : 180;
      var curveSamples = growthCurve.getPoints(sampleCount);

      var curtainPositions = new Float32Array(sampleCount * 2 * 3);
      var curtainColors = new Float32Array(sampleCount * 2 * 3);
      var curtainIndices = [];

      for (var s = 0; s <= sampleCount; s++) {
        var pt = curveSamples[Math.min(s, sampleCount)];
        var sProg = Math.min(Math.max((25.0 - pt.z) / 480.0, 0.0), 1.0);
        var sCol = startCol.clone().lerp(endCol, sProg);

        // Top vertex (attached to curve)
        var topIdx = s * 2;
        curtainPositions[topIdx * 3] = pt.x;
        curtainPositions[topIdx * 3 + 1] = pt.y;
        curtainPositions[topIdx * 3 + 2] = pt.z;

        // Top color: vibrant cyan/violet at 0.38 opacity
        curtainColors[topIdx * 3] = sCol.r * 0.38;
        curtainColors[topIdx * 3 + 1] = sCol.g * 0.38;
        curtainColors[topIdx * 3 + 2] = sCol.b * 0.38;

        // Bottom vertex (projected straight down to analytics floor)
        var botIdx = s * 2 + 1;
        curtainPositions[botIdx * 3] = pt.x;
        curtainPositions[botIdx * 3 + 1] = floorY;
        curtainPositions[botIdx * 3 + 2] = pt.z;

        // Bottom color: soft fade toward floor (0.04)
        curtainColors[botIdx * 3] = sCol.r * 0.04;
        curtainColors[botIdx * 3 + 1] = sCol.g * 0.04;
        curtainColors[botIdx * 3 + 2] = sCol.b * 0.04;

        if (s < sampleCount) {
          var v0 = topIdx;
          var v1 = botIdx;
          var v2 = topIdx + 2;
          var v3 = botIdx + 2;
          curtainIndices.push(v0, v1, v2);
          curtainIndices.push(v1, v3, v2);
        }
      }

      var curtainGeom = new THREE.BufferGeometry();
      curtainGeom.setAttribute('position', new THREE.BufferAttribute(curtainPositions, 3));
      curtainGeom.setAttribute('color', new THREE.BufferAttribute(curtainColors, 3));
      curtainGeom.setIndex(curtainIndices);

      var curtainMat = new THREE.MeshBasicMaterial({
        vertexColors: true,
        transparent: true,
        opacity: 0.35,
        blending: THREE.AdditiveBlending,
        side: THREE.DoubleSide,
        depthWrite: false
      });

      curtainMesh = new THREE.Mesh(curtainGeom, curtainMat);
      scene.add(curtainMesh);
    })();

    // ---------------------------------------------------------------------------
    // 5. 4 FLOATING MILESTONE NODES (1K, 10K, 100K, 1M) + GLASSMORPHIC PILL LABELS
    // ---------------------------------------------------------------------------
    var milestonesData = [
      {
        t: 0.105,
        metric: '1K VIEWS',
        sub: 'Hook & CTR Spike',
        color: 0x22d3ee,
        hex: '#22d3ee',
        ringRadius: 0.85
      },
      {
        t: 0.235,
        metric: '10K VIEWS',
        sub: 'Algorithmic Velocity',
        color: 0x38bdf8,
        hex: '#38bdf8',
        ringRadius: 0.95
      },
      {
        t: 0.455,
        metric: '100K VIEWS',
        sub: 'Browse & Suggested',
        color: 0xa855f7,
        hex: '#a855f7',
        ringRadius: 1.10
      },
      {
        t: 0.730,
        metric: '1M+ VIEWS',
        sub: 'Viral Channel Authority',
        color: 0xe879f9,
        hex: '#e879f9',
        ringRadius: 1.30
      }
    ];

    function createGlassmorphicPillTexture(metric, subText, accentHex) {
      var pCanvas = document.createElement('canvas');
      pCanvas.width = 320;
      pCanvas.height = 120;
      var ctx = pCanvas.getContext('2d');

      // Rounded rectangle path (compact HUD chip)
      var x = 8, y = 8, w = 304, h = 104, r = 22;
      ctx.beginPath();
      ctx.moveTo(x + r, y);
      ctx.lineTo(x + w - r, y);
      ctx.quadraticCurveTo(x + w, y, x + w, y + r);
      ctx.lineTo(x + w, y + h - r);
      ctx.quadraticCurveTo(x + w, y + h, x + w - r, y + h);
      ctx.lineTo(x + r, y + h);
      ctx.quadraticCurveTo(x, y + h, x, y + h - r);
      ctx.lineTo(x + r, y);
      ctx.quadraticCurveTo(x, y, x + r, y);
      ctx.closePath();

      // Translucent frosted glass fill
      ctx.fillStyle = 'rgba(10, 6, 20, 0.88)';
      ctx.fill();

      // Subtle gradient border
      var borderGrad = ctx.createLinearGradient(x, y, x + w, y + h);
      borderGrad.addColorStop(0, accentHex);
      borderGrad.addColorStop(0.5, 'rgba(168, 85, 247, 0.50)');
      borderGrad.addColorStop(1, 'rgba(34, 211, 238, 0.30)');
      ctx.lineWidth = 3;
      ctx.strokeStyle = borderGrad;
      ctx.stroke();

      // Glowing live status dot
      ctx.beginPath();
      ctx.arc(x + 28, y + 36, 6, 0, Math.PI * 2);
      ctx.fillStyle = accentHex;
      ctx.shadowColor = accentHex;
      ctx.shadowBlur = 10;
      ctx.fill();
      ctx.shadowBlur = 0;

      // Primary Metric Text (sleek HUD chip: 1K VIEWS, 10K VIEWS, etc.)
      ctx.fillStyle = '#ffffff';
      ctx.font = 'bold 24px "Plus Jakarta Sans", system-ui, sans-serif';
      ctx.fillText(metric, x + 44, y + 44);

      // Subtitle / Telemetry Text
      ctx.fillStyle = 'rgba(203, 213, 225, 0.82)';
      ctx.font = '600 14px "Plus Jakarta Sans", system-ui, sans-serif';
      ctx.fillText(subText, x + 28, y + 80);

      var tex = new THREE.CanvasTexture(pCanvas);
      tex.needsUpdate = true;
      return tex;
    }

    milestonesData.forEach(function (data, idx) {
      var nodePos = growthCurve.getPointAt(data.t);

      var nodeGroup = new THREE.Group();
      nodeGroup.position.copy(nodePos);

      // 1. Sleek Compact 3D Sphere (Scaled Down)
      var sphereGeom = new THREE.SphereGeometry(0.22, 16, 16);
      var sphereMat = new THREE.MeshStandardMaterial({
        color: data.color,
        emissive: data.color,
        emissiveIntensity: 0.85,
        roughness: 0.2,
        metalness: 0.8
      });
      var sphereMesh = new THREE.Mesh(sphereGeom, sphereMat);
      nodeGroup.add(sphereMesh);

      // 2. Compact Animated Pulse Ring (Scaled Down)
      var ringGeom = new THREE.RingGeometry(data.ringRadius * 0.28, data.ringRadius * 0.40, 32);
      var ringMat = new THREE.MeshBasicMaterial({
        color: data.color,
        transparent: true,
        opacity: 0.65,
        blending: THREE.AdditiveBlending,
        side: THREE.DoubleSide,
        depthWrite: false
      });
      var ringMesh = new THREE.Mesh(ringGeom, ringMat);
      ringMesh.rotation.x = Math.PI / 2;
      nodeGroup.add(ringMesh);

      // 3. Subtle Point Light Accent
      var nodeLight = new THREE.PointLight(data.color, 0.9, 18);
      nodeGroup.add(nodeLight);

      // 4. Compact Floating HUD Chip Label Sprite (Scaled Down by ~60%)
      var labelTex = createGlassmorphicPillTexture(data.metric, data.sub, data.hex);
      var spriteMat = new THREE.SpriteMaterial({
        map: labelTex,
        transparent: true,
        depthTest: false,
        depthWrite: false
      });
      var sprite = new THREE.Sprite(spriteMat);
      sprite.position.set(0, 0.95, 0);
      sprite.scale.set(2.8, 1.15, 1.0);
      nodeGroup.add(sprite);

      scene.add(nodeGroup);

      milestoneNodes.push({
        group: nodeGroup,
        sphere: sphereMesh,
        ring: ringMesh,
        sprite: sprite,
        basePos: nodePos.clone(),
        baseScale: data.ringRadius,
        t: data.t,
        idx: idx
      });
    });

    // ---------------------------------------------------------------------------
    // 6. AMBIENT DATA PARTICLES & DRIFTING TELEMETRY BITS
    // ---------------------------------------------------------------------------
    var partCount = isMobile ? 65 : 130;
    var partGeom = new THREE.BufferGeometry();
    var pPositions = new Float32Array(partCount * 3);
    var pColors = new Float32Array(partCount * 3);

    for (var pi = 0; pi < partCount; pi++) {
      pPositions[pi * 3] = (Math.random() - 0.5) * options.gridWidth * 0.75;
      pPositions[pi * 3 + 1] = floorY + 0.5 + Math.random() * 16.0;
      pPositions[pi * 3 + 2] = 20.0 - Math.random() * options.gridLength;

      var isCyan = Math.random() > 0.45;
      pColors[pi * 3] = isCyan ? 0.133 : 0.486;
      pColors[pi * 3 + 1] = isCyan ? 0.827 : 0.227;
      pColors[pi * 3 + 2] = isCyan ? 0.933 : 0.929;
    }

    partGeom.setAttribute('position', new THREE.BufferAttribute(pPositions, 3));
    partGeom.setAttribute('color', new THREE.BufferAttribute(pColors, 3));

    function createCircleParticleTexture() {
      var pCanvas = document.createElement('canvas');
      pCanvas.width = 64;
      pCanvas.height = 64;
      var ctx = pCanvas.getContext('2d');
      var grad = ctx.createRadialGradient(32, 32, 0, 32, 32, 32);
      grad.addColorStop(0, 'rgba(255, 255, 255, 1.0)');
      grad.addColorStop(0.35, 'rgba(34, 211, 238, 0.75)');
      grad.addColorStop(0.70, 'rgba(124, 58, 237, 0.25)');
      grad.addColorStop(1, 'rgba(0, 0, 0, 0)');
      ctx.fillStyle = grad;
      ctx.fillRect(0, 0, 64, 64);
      var tex = new THREE.CanvasTexture(pCanvas);
      tex.needsUpdate = true;
      return tex;
    }

    var partMat = new THREE.PointsMaterial({
      size: isMobile ? 2.4 : 3.6,
      map: createCircleParticleTexture(),
      vertexColors: true,
      transparent: true,
      opacity: 0.26,
      blending: THREE.AdditiveBlending,
      depthWrite: false
    });

    ambientDataParticles = new THREE.Points(partGeom, partMat);
    scene.add(ambientDataParticles);

    // ---------------------------------------------------------------------------
    // 7. SCROLL-LINKED CAMERA JOURNEY & RESIZE HANDLERS
    // ---------------------------------------------------------------------------
    function onScroll() {
      var scrollY = window.pageYOffset || document.documentElement.scrollTop || 0;
      var bannerHeight = heroBanner ? (heroBanner.offsetHeight || window.innerHeight) : window.innerHeight;
      targetScrollProgress = Math.min(Math.max(scrollY / bannerHeight, 0), 1);
    }

    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    function getBannerSize() {
      var w = (heroBanner && heroBanner.clientWidth > 0) ? heroBanner.clientWidth : window.innerWidth;
      var h = (heroBanner && heroBanner.clientHeight > 200) ? heroBanner.clientHeight : Math.max(window.innerHeight, 700);
      return { width: Math.max(w, 320), height: Math.max(h, 400) };
    }

    function onResize() {
      if (!renderer || !camera) return;
      var size = getBannerSize();
      camera.aspect = size.width / size.height;
      camera.updateProjectionMatrix();
      renderer.setSize(size.width, size.height);
      renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, window.innerWidth < 768 ? 1.0 : 1.25));
    }

    window.addEventListener('resize', onResize, { passive: true });
    if ('IntersectionObserver' in window && heroBanner) {
      var bannerObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          isIntersecting = entry.isIntersecting;
          if (isIntersecting && !isRunning && !document.hidden) {
            isRunning = true;
            clock.start();
            animate();
          }
        });
      }, { threshold: 0.05 });
      bannerObserver.observe(heroBanner);
    }


    document.addEventListener('visibilitychange', function () {
      if (document.hidden) {
        isRunning = false;
      } else {
        isRunning = true;
        clock.start();
        animate();
      }
    });

    // ---------------------------------------------------------------------------
    // 8. 60FPS ANIMATION LOOP
    // ---------------------------------------------------------------------------
    function animate() {
      if (!isRunning) return;
      if (!isIntersecting) {
        animationFrameId = requestAnimationFrame(animate);
        return;
      }
      animationFrameId = requestAnimationFrame(animate);

      var delta = clock.getDelta();
      var elapsedTime = clock.getElapsedTime();

      // Smooth scroll lerp interpolation
      currentScrollProgress += (targetScrollProgress - currentScrollProgress) * 0.055;

      // 1. Camera Tracks Along Upward Growth Curve
      var curveProgress = 0.08 + currentScrollProgress * 0.72;
      var targetPt = growthCurve.getPointAt(Math.min(curveProgress, 0.98));
      var futurePt = growthCurve.getPointAt(Math.min(curveProgress + 0.08, 0.99));

      // Chase camera offset relative to target curve point
      var camChaseDist = 18.0 + (1.0 - currentScrollProgress) * 4.0;
      var camElev = 3.6 + Math.sin(currentScrollProgress * Math.PI) * 1.8;
      var camSideOffset = -4.5 + Math.sin(currentScrollProgress * Math.PI * 1.2) * 2.2;

      var targetCamX = targetPt.x + camSideOffset;
      var targetCamY = targetPt.y + camElev;
      var targetCamZ = targetPt.z + camChaseDist;

      camera.position.x += (targetCamX - camera.position.x) * 0.06;
      camera.position.y += (targetCamY - camera.position.y) * 0.06;
      camera.position.z += (targetCamZ - camera.position.z) * 0.06;

      // Smooth camera aim ahead along trajectory
      camera.lookAt(futurePt.x, futurePt.y + 0.6, futurePt.z);

      // 2. Animate Milestone Pulse Rings & Floats
      milestoneNodes.forEach(function (node) {
        var pulse = (Math.sin(elapsedTime * 2.8 + node.idx * 1.5) + 1.0) * 0.5;
        var scale = 1.0 + pulse * 0.45;
        node.ring.scale.set(scale, scale, 1.0);
        node.ring.material.opacity = 0.65 - pulse * 0.45;

        // Subtle gentle vertical hovering
        var hoverY = Math.sin(elapsedTime * 1.4 + node.idx * 1.2) * 0.18;
        node.group.position.y = node.basePos.y + hoverY;
      });

      // 3. Subtle Luminous Glow Tube Pulse
      if (glowTubeMat) {
        glowTubeMat.opacity = 0.28 + Math.sin(elapsedTime * 1.8) * 0.07;
      }

      // 4. Parallax Data Particles Recycling
      var pAttr = ambientDataParticles.geometry.attributes.position;
      var pCount = pAttr.count;
      var camZ = camera.position.z;

      for (var p = 0; p < pCount; p++) {
        var pZ = pAttr.getZ(p);
        if (pZ > camZ + 8.0) {
          pAttr.setZ(p, camZ - 280.0 - Math.random() * 80.0);
          pAttr.setX(p, (Math.random() - 0.5) * options.gridWidth * 0.7);
          pAttr.setY(p, floorY + 0.5 + Math.random() * 16.0);
        }
      }
      pAttr.needsUpdate = true;

      // Render
      renderer.render(scene, camera);
    }

    animate();

    var instance = {
      canvas: canvas,
      renderer: renderer,
      scene: scene,
      camera: camera,
      growthCurve: growthCurve,
      destroy: function () {
        isRunning = false;
        if (animationFrameId) cancelAnimationFrame(animationFrameId);
        window.removeEventListener('scroll', onScroll);
        window.removeEventListener('resize', onResize);
        if (renderer) renderer.dispose();
        window.VGYtGrowthTerrain.initialized = false;
        window.VGYtGrowthTerrain.instance = null;
      }
    };

    window.VGYtGrowthTerrain.initialized = true;
    window.VGYtGrowthTerrain.instance = instance;
    return instance;
  }

  function tryInit() {
    if (window.VGYtGrowthTerrain.initialized) return;
    if (typeof THREE === 'undefined') {
      var attempts = 0;
      var interval = setInterval(function () {
        attempts++;
        if (typeof THREE !== 'undefined') {
          clearInterval(interval);
          initYtGrowthTerrain();
        } else if (attempts > 30) {
          clearInterval(interval);
          console.warn('[VGYtGrowthTerrain] Three.js not found after timeout.');
        }
      }, 50);
      return;
    }
    initYtGrowthTerrain();
  }

  // Auto-init on DOM ready or window load
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', tryInit);
  } else {
    tryInit();
  }
  window.addEventListener('load', function () {
    if (!window.VGYtGrowthTerrain.initialized) {
      tryInit();
    }
  });

})(window, document);
