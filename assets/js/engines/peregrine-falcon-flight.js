/**
 * =============================================================================
 * VG DIGITAL FALCON — TRUE PHOTOREALISTIC PEREGRINE FALCON FLIGHT PATH
 * =============================================================================
 * Interactive 3D scroll-driven background scoped strictly to the Home Page
 * Banner Section (.vg-hero-section). Powered by Three.js WebGL.
 *
 * Architecture:
 * 1. Primary / Fallback Model Architecture:
 *    - Primary: Attempts to load rigged .glb (./assets/models/peregrine-falcon.glb).
 *    - Hyper-Realistic Fallback: Uses high-subdivision 3D Wing-Flex Mesh (PlaneGeometry
 *      with 32x16 subdivisions) textured with the authentic alpha-transparent cutout
 *      (./assets/img/peregrine-falcon-real.png).
 * 2. Custom Wing-Flex Vertex Shader:
 *    - Natural biological wing flexing: wave propagation from body out to wingtips,
 *      aerodynamic camber, and dynamic wing twist during flight & banking.
 * 3. Cinematic Rim Lighting & Depth Integration:
 *    - Studio key lighting + Dual-tone Fresnel rim highlights (#00F0FF Cyber Cyan
 *      on the left wing, #A855F7 Electric Violet on the right wing) along the feather
 *      edges so the wildlife photograph blends seamlessly into our #07070F dark canyon.
 * 4. Subtle Wingtip Aerodynamic Light Trails:
 *    - Dual-tone vapor trails (#00F0FF left, #A855F7 right) using THREE.AdditiveBlending
 *      at 0.45 opacity streaming off pointed wingtips.
 * 5. Dark Obsidian Canyon Corridor:
 *    - #0D0D1A basalt rock contours with THREE.FogExp2('#07070F', 0.018).
 * 6. Banner-Scoped Scroll Journey:
 *    - Bound to banner height; automatically pauses via IntersectionObserver when
 *      scrolled past the hero section.
 * =============================================================================
 */

