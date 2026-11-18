<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SoloGym • Science-Backed Offline-First Gym Tracker & Trainer Mascot</title>
    <meta name="description" content="SoloGym is a 100% free, offline-first gym tracking application with an adaptive gym trainer mascot, 3-tier exercise safety hierarchy, calibrated Olympic plate calculator, and Navy body composition analytics.">
    <link rel="icon" type="image/png" href="{{ asset('assets/images/app_logo.png') }}">
    <link rel="stylesheet" href="{{ asset('css/sologym.css') }}">
</head>
<body>

    <!-- Navigation Header -->
    <header class="navbar">
        <div class="container nav-inner">
            <a href="#" class="brand">
                <img src="{{ asset('assets/images/app_logo.png') }}" alt="SoloGym Logo" class="brand-icon">
                <span class="brand-name">SoloGym</span>
            </a>

            <ul class="nav-links">
                <li><a href="#mascot">Trainer Mascot</a></li>
                <li><a href="#hierarchy">Safety Hierarchy</a></li>
                <li><a href="#plate-calc">Plate Calculator</a></li>
                <li><a href="#body-comp">Navy Body Comp</a></li>
                <li><a href="#literature">Literature</a></li>
                <li><a href="#comparison">Compare</a></li>
            </ul>

            <a href="{{ route('download.apk') }}" class="btn-cta">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM17 13l-5 5-5-5h3V9h4v4h3z"/>
                </svg>
                <span>Download APK</span>
            </a>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container hero-grid">
            <div class="hero-content">
                <div class="hero-badge">
                    <span class="pulse-dot"></span>
                    <span>v1.0.0 Release • 100% Free & Offline-First</span>
                </div>

                <h1 class="hero-title">
                    The Science-Backed Gym Tracker That Puts <span class="highlight">Safety & Progression</span> First.
                </h1>

                <p class="hero-description">
                    Say goodbye to forced cloud subscriptions, ads, and random routines. SoloGym delivers a dynamic gym trainer mascot, 3-tier injury-preventative exercise progressions, calibrated Olympic plate math, and tape-measure body recomposition—all 100% offline in local SQLite.
                </p>

                <div class="hero-cta-group">
                    <a href="{{ route('download.apk') }}" class="btn-cta" style="padding: 14px 26px; font-size: 16px;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM17 13l-5 5-5-5h3V9h4v4h3z"/>
                        </svg>
                        <span>Download APK ({{ $apkSize }})</span>
                    </a>
                    <a href="#mascot" class="btn-secondary" style="padding: 14px 22px; font-size: 15px;">
                        <span>Explore Interactive Infographics</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M7 13l5 5 5-5M7 6l5 5 5-5"/></svg>
                    </a>
                </div>

                <div class="apk-info-strip">
                    <span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                        Android 8.0+ (ARM64 & x86_64)
                    </span>
                    <span>•</span>
                    <span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        Zero Permissions Required
                    </span>
                    <span>•</span>
                    <span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        {{ number_format($downloadCount) }}+ Downloads
                    </span>
                </div>
            </div>

            <!-- Interactive Phone Mockup Showcase -->
            <div class="mockup-container">
                <div class="phone-mockup">
                    <div class="phone-screen">
                        <div class="screen-header">
                            <div class="screen-brand">
                                <img src="{{ asset('assets/images/app_logo.png') }}" style="width:22px;height:22px;border-radius:4px;" alt="">
                                <span>SoloGym</span>
                            </div>
                            <span style="font-size: 11px; color: var(--neon-green); font-weight: bold; background: rgba(0,230,118,0.15); padding: 2px 8px; border-radius: 4px;">OFFLINE</span>
                        </div>

                        <!-- Mascot Card inside Mockup -->
                        <div class="mascot-hero-card">
                            <img src="{{ asset('assets/images/mascot/mascot_welcoming.jpg') }}" alt="Coach Rex" class="mascot-hero-img">
                            <div style="font-weight: 800; font-size: 15px; color: #FFF; margin-bottom: 2px;">Coach Rex 🔥</div>
                            <div style="font-size: 11px; color: var(--neon-green); font-weight: 600; margin-bottom: 6px;">BEAST MODE • LEVEL 3</div>
                            <div style="font-size: 12px; color: var(--text-secondary); font-style: italic; line-height: 1.3;">
                                “Welcome back! Ready to set a new PR on chest-supported rows today?”
                            </div>
                        </div>

                        <!-- Metrics Row -->
                        <div class="screen-metric-row">
                            <div class="metric-pill">
                                <div class="label">Streak</div>
                                <div class="val">14 Days</div>
                            </div>
                            <div class="metric-pill">
                                <div class="label">Navy BF%</div>
                                <div class="val">14.8%</div>
                            </div>
                            <div class="metric-pill">
                                <div class="label">Tier Focus</div>
                                <div class="val" style="color:var(--neon-cyan)">Tier 1</div>
                            </div>
                        </div>

                        <!-- Exercise Preview Row -->
                        <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 12px; padding: 10px; display: flex; align-items: center; justify-content: space-between;">
                            <div>
                                <div style="font-size: 12px; font-weight: bold; color: #FFF;">Chest-Supported Row</div>
                                <div style="font-size: 10px; color: var(--neon-green);">Tier 1: Machine • 3x 10-12 reps</div>
                            </div>
                            <div style="font-size: 13px; font-weight: 800; color: var(--neon-cyan);">60 kg</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 4-Pillar Infographic Strip -->
    <section class="pillars">
        <div class="container pillars-grid">
            <div class="pillar-card">
                <div class="pillar-icon" style="color: var(--neon-green);">🔒</div>
                <div>
                    <div class="pillar-title">100% Offline & Zero Ads</div>
                    <div class="pillar-desc">No login, no cloud outages, no telemetry. Your logs and biometrics stay on your device in local SQLite.</div>
                </div>
            </div>

            <div class="pillar-card">
                <div class="pillar-icon" style="color: var(--neon-cyan);">🛡️</div>
                <div>
                    <div class="pillar-title">3-Tier Safety Hierarchy</div>
                    <div class="pillar-desc">Machine-supported volume first. Eliminate axial spinal compression for beginners before heavy loading.</div>
                </div>
            </div>

            <div class="pillar-card">
                <div class="pillar-icon" style="color: var(--warning);">⚡</div>
                <div>
                    <div class="pillar-title">Adaptive Mascot Coach</div>
                    <div class="pillar-desc">Dynamic personality with 6 emotional states that cheer PRs, time rest intervals, and analyze trends.</div>
                </div>
            </div>

            <div class="pillar-card">
                <div class="pillar-icon" style="color: var(--purple);">📐</div>
                <div>
                    <div class="pillar-title">Calibrated Olympic Math</div>
                    <div class="pillar-desc">Exact color-coded plate loading diagrams and US Navy tape-measure circumference calculations.</div>
                </div>
            </div>
        </div>
    </section>

    <!-- INFOGRAPHIC 1: Adaptive Trainer Mascot -->
    <section id="mascot" class="section">
        <div class="container">
            <span class="section-tag">Interactive Infographic • Coach System</span>
            <h2 class="section-title">An Emotion-Aware Trainer in Your Pocket</h2>
            <p class="section-subtitle">
                Most gym apps are lifeless spreadsheets. SoloGym features an adaptive mascot trainer that visually updates its expression and verbal cues based on your session progress, rest timer, and progressive overload breakthroughs.
            </p>

            <div class="mascot-showcase-card">
                <div class="mascot-avatar-view">
                    <img id="mascot-display-img" src="{{ asset('assets/images/mascot/mascot_welcoming.jpg') }}" alt="Mascot Persona" class="mascot-large-img">
                    <div>
                        <span id="mascot-state-tag" class="mascot-badge-tag">App Launch Greeting</span>
                    </div>
                </div>

                <div class="mascot-content-view">
                    <div style="font-size: 13px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 10px;">
                        Interactive Emotion Simulator (Click to preview states):
                    </div>

                    <div class="mascot-state-tabs">
                        @foreach ($mascotStates as $idx => $m)
                            <button 
                                class="state-tab {{ $idx === 0 ? 'active' : '' }}"
                                data-image="{{ asset($m['image']) }}"
                                data-quote="{{ $m['quote'] }}"
                                data-tag="{{ $m['tag'] }}"
                                data-color="{{ $m['color'] }}"
                            >
                                {{ $m['name'] }}
                            </button>
                        @endforeach
                    </div>

                    <div class="mascot-quote-box">
                        <span id="mascot-quote-text">“Welcome back to the gym! Your form is your shield today. Let’s make every single rep count!”</span>
                    </div>

                    <div class="mascot-stats-grid">
                        <div class="m-stat">
                            <div class="st-label">Personality Modes</div>
                            <div class="st-val">Rex, Kai, Chloe</div>
                        </div>
                        <div class="m-stat">
                            <div class="st-label">Dynamic States</div>
                            <div class="st-val">6 Adaptive Cues</div>
                        </div>
                        <div class="m-stat">
                            <div class="st-label">Audio & Haptics</div>
                            <div class="st-val">Smart Rest Beeps</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- INFOGRAPHIC 2: 3-Tier Exercise Hierarchy -->
    <section id="hierarchy" class="section" style="background: rgba(18, 24, 32, 0.4); border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color);">
        <div class="container">
            <span class="section-tag">Scientific Methodology • Injury Prevention</span>
            <h2 class="section-title">The 3-Tier Exercise Safety Hierarchy</h2>
            <p class="section-subtitle">
                Rooted in the peer-reviewed biomechanics of Dr. Brad Schoenfeld and Dr. Eric Helms. Rather than throwing beginners under heavy axial loads, SoloGym scaffolds your movements across three progressive stability tiers.
            </p>

            <div class="tier-cards-grid">
                <!-- Tier 1 -->
                <div class="tier-card tier-1">
                    <div class="tier-badge">TIER 1 • MAX SAFETY</div>
                    <div class="tier-title">Machine & Cable Support</div>
                    <p class="tier-desc">
                        Fixed movement planes that virtually eliminate lumbar shear forces and joint instability. Delivers maximum targeted tension directly onto the working muscle fibers with near-zero injury risk.
                    </p>
                    <ul class="tier-exercises">
                        <li>Chest-Supported Machine / T-Bar Row</li>
                        <li>Cable Lateral Raise (Continuous tension)</li>
                        <li>Leg Press & Machine Seated Calf Raise</li>
                        <li>Lat Pulldown & Seated Cable Row</li>
                        <li>Cable Woodchopper / Pallof Press</li>
                    </ul>
                </div>

                <!-- Tier 2 -->
                <div class="tier-card tier-2">
                    <div class="tier-badge">TIER 2 • MODERATE STABILITY</div>
                    <div class="tier-title">Dumbbells & Supported Benches</div>
                    <p class="tier-desc">
                        Introduces three-dimensional stabilizer recruitment and unilateral symmetry. If fatigue hits, dumbbells can be safely dropped without trapping the lifter.
                    </p>
                    <ul class="tier-exercises">
                        <li>Dumbbell Goblet Squat (Upright torso)</li>
                        <li>Flat / Incline Dumbbell Bench Press</li>
                        <li>Dumbbell Romanian Deadlift (Hinge motor pattern)</li>
                        <li>Dumbbell Walking Lunges</li>
                        <li>Dumbbell Seated Overhead Press</li>
                    </ul>
                </div>

                <!-- Tier 3 -->
                <div class="tier-card tier-3">
                    <div class="tier-badge">TIER 3 • ADVANCED AXIAL</div>
                    <div class="tier-title">Free Olympic Barbells</div>
                    <p class="tier-desc">
                        Demands complex kinetic bracing, core intra-abdominal pressure, and strict neural control. Recommended only once Tier 1 & 2 movement competence is thoroughly established.
                    </p>
                    <ul class="tier-exercises">
                        <li>Barbell Back Squat (High bar / Low bar)</li>
                        <li>Conventional & Sumo Barbell Deadlift</li>
                        <li>Barbell Overhead Press (Military)</li>
                        <li>Barbell Bent-Over Row</li>
                        <li>Barbell Flat Bench Press</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- INFOGRAPHIC 3: Interactive Olympic Plate Calculator -->
    <section id="plate-calc" class="section">
        <div class="container">
            <span class="section-tag">Interactive Tool • Barbell Math</span>
            <h2 class="section-title">Standard Olympic Plate & Machine Math</h2>
            <p class="section-subtitle">
                Never do gym math in your head again. SoloGym calculates calibrated Olympic plates per sleeve according to standard color-coded specifications (Red 25kg, Blue 20kg, Yellow 15kg, Green 10kg, White 5kg, Black 2.5kg, Chrome 1.25kg).
            </p>

            <div class="calc-wrapper">
                <div class="calc-controls">
                    <div class="input-group">
                        <label class="input-label" for="plate-target-weight">Target Weight</label>
                        <input type="number" id="plate-target-weight" class="input-field" value="80" step="2.5" min="10" max="400">
                    </div>

                    <div class="input-group">
                        <label class="input-label" for="plate-barbell-select">Barbell Default</label>
                        <select id="plate-barbell-select" class="input-field">
                            <option value="20" selected>20 kg (Olympic Men's Standard)</option>
                            <option value="15">15 kg (Olympic Women's Standard)</option>
                            <option value="10">10 kg (Technique / Junior Bar)</option>
                        </select>
                    </div>

                    <div class="input-group">
                        <label class="input-label" for="plate-unit-select">Weight Unit</label>
                        <select id="plate-unit-select" class="input-field">
                            <option value="kg" selected>Kilograms (KG)</option>
                            <option value="lbs">Pounds (LBS)</option>
                        </select>
                    </div>

                    <button id="plate-calc-btn" class="btn-cta" style="height: 44px;">
                        <span>Calculate Plates</span>
                    </button>
                </div>

                <!-- Real-time Barbell Visualizer -->
                <div class="barbell-visualizer">
                    <div class="barbell-assembly" id="barbell-plate-assembly">
                        <!-- Rendered dynamically by JS -->
                    </div>
                </div>

                <!-- Summary Pills -->
                <div class="calc-summary-pills">
                    <div style="display: flex; gap: 10px;">
                        <span class="m-stat" style="display:inline-flex; align-items:center; gap:8px;">
                            <span class="st-label">Total Loaded:</span>
                            <span class="st-val" id="calc-total-loaded" style="color:var(--neon-green)">80 kg</span>
                        </span>
                        <span class="m-stat" style="display:inline-flex; align-items:center; gap:8px;">
                            <span class="st-label">Per Side:</span>
                            <span class="st-val" id="calc-per-side" style="color:var(--neon-cyan)">30 kg / side</span>
                        </span>
                    </div>

                    <div style="font-size: 13px; color: var(--text-secondary);" id="calc-plates-desc">
                        Loading: 1x 20kg + 1x 10kg per side
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- INFOGRAPHIC 4: Navy Body Composition Formula -->
    <section id="body-comp" class="section" style="background: rgba(18, 24, 32, 0.4); border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color);">
        <div class="container">
            <span class="section-tag">Biometric Analytics • Pure Recomposition</span>
            <h2 class="section-title">Beyond the Bathroom Scale: US Navy Tape Formula</h2>
            <p class="section-subtitle">
                Scale weight alone cannot distinguish muscle gain from fat gain or water retention. SoloGym utilizes the standardized US Navy Circumference Method to track your true Lean Mass vs Fat Mass over time.
            </p>

            <div class="calc-wrapper" style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">
                <div>
                    <h3 style="font-size: 18px; margin-bottom: 20px;">Interactive Tape Measurements:</h3>

                    <div class="input-group" style="margin-bottom: 16px;">
                        <div style="display:flex; justify-content:space-between;">
                            <label class="input-label">Height (cm):</label>
                            <span id="lbl-height" style="color:var(--neon-green); font-weight:bold;">175 cm</span>
                        </div>
                        <input type="range" id="navy-height" min="140" max="210" value="175" style="width:100%; accent-color:var(--neon-green);">
                    </div>

                    <div class="input-group" style="margin-bottom: 16px;">
                        <div style="display:flex; justify-content:space-between;">
                            <label class="input-label">Body Weight (kg):</label>
                            <span id="lbl-weight" style="color:var(--neon-green); font-weight:bold;">75 kg</span>
                        </div>
                        <input type="range" id="navy-weight" min="45" max="150" value="75" style="width:100%; accent-color:var(--neon-green);">
                    </div>

                    <div class="input-group" style="margin-bottom: 16px;">
                        <div style="display:flex; justify-content:space-between;">
                            <label class="input-label">Waist Circumference at Navel (cm):</label>
                            <span id="lbl-waist" style="color:var(--neon-cyan); font-weight:bold;">82 cm</span>
                        </div>
                        <input type="range" id="navy-waist" min="60" max="130" value="82" style="width:100%; accent-color:var(--neon-cyan);">
                    </div>

                    <div class="input-group" style="margin-bottom: 16px;">
                        <div style="display:flex; justify-content:space-between;">
                            <label class="input-label">Neck Circumference (cm):</label>
                            <span id="lbl-neck" style="color:var(--neon-cyan); font-weight:bold;">38 cm</span>
                        </div>
                        <input type="range" id="navy-neck" min="28" max="55" value="38" style="width:100%; accent-color:var(--neon-cyan);">
                    </div>
                </div>

                <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 28px; display: flex; flex-direction: column; justify-content: center;">
                    <div style="text-align: center; margin-bottom: 24px;">
                        <div style="font-size: 12px; color: var(--text-muted); text-transform: uppercase;">Estimated Body Fat Percentage</div>
                        <div id="res-bf-val" style="font-size: 54px; font-weight: 900; color: var(--neon-green); line-height: 1.1;">14.8%</div>
                        <div id="res-cat-val" style="font-size: 14px; font-weight: bold; color: var(--neon-cyan); margin-top: 4px;">Fitness (Optimal)</div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                        <div class="m-stat" style="text-align: center;">
                            <div class="st-label">Lean Muscle Mass</div>
                            <div class="st-val" id="res-lean-val" style="color:var(--neon-green)">63.9 kg</div>
                        </div>
                        <div class="m-stat" style="text-align: center;">
                            <div class="st-label">Fat Mass</div>
                            <div class="st-val" id="res-fat-val" style="color:var(--warning)">11.1 kg</div>
                        </div>
                    </div>

                    <div style="margin-top: 20px; font-size: 12px; color: var(--text-muted); line-height: 1.4; text-align: center;">
                        Formula: 495 / (1.0324 - 0.19077 × log₁₀(Waist - Neck) + 0.15456 × log₁₀(Height)) - 450
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- INFOGRAPHIC 5: Consulted Scientific Literature -->
    <section id="literature" class="section">
        <div class="container">
            <span class="section-tag">Evidence-Based Principles • Peer Reviewed</span>
            <h2 class="section-title">Professional Literature & Frameworks Consulted</h2>
            <p class="section-subtitle">
                SoloGym isn't built on gym lore or influencer fads. Every rest timer interval, volume guideline, and exercise tier is directly derived from published exercise science literature.
            </p>

            <div class="lit-grid">
                @foreach ($literature as $item)
                    <div class="lit-card">
                        <span class="tier-badge" style="background: rgba(0, 229, 255, 0.12); color: var(--neon-cyan); margin-bottom: 8px;">
                            {{ $item['badge'] }}
                        </span>
                        <div class="lit-author">{{ $item['author'] }}</div>
                        <div class="lit-title">{{ $item['title'] }}</div>
                        <p class="lit-desc">{{ $item['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- INFOGRAPHIC 6: SoloGym vs Subscription Apps -->
    <section id="comparison" class="section" style="background: rgba(18, 24, 32, 0.4); border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color);">
        <div class="container">
            <span class="section-tag">Value Comparison • Fair Fitness</span>
            <h2 class="section-title">SoloGym vs. Traditional Gym Apps</h2>
            <p class="section-subtitle">
                Why pay $80 every single year just to record sets on your own phone? Here is how SoloGym compares against typical mainstream workout trackers.
            </p>

            <div class="comparison-table-wrapper">
                <table class="comparison-table">
                    <thead>
                        <tr>
                            <th>Feature</th>
                            <th class="highlight-col">SoloGym (v1.0.0)</th>
                            <th>Hevy / Strong</th>
                            <th>FitNotes / Jefit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="feature-col">Cost & Subscription</td>
                            <td class="highlight-col">100% Free Forever (Open)</td>
                            <td>$59 - $79 / Year Paywall</td>
                            <td>Freemium with Ads</td>
                        </tr>
                        <tr>
                            <td class="feature-col">Data Privacy & Storage</td>
                            <td class="highlight-col">Local SQLite (No Account Needed)</td>
                            <td>Mandatory Cloud Login & Telemetry</td>
                            <td>Cloud Account Required</td>
                        </tr>
                        <tr>
                            <td class="feature-col">Adaptive Trainer Mascot</td>
                            <td class="highlight-col">Yes (6 Emotion States & Voice Cues)</td>
                            <td>None</td>
                            <td>None</td>
                        </tr>
                        <tr>
                            <td class="feature-col">Safety Exercise Hierarchy</td>
                            <td class="highlight-col">3-Tier Joint Stability Categorization</td>
                            <td>Flat Uncurated Alphabetical List</td>
                            <td>Basic Muscle Filters</td>
                        </tr>
                        <tr>
                            <td class="feature-col">Smart Rest Timers</td>
                            <td class="highlight-col">Schoenfeld Hypertrophy RPE Curves</td>
                            <td>Static Timer</td>
                            <td>Basic Countdown</td>
                        </tr>
                        <tr>
                            <td class="feature-col">Body Recomposition</td>
                            <td class="highlight-col">US Navy Tape Circumference Math</td>
                            <td>Scale Weight Only</td>
                            <td>Scale Weight Only</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- DOWNLOAD HUB SECTION -->
    <section class="section">
        <div class="container">
            <div class="download-hub">
                <div class="hero-badge" style="margin-bottom: 16px;">
                    <span class="pulse-dot"></span>
                    <span>Direct APK Download • Verified Package</span>
                </div>

                <h2 class="hub-title">Ready to Elevate Your Training?</h2>
                <p class="hub-desc">
                    Download SoloGym APK directly to your Android device. No Google account, no tracking cookies, and no recurring fees. Just pure gym progress.
                </p>

                <div style="display:flex; justify-content:center; gap:16px; flex-wrap:wrap; margin-bottom: 24px;">
                    <a href="{{ route('download.apk') }}" class="btn-cta" style="padding: 16px 36px; font-size: 18px;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM17 13l-5 5-5-5h3V9h4v4h3z"/>
                        </svg>
                        <span>Download SoloGym-v1.0.0.apk ({{ $apkSize }})</span>
                    </a>
                </div>

                <!-- SHA256 Checksum -->
                <div class="checksum-box">
                    <span>SHA-256: {{ substr($apkSha256, 0, 16) }}...{{ substr($apkSha256, -16) }}</span>
                    <button id="btn-copy-checksum" data-hash="{{ $apkSha256 }}" style="background:none; border:none; color:var(--neon-green); cursor:pointer; font-weight:bold; margin-left:8px;">
                        Copy Full Hash
                    </button>
                </div>

                <!-- 3-Step Install Guide -->
                <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:16px; max-width:800px; margin:40px auto 0; text-align:left;">
                    <div style="background:var(--bg-surface); padding:16px; border-radius:12px; border:1px solid var(--border-color);">
                        <div style="font-weight:bold; color:var(--neon-green); font-size:14px; margin-bottom:4px;">1. Download</div>
                        <div style="font-size:12px; color:var(--text-secondary);">Tap the download button above to save the APK to your Android downloads folder.</div>
                    </div>
                    <div style="background:var(--bg-surface); padding:16px; border-radius:12px; border:1px solid var(--border-color);">
                        <div style="font-weight:bold; color:var(--neon-cyan); font-size:14px; margin-bottom:4px;">2. Allow Install</div>
                        <div style="font-size:12px; color:var(--text-secondary);">If prompted by Android, tap "Settings" and enable "Allow from this source".</div>
                    </div>
                    <div style="background:var(--bg-surface); padding:16px; border-radius:12px; border:1px solid var(--border-color);">
                        <div style="font-weight:bold; color:var(--warning); font-size:14px; margin-bottom:4px;">3. Train & Track</div>
                        <div style="font-size:12px; color:var(--text-secondary);">Open SoloGym, meet Coach Rex, and generate your 100% offline workout routine!</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-links">
                <a href="#mascot">Trainer Mascot</a>
                <a href="#hierarchy">Safety Hierarchy</a>
                <a href="#plate-calc">Plate Math</a>
                <a href="#body-comp">Navy Biometrics</a>
                <a href="#literature">Literature Consulted</a>
                <a href="{{ route('download.apk') }}">Download APK</a>
            </div>

            <p style="margin-bottom: 8px;">
                SoloGym is a MoonDev open initiative designed for lifters who prioritize joint longevity, privacy, and evidence-based overload.
            </p>
            <p style="color: var(--text-muted); font-size: 11px;">
                &copy; {{ date('Y') }} SoloGym. Built with Laravel & Flutter. 100% Offline-First.
            </p>
        </div>
    </footer>

    <script src="{{ asset('js/sologym.js') }}"></script>
</body>
</html>