(function (window, document) {
  'use strict';

  window.VGPeregrineFlight = {
    initialized: false,
    instance: null,
    init: initPeregrineFlight
  };

  function initPeregrineFlight(userOptions) {
    if (window.VGPeregrineFlight.initialized && window.VGPeregrineFlight.instance) {
      return window.VGPeregrineFlight.instance;
    }

    if (typeof THREE === 'undefined') {
      console.warn('[VGPeregrineFlight] Three.js not found. 3D flight disabled.');
      return null;
    }

    var heroBanner = document.querySelector('.vg-hero-section');
    if (!heroBanner) {
      return null;
    }

    var options = Object.assign({
      canvasId: 'vg-home-peregrine-canvas',
      baseColor: 0x0a0614,
      fogDensity: 0.015,
      canyonWidth: 68,
      canyonChunkLength: 190,
      chunkCount: 3,
      particleCount: 150,
      trailLength: 46,
      speed: 14.5,
      scrollMultiplier: 260.0,
      glbModelPath: 'assets/models/peregrine-falcon.glb',
      realCutoutPath: 'assets/img/peregrine-falcon-real.png'
    }, userOptions || {});

    var canvas = document.getElementById(options.canvasId);
    var container = document.getElementById('falcon-canvas-container');
    if (!canvas) {
      canvas = document.createElement('canvas');
      canvas.id = options.canvasId;
      canvas.className = 'vg-home-peregrine-canvas';
      if (container) {
        container.appendChild(canvas);
      } else {
        heroBanner.insertBefore(canvas, heroBanner.firstChild);
      }
    } else if (!container && canvas.parentElement !== heroBanner) {
      heroBanner.insertBefore(canvas, heroBanner.firstChild);
    }

    var isMobile = window.innerWidth < 768;
    if (isMobile) {
      options.particleCount = 65;
      options.trailLength = 30;
    }

    function getBannerSize() {
      var w = heroBanner ? (heroBanner.clientWidth || window.innerWidth) : window.innerWidth;
      var h = heroBanner ? (heroBanner.clientHeight || window.innerHeight) : window.innerHeight;
      return { width: w, height: h };
    }

    var initialSize = getBannerSize();

    // --- State & Variables ---
    var renderer, scene, camera;
    var canyonChunks = [];
    var falconGroup, falconMesh, falconShaderMaterial;
    var leftWingTipMarker, rightWingTipMarker;
    var leftTrailRibbon, rightTrailRibbon;
    var ambientParticles;
    var animationMixer = null;
    var animationFrameId = null;
    var isRunning = true;
    var isIntersecting = true;
    var clock = new THREE.Clock();

    var targetScrollProgress = 0;
    var currentScrollProgress = 0;
    var flightZ = 0;

    var tmpVecA = new THREE.Vector3();
    var tmpVecB = new THREE.Vector3();
    var tmpVecC = new THREE.Vector3();

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
      console.warn('[VGPeregrineFlight] WebGL init failed:', e);
      return null;
    }

    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, isMobile ? 1.0 : 1.25));
    renderer.setSize(initialSize.width, initialSize.height);
    renderer.setClearColor(0x000000, 0.0);
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.3;

    scene = new THREE.Scene();
    scene.fog = new THREE.FogExp2(options.baseColor, options.fogDensity);

    camera = new THREE.PerspectiveCamera(52, initialSize.width / initialSize.height, 0.5, 950);
    camera.position.set(0, 3.8, 19);

    // Studio Key Lighting & Atmospheric Lights (Refined & Balanced)
    var ambientLight = new THREE.AmbientLight(0x160f2e, 1.6);
    scene.add(ambientLight);

    var keyLight = new THREE.DirectionalLight(0xf5edff, 1.6);
    keyLight.position.set(20, 45, 20);
    scene.add(keyLight);

    // Refined Dual-Tone Grazing Rim Lighting (#22d3ee Neon Cyan & #a855f7 Soft Violet)
    var cyanRimLight = new THREE.DirectionalLight(0x22d3ee, 1.8);
    cyanRimLight.position.set(-35, 18, -15);
    scene.add(cyanRimLight);

    var violetRimLight = new THREE.DirectionalLight(0xa855f7, 2.0);
    violetRimLight.position.set(35, 30, 10);
    scene.add(violetRimLight);

    // ---------------------------------------------------------------------------
    // 2. PROCEDURAL CANYON PATH FUNCTION (#0D0D1A Basalt Terrain)
    // ---------------------------------------------------------------------------
    function getCanyonCenterX(z) {
      return 4.2 + Math.sin(z * 0.009) * 8.0;
    }

    function getCanyonHeight(xRel, z) {
      var absX = Math.abs(xRel);
      var floorHalfWidth = 22.0; // Wide open floor pushes steep walls far outward
      var h = -14.5; // Deep low base keeps floor below UI

      h += Math.sin(xRel * 0.25 + z * 0.075) * 0.9 + Math.cos(z * 0.038) * 1.1;

      if (absX > floorHalfWidth) {
        var wallFactor = (absX - floorHalfWidth) / (options.canyonWidth * 0.5 - floorHalfWidth);
        wallFactor = Math.min(Math.max(wallFactor, 0), 1.35);
        // Significantly lower ridge on the center-left (max 10.0 vs 15.0 on far right)
        var maxElevation = xRel < 0 ? 10.0 : 15.0;
        var cliffElevation = Math.pow(wallFactor, 1.55) * maxElevation;
        var cragNoise = Math.sin(xRel * 0.65 + z * 0.16) * 1.5 + Math.cos(z * 0.22 - xRel * 0.45) * 1.2;
        h += cliffElevation + cragNoise;
      }
      return h;
    }

    var basaltRockMaterial = new THREE.MeshStandardMaterial({
      color: 0x0e091d,
      roughness: 0.85,
      metalness: 0.18,
      flatShading: true
    });

    var canyonWireMaterial = new THREE.MeshBasicMaterial({
      color: 0x7c3aed,
      wireframe: true,
      transparent: true,
      opacity: 0.22
    });

    var segX = isMobile ? 24 : 36;
    var segZ = isMobile ? 32 : 48;

    for (var i = 0; i < options.chunkCount; i++) {
      var chunkGeom = new THREE.PlaneGeometry(options.canyonWidth, options.canyonChunkLength, segX, segZ);
      chunkGeom.rotateX(-Math.PI / 2);

      var pAttr = chunkGeom.attributes.position;
      var vCount = pAttr.count;
      var baseX = new Float32Array(vCount);
      var baseZ = new Float32Array(vCount);

      for (var bv = 0; bv < vCount; bv++) {
        baseX[bv] = pAttr.getX(bv);
        baseZ[bv] = pAttr.getZ(bv);
      }

      var chunkMesh = new THREE.Mesh(chunkGeom, basaltRockMaterial);
      var chunkWire = new THREE.Mesh(chunkGeom, canyonWireMaterial);
      chunkMesh.add(chunkWire);

      var chunkObj = {
        mesh: chunkMesh,
        geometry: chunkGeom,
        baseX: baseX,
        baseZ: baseZ,
        zStart: -i * options.canyonChunkLength
      };

      updateChunkGeometry(chunkObj, chunkObj.zStart);
      scene.add(chunkMesh);
      canyonChunks.push(chunkObj);
    }

    function updateChunkGeometry(chunkObj, zOffset) {
      chunkObj.zStart = zOffset;
      var posAttr = chunkObj.geometry.attributes.position;
      var totalVerts = posAttr.count;
      var bX = chunkObj.baseX;
      var bZ = chunkObj.baseZ;

      for (var v = 0; v < totalVerts; v++) {
        var localX = bX[v];
        var localZ = bZ[v];
        var worldZ = zOffset + localZ;
        var centerX = getCanyonCenterX(worldZ);

        var worldX = localX + centerX;
        var worldY = getCanyonHeight(localX, worldZ);

        posAttr.setX(v, worldX);
        posAttr.setY(v, worldY);
      }

      posAttr.needsUpdate = true;
      chunkObj.geometry.computeVertexNormals();
      chunkObj.mesh.position.set(0, 0, zOffset);
    }

    // ---------------------------------------------------------------------------
    // 3. PHOTOREALISTIC PEREGRINE FALCON (3D WING-FLEX MESH + SHADER)
    // ---------------------------------------------------------------------------
    falconGroup = new THREE.Group();

    // Custom Vertex Shader: Biological Wing Flexing & Dihedral Camber
    var falconVertexShader = `
      uniform float uTime;
      varying vec2 vUv;
      varying vec3 vNormalVec;
      varying vec3 vViewPos;
      varying float vLocalX;

      void main() {
        vUv = uv;
        vLocalX = position.x;

        vec3 pos = position;
        float halfWidth = 7.1; // corresponds to 14.2 / 2
        float s = clamp(abs(pos.x) / halfWidth, 0.0, 1.0);
        float flexFactor = smoothstep(0.12, 1.0, s);

        // Smooth biological wing-flap stroke wave propagation
        float flapPhase = uTime * 5.2 - s * 1.1;
        float flapY = sin(flapPhase) * 0.95 * flexFactor;

        // Wing dihedral arch & aerodynamic camber
        float dihedral = sin(s * 1.5707) * 0.45;
        pos.y += flapY + dihedral;

        // Dynamic wing twist (washout during stroke)
        float twist = cos(flapPhase) * 0.22 * flexFactor * (pos.y / 5.3);
        pos.z += twist - pow(s, 1.4) * 0.55;

        vNormalVec = normalize(normalMatrix * normal);
        vec4 mvPosition = modelViewMatrix * vec4(pos, 1.0);
        vViewPos = -mvPosition.xyz;
        gl_Position = projectionMatrix * mvPosition;
      }
    `;

    // Custom Fragment Shader: Realistic Feather Texture + Dual-Tone Rim Lighting
    var falconFragmentShader = `
      uniform sampler2D uTexture;
      uniform float uOpacity;
      varying vec2 vUv;
      varying vec3 vNormalVec;
      varying vec3 vViewPos;
      varying float vLocalX;

      void main() {
        vec4 texColor = texture2D(uTexture, vUv);
        if (texColor.a < 0.05) discard;

        // View direction & normal
        vec3 viewDir = normalize(vViewPos);
        vec3 normal = normalize(vNormalVec);
        float NdotV = max(0.0, dot(normal, viewDir));
        float fresnel = pow(1.0 - NdotV, 2.8);

        // Subtle dual-tone edge rim accents (#22d3ee Neon Cyan & #a855f7 Soft Violet)
        vec3 cyanRim = vec3(0.133, 0.827, 0.933);       // #22d3ee Neon Cyan
        vec3 violetRim = vec3(0.659, 0.333, 0.969);     // #a855f7 Soft Luxury Violet
        float sideFactor = smoothstep(-3.8, 3.8, vLocalX);
        vec3 rimColor = mix(cyanRim, violetRim, sideFactor);

        // High contrast barred feather plumage: keeps darks rich and barred lines razor sharp
        vec3 baseColor = pow(texColor.rgb, vec3(1.06));
        // Grazing edge rim restricted strictly to outer contours (fresnel power 3.4)
        float edgeFresnel = pow(1.0 - NdotV, 3.4);
        // Controlled multiplier (0.50 max) eliminates overexposure so feather details stay crisp
        vec3 finalColor = baseColor + rimColor * (edgeFresnel * 0.50);

        gl_FragColor = vec4(finalColor, texColor.a * uOpacity);
      }
    `;

    var textureLoader = new THREE.TextureLoader();
    var falconTexture = textureLoader.load(options.realCutoutPath, function () {
      if (renderer) renderer.render(scene, camera);
    });
    falconTexture.generateMipmaps = true;
    falconTexture.minFilter = THREE.LinearMipmapLinearFilter;

    falconShaderMaterial = new THREE.ShaderMaterial({
      uniforms: {
        uTexture: { value: falconTexture },
        uTime: { value: 0.0 },
        uOpacity: { value: 1.0 }
      },
      vertexShader: falconVertexShader,
      fragmentShader: falconFragmentShader,
      transparent: true,
      side: THREE.DoubleSide
    });

    // High-subdivision 3D mesh (width: 14.2, height: 10.6, 32 x 16 segments)
    var birdW = 14.2;
    var birdH = 10.6;
    var falconGeom = new THREE.PlaneGeometry(birdW, birdH, 32, 16);
    // Angled forward towards camera so wings, head, and tail are displayed at a 3/4 perspective
    falconGeom.rotateX(-1.32);

    falconMesh = new THREE.Mesh(falconGeom, falconShaderMaterial);
    falconGroup.add(falconMesh);

    // Primary wingtip markers corresponding to the pointed tips of the real bird photo
    leftWingTipMarker = new THREE.Object3D();
    leftWingTipMarker.position.set(-6.8, -0.6, 2.2);
    falconGroup.add(leftWingTipMarker);

    rightWingTipMarker = new THREE.Object3D();
    rightWingTipMarker.position.set(6.8, 1.2, -1.2);
    falconGroup.add(rightWingTipMarker);

    // Subtle dual-tone wingtip aura accents (low studio accent intensities)
    var falconCyanAura = new THREE.PointLight(0x22d3ee, 1.2, 35);
    falconCyanAura.position.set(-6.2, 0.4, 1.5);
    falconGroup.add(falconCyanAura);

    var falconVioletAura = new THREE.PointLight(0xa855f7, 1.4, 35);
    falconVioletAura.position.set(6.2, 1.0, -0.8);
    falconGroup.add(falconVioletAura);

    // Ambient Halo Glow Backing behind Falcon (subtle luxury aura, non-washed out)
    (function () {
      var hCanvas = document.createElement('canvas');
      hCanvas.width = 256;
      hCanvas.height = 256;
      var hCtx = hCanvas.getContext('2d');
      var hGrad = hCtx.createRadialGradient(128, 128, 0, 128, 128, 128);
      hGrad.addColorStop(0, 'rgba(124, 58, 237, 0.35)');
      hGrad.addColorStop(0.35, 'rgba(34, 211, 238, 0.18)');
      hGrad.addColorStop(0.75, 'rgba(124, 58, 237, 0.04)');
      hGrad.addColorStop(1, 'rgba(0, 0, 0, 0)');
      hCtx.fillStyle = hGrad;
      hCtx.fillRect(0, 0, 256, 256);
      var hTex = new THREE.CanvasTexture(hCanvas);

      var haloMat = new THREE.MeshBasicMaterial({
        map: hTex,
        transparent: true,
        opacity: 0.22,
        blending: THREE.AdditiveBlending,
        depthWrite: false,
        side: THREE.DoubleSide
      });
      var haloMesh = new THREE.Mesh(new THREE.PlaneGeometry(birdW * 1.25, birdH * 1.25), haloMat);
      haloMesh.position.set(0, -0.2, -0.35);
      falconGroup.add(haloMesh);
    })();

    // scale(1.5): Enlarge 1.5x so full spread wings are unmistakable as hero centerpiece
    falconGroup.scale.set(1.5, 1.5, 1.5);
    scene.add(falconGroup);

    // Optional Rigged .GLB loader attempt (if GLTFLoader is available and model exists)
    if (typeof THREE.GLTFLoader !== 'undefined') {
      var gltfLoader = new THREE.GLTFLoader();
      gltfLoader.load(options.glbModelPath, function (gltf) {
        if (gltf && gltf.scene) {
          // If a rigged .glb model is found, smoothly swap in the 3D model
          falconGroup.remove(falconMesh);
          var glbModel = gltf.scene;
          glbModel.scale.set(2.4, 2.4, 2.4);
          glbModel.rotation.y = Math.PI;
          falconGroup.add(glbModel);

          if (gltf.animations && gltf.animations.length > 0) {
            animationMixer = new THREE.AnimationMixer(glbModel);
            var action = animationMixer.clipAction(gltf.animations[0]);
            action.play();
          }
        }
      }, undefined, function () {
        // Fallback already running smoothly
      });
    }

    // ---------------------------------------------------------------------------
    // 4. SUBTLE WINGTIP AERODYNAMIC LIGHT TRAILS (AdditiveBlending @ 0.45 Opacity)
    // ---------------------------------------------------------------------------
    function createWingtipTrail(hexColor, maxPoints) {
      var totalVerts = maxPoints * 2;
      var positions = new Float32Array(totalVerts * 3);
      var colors = new Float32Array(totalVerts * 3);
      var baseCol = new THREE.Color(hexColor);

      for (var p = 0; p < maxPoints; p++) {
        var alpha = Math.pow(1.0 - (p / maxPoints), 1.35) * 0.45;
        var r = baseCol.r * alpha;
        var g = baseCol.g * alpha;
        var b = baseCol.b * alpha;

        colors[p * 6] = r;
        colors[p * 6 + 1] = g;
        colors[p * 6 + 2] = b;

        colors[p * 6 + 3] = r;
        colors[p * 6 + 4] = g;
        colors[p * 6 + 5] = b;
      }

      var indices = [];
      for (var seg = 0; seg < maxPoints - 1; seg++) {
        var v0 = seg * 2;
        var v1 = v0 + 1;
        var v2 = v0 + 2;
        var v3 = v0 + 3;
        indices.push(v0, v1, v2);
        indices.push(v1, v3, v2);
      }

      var geom = new THREE.BufferGeometry();
      geom.setAttribute('position', new THREE.BufferAttribute(positions, 3));
      geom.setAttribute('color', new THREE.BufferAttribute(colors, 3));
      geom.setIndex(indices);

      var mat = new THREE.MeshBasicMaterial({
        vertexColors: true,
        transparent: true,
        opacity: 0.45,
        blending: THREE.AdditiveBlending,
        side: THREE.DoubleSide,
        depthWrite: false
      });

      var mesh = new THREE.Mesh(geom, mat);
      mesh.frustumCulled = false;
      scene.add(mesh);

      var history = [];
      for (var h = 0; h < maxPoints; h++) {
        history.push(new THREE.Vector3(0, -4.5, -14.0));
      }

      return {
        mesh: mesh,
        geometry: geom,
        history: history,
        maxPoints: maxPoints,
        update: function (newWorldPos, ribbonNormal) {
          for (var k = this.maxPoints - 1; k > 0; k--) {
            this.history[k].copy(this.history[k - 1]);
          }
          this.history[0].copy(newWorldPos);

          var posAttr = this.geometry.attributes.position;
          var halfWidth = 0.42;

          for (var idx = 0; idx < this.maxPoints; idx++) {
            var pt = this.history[idx];
            var norm = ribbonNormal || tmpVecC.set(0, 1, 0);

            posAttr.setXYZ(idx * 2, pt.x + norm.x * halfWidth, pt.y + norm.y * halfWidth, pt.z + norm.z * halfWidth);
            posAttr.setXYZ(idx * 2 + 1, pt.x - norm.x * halfWidth, pt.y - norm.y * halfWidth, pt.z - norm.z * halfWidth);
          }
          posAttr.needsUpdate = true;
        }
      };
    }

    leftTrailRibbon = createWingtipTrail(0x22d3ee, options.trailLength);
    rightTrailRibbon = createWingtipTrail(0xec4899, options.trailLength);

    // ---------------------------------------------------------------------------
    // 5. ATMOSPHERIC CANYON PARTICLES
    // ---------------------------------------------------------------------------
    var partGeom = new THREE.BufferGeometry();
    var pCount = options.particleCount;
    var pPositions = new Float32Array(pCount * 3);
    var pColors = new Float32Array(pCount * 3);

    for (var pi = 0; pi < pCount; pi++) {
      pPositions[pi * 3] = (Math.random() - 0.5) * options.canyonWidth * 0.9;
      pPositions[pi * 3 + 1] = -10 + Math.random() * 26;
      pPositions[pi * 3 + 2] = -Math.random() * (options.canyonChunkLength * options.chunkCount);

      var isCyan = Math.random() > 0.5;
      pColors[pi * 3] = isCyan ? 0.0 : 0.66;
      pColors[pi * 3 + 1] = isCyan ? 0.94 : 0.33;
      pColors[pi * 3 + 2] = isCyan ? 1.0 : 0.97;
    }

    partGeom.setAttribute('position', new THREE.BufferAttribute(pPositions, 3));
    partGeom.setAttribute('color', new THREE.BufferAttribute(pColors, 3));

    // Circular Soft-Dot Particle Texture (Eliminates square debris blocks)
    function createCircleParticleTexture() {
      var pCanvas = document.createElement('canvas');
      pCanvas.width = 64;
      pCanvas.height = 64;
      var ctx = pCanvas.getContext('2d');
      var grad = ctx.createRadialGradient(32, 32, 0, 32, 32, 32);
      grad.addColorStop(0, 'rgba(255, 255, 255, 1.0)');
      grad.addColorStop(0.35, 'rgba(168, 85, 247, 0.75)');
      grad.addColorStop(0.70, 'rgba(34, 211, 238, 0.25)');
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
      opacity: 0.24, // Ultra-subtle micro-particle opacity (0.15–0.3)
      blending: THREE.AdditiveBlending,
      depthWrite: false
    });

    ambientParticles = new THREE.Points(partGeom, partMat);
    scene.add(ambientParticles);

    // ---------------------------------------------------------------------------
    // 6. BANNER-SCOPED SCROLL & INTERSECTION OBSERVER
    // ---------------------------------------------------------------------------
    function onScroll() {
      var scrollY = window.pageYOffset || document.documentElement.scrollTop || 0;
      var bannerHeight = heroBanner ? heroBanner.offsetHeight : window.innerHeight;
      targetScrollProgress = Math.min(Math.max(scrollY / bannerHeight, 0), 1);
    }

    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    function onResize() {
      var size = getBannerSize();
      camera.aspect = size.width / size.height;
      camera.updateProjectionMatrix();
      renderer.setSize(size.width, size.height);
      renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, isMobile ? 1.0 : 1.25));
    }

    window.addEventListener('resize', onResize);

    document.addEventListener('visibilitychange', function () {
      if (document.hidden) {
        isRunning = false;
      } else if (isIntersecting) {
        isRunning = true;
        clock.start();
        animate();
      }
    });

    if ('IntersectionObserver' in window && heroBanner) {
      var bannerObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          isIntersecting = entry.isIntersecting;
          if (isIntersecting && !isRunning && !document.hidden) {
            isRunning = true;
            clock.start();
            animate();
          } else if (!isIntersecting) {
            isRunning = false;
          }
        });
      }, { threshold: 0.05 });
      bannerObserver.observe(heroBanner);
    }

    // ---------------------------------------------------------------------------
    // 7. ANIMATION & RENDER LOOP (Locked 60fps)
    // ---------------------------------------------------------------------------
    function animate() {
      if (!isRunning) return;
      animationFrameId = requestAnimationFrame(animate);

      var delta = clock.getDelta();
      var elapsedTime = clock.getElapsedTime();

      // Update shader time uniform for smooth biological wing flexing
      if (falconShaderMaterial && falconShaderMaterial.uniforms) {
        falconShaderMaterial.uniforms.uTime.value = elapsedTime;
      }

      // Update GLTF animation mixer if active
      if (animationMixer) {
        animationMixer.update(delta);
      }

      // Damped scroll interpolation
      currentScrollProgress += (targetScrollProgress - currentScrollProgress) * 0.055;

      var cruiseDist = elapsedTime * options.speed;
      var scrollDist = currentScrollProgress * options.scrollMultiplier;
      flightZ = cruiseDist + scrollDist;

      // 1. Canyon Chunks Recycling
      var chunkLen = options.canyonChunkLength;
      var totalCorridorLength = chunkLen * options.chunkCount;

      for (var c = 0; c < canyonChunks.length; c++) {
        var chunk = canyonChunks[c];
        if (chunk.zStart > -flightZ + chunkLen * 1.5) {
          var newZ = chunk.zStart - totalCorridorLength;
          updateChunkGeometry(chunk, newZ);
        }
      }

      // 2. Falcon Position & Flight Kinematics (Centered in Open Sky Corridor, Shifted Up, Full Wingspan Visible)
      var falconWorldZ = -flightZ - 14.0;
      var diagonalCycle = (Math.sin(elapsedTime * 0.35) + 1.0) * 0.5; // 0.0 to 1.0 smooth cycle
      // Centered in the open sky corridor between left card (580px wide) and right orbit diagram (range: 3.4 to 5.2)
      var falconDiagX = 3.4 + diagonalCycle * 1.8;
      // Shifted slightly higher into the open sky gap (range: 0.6 to 3.4) so both wings are 100% visible
      var falconTargetY = 0.6 + diagonalCycle * 2.8 + Math.sin(elapsedTime * 1.0) * 0.3;

      falconGroup.position.set(falconDiagX, falconTargetY, falconWorldZ);

      // Dynamic 3/4 Flight Perspective: angled yaw, banking roll, and gentle pitch
      var bankAngle = -0.18 - Math.cos(elapsedTime * 0.35) * 0.12;
      falconGroup.rotation.z += (bankAngle - falconGroup.rotation.z) * 0.07;
      falconGroup.rotation.y = 0.22 - Math.cos(elapsedTime * 0.35) * 0.08;
      falconGroup.rotation.x = 0.05 + Math.sin(elapsedTime * 0.8) * 0.02;

      falconGroup.updateMatrixWorld(true);

      // 3. Wing-Tip Ribbon Light Trails Update
      leftWingTipMarker.getWorldPosition(tmpVecA);
      rightWingTipMarker.getWorldPosition(tmpVecB);

      tmpVecC.set(0, 1, 0).applyAxisAngle(new THREE.Vector3(0, 0, 1), falconGroup.rotation.z);

      leftTrailRibbon.update(tmpVecA, tmpVecC);
      rightTrailRibbon.update(tmpVecB, tmpVecC);

      // 4. Smooth Cinematic Camera Follow Journey
      var camChaseDist = 18.0 + (1.0 - currentScrollProgress) * 4.0;
      var camHeight = 2.2 + Math.sin(currentScrollProgress * Math.PI) * 1.8;
      var camLateralOffset = Math.sin(currentScrollProgress * Math.PI * 1.2) * 2.0;

      var targetCamX = 1.4 + (falconDiagX - 4.3) * 0.3 + camLateralOffset * 0.3;
      var targetCamY = falconTargetY + camHeight;
      var targetCamZ = falconWorldZ + camChaseDist;

      camera.position.x += (targetCamX - camera.position.x) * 0.07;
      camera.position.y += (targetCamY - camera.position.y) * 0.07;
      camera.position.z += (targetCamZ - camera.position.z) * 0.08;

      tmpVecA.set(falconDiagX * 0.85, falconTargetY + 0.3, falconWorldZ - 14.0);
      camera.lookAt(tmpVecA);
      camera.rotation.z += (bankAngle * 0.18);

      // 5. Canyon Dust Particles Recycling
      var pAttr = ambientParticles.geometry.attributes.position;
      var pTotal = pAttr.count;
      var camZ = camera.position.z;

      for (var p = 0; p < pTotal; p++) {
        var pZ = pAttr.getZ(p);
        if (pZ > camZ + 10.0) {
          pAttr.setZ(p, camZ - 260.0 - Math.random() * 80.0);
          pAttr.setX(p, getCanyonCenterX(pZ) + (Math.random() - 0.5) * options.canyonWidth * 0.85);
          pAttr.setY(p, -10 + Math.random() * 26);
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
      falconGroup: falconGroup,
      destroy: function () {
        isRunning = false;
        if (animationFrameId) cancelAnimationFrame(animationFrameId);
        window.removeEventListener('scroll', onScroll);
        window.removeEventListener('resize', onResize);
        if (renderer) renderer.dispose();
        window.VGPeregrineFlight.initialized = false;
        window.VGPeregrineFlight.instance = null;
      }
    };

    window.VGPeregrineFlight.initialized = true;
    window.VGPeregrineFlight.instance = instance;
    return instance;
  }

  // Auto-init on DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      if (document.querySelector('.vg-hero-section')) {
        initPeregrineFlight();
      }
    });
  } else {
    if (document.querySelector('.vg-hero-section')) {
      initPeregrineFlight();
    }
  }

})(window, document);
