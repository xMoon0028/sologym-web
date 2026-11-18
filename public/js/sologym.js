/**
 * SoloGym Promotional & Infographic Interactive Logic
 */

document.addEventListener('DOMContentLoaded', () => {
    initMascotSwitcher();
    initPlateCalculator();
    initNavyBodyComp();
    initCopyChecksum();
});

/* 1. Mascot State Switcher */
function initMascotSwitcher() {
    const tabs = document.querySelectorAll('.state-tab');
    const mascotImg = document.getElementById('mascot-display-img');
    const mascotQuote = document.getElementById('mascot-quote-text');
    const mascotTag = document.getElementById('mascot-state-tag');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            const imgSrc = tab.getAttribute('data-image');
            const quote = tab.getAttribute('data-quote');
            const tag = tab.getAttribute('data-tag');
            const color = tab.getAttribute('data-color') || '#00E676';

            if (mascotImg) {
                mascotImg.style.opacity = '0.4';
                setTimeout(() => {
                    mascotImg.src = imgSrc;
                    mascotImg.style.borderColor = color;
                    mascotImg.style.boxShadow = `0 0 30px ${color}40`;
                    mascotImg.style.opacity = '1';
                }, 150);
            }

            if (mascotQuote) {
                mascotQuote.textContent = `“${quote}”`;
                mascotQuote.parentElement.style.borderLeftColor = color;
            }

            if (mascotTag) {
                mascotTag.textContent = tag;
                mascotTag.style.color = color;
                mascotTag.style.backgroundColor = `${color}20`;
            }
        });
    });
}

/* 2. Interactive Olympic Plate Calculator */
function initPlateCalculator() {
    const targetInput = document.getElementById('plate-target-weight');
    const barSelect = document.getElementById('plate-barbell-select');
    const unitSelect = document.getElementById('plate-unit-select');
    const calcBtn = document.getElementById('plate-calc-btn');

    if (!targetInput || !barSelect || !calcBtn) return;

    const runCalculation = () => {
        const target = parseFloat(targetInput.value) || 0;
        const barbell = parseFloat(barSelect.value) || 20;
        const unit = unitSelect ? unitSelect.value : 'kg';

        fetch(`/api/calculate-plates?weight=${target}&barbell=${barbell}&unit=${unit}`)
            .then(res => res.json())
            .then(data => {
                renderPlates(data);
            })
            .catch(() => {
                // Fallback local calc
                renderLocalPlates(target, barbell, unit);
            });
    };

    calcBtn.addEventListener('click', runCalculation);
    targetInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') runCalculation();
    });
    barSelect.addEventListener('change', runCalculation);
    if (unitSelect) unitSelect.addEventListener('change', runCalculation);

    // Initial run
    runCalculation();
}

function renderPlates(data) {
    const assembly = document.getElementById('barbell-plate-assembly');
    const summaryLoaded = document.getElementById('calc-total-loaded');
    const summaryPerSide = document.getElementById('calc-per-side');
    const summaryPlates = document.getElementById('calc-plates-desc');

    if (!assembly) return;

    assembly.innerHTML = `
        <div class="barbell-shaft" title="Barbell Shaft"></div>
        <div class="barbell-collar" title="Olympic Collar"></div>
    `;

    if (!data.success) {
        if (summaryLoaded) summaryLoaded.textContent = `${data.barbell} ${data.unit}`;
        if (summaryPerSide) summaryPerSide.textContent = `0 ${data.unit} / side`;
        if (summaryPlates) summaryPlates.textContent = data.message;
        return;
    }

    const plates = data.platesPerSide || [];
    let plateDescriptions = [];

    // Visual heights corresponding to weights
    const heightMap = {
        25: 110,
        20: 102,
        15: 90,
        10: 76,
        5: 60,
        2.5: 48,
        1.25: 38,
        // lbs
        45: 110,
        35: 98,
        // fallback
    };

    plates.forEach(p => {
        plateDescriptions.push(`${p.count}x ${p.weight}${data.unit}`);
        for (let i = 0; i < p.count; i++) {
            const disc = document.createElement('div');
            disc.className = 'plate-disc';
            const height = heightMap[p.weight] || Math.max(34, Math.min(110, p.weight * 4.2));
            disc.style.height = `${height}px`;
            disc.style.width = '20px';
            disc.style.backgroundColor = p.color;
            disc.style.color = p.textDark ? '#000000' : '#FFFFFF';
            disc.title = `${p.weight} ${data.unit}`;
            disc.textContent = p.weight >= 10 ? p.weight : p.weight;
            assembly.appendChild(disc);
        }
    });

    if (summaryLoaded) summaryLoaded.textContent = `${data.totalLoaded} ${data.unit}`;
    if (summaryPerSide) {
        const sideWeight = (data.totalLoaded - data.barbell) / 2;
        summaryPerSide.textContent = `${sideWeight} ${data.unit} / side`;
    }
    if (summaryPlates) {
        summaryPlates.textContent = plates.length > 0
            ? plateDescriptions.join(' + ') + ' per side'
            : 'Bar only (no plates needed)';
    }
}

function renderLocalPlates(target, barbell, unit) {
    const diff = target - barbell;
    if (diff <= 0) {
        renderPlates({
            success: true,
            totalLoaded: barbell,
            barbell: barbell,
            unit: unit,
            platesPerSide: []
        });
        return;
    }
    const perSide = diff / 2;
    const denoms = [25, 20, 15, 10, 5, 2.5, 1.25];
    const colors = {
        25: '#EF4444', 20: '#3B82F6', 15: '#EAB308',
        10: '#22C55E', 5: '#F3F4F6', 2.5: '#1F2937', 1.25: '#9CA3AF'
    };
    let remaining = perSide;
    let list = [];
    denoms.forEach(d => {
        let cnt = Math.floor(remaining / d);
        if (cnt > 0) {
            list.push({ weight: d, count: cnt, color: colors[d], textDark: d === 5 || d === 1.25 });
            remaining -= cnt * d;
        }
    });
    renderPlates({
        success: true,
        totalLoaded: target - (remaining * 2),
        barbell: barbell,
        unit: unit,
        platesPerSide: list
    });
}

/* 3. Interactive Navy Body Comp Live Simulator */
function initNavyBodyComp() {
    const heightRange = document.getElementById('navy-height');
    const weightRange = document.getElementById('navy-weight');
    const waistRange = document.getElementById('navy-waist');
    const neckRange = document.getElementById('navy-neck');
    const genderSelect = document.getElementById('navy-gender');

    const bfVal = document.getElementById('res-bf-val');
    const leanVal = document.getElementById('res-lean-val');
    const fatVal = document.getElementById('res-fat-val');
    const catVal = document.getElementById('res-cat-val');

    if (!heightRange || !waistRange) return;

    const updateComp = () => {
        const height = parseFloat(heightRange.value);
        const weight = parseFloat(weightRange.value);
        const waist = parseFloat(waistRange.value);
        const neck = parseFloat(neckRange.value);
        const gender = genderSelect ? genderSelect.value : 'male';

        document.getElementById('lbl-height').textContent = `${height} cm`;
        document.getElementById('lbl-weight').textContent = `${weight} kg`;
        document.getElementById('lbl-waist').textContent = `${waist} cm`;
        document.getElementById('lbl-neck').textContent = `${neck} cm`;

        let denom = waist - neck;
        if (denom <= 0) denom = 1;

        let bf = 495 / (1.0324 - 0.19077 * Math.log10(denom) + 0.15456 * Math.log10(height)) - 450;
        bf = Math.max(4.0, Math.min(48.0, Math.round(bf * 10) / 10));

        const fatMass = Math.round(weight * (bf / 100) * 10) / 10;
        const leanMass = Math.round((weight - fatMass) * 10) / 10;

        let cat = 'Fitness (Optimal)';
        if (bf < 6) cat = 'Essential Fat';
        else if (bf < 14) cat = 'Athletic';
        else if (bf < 18) cat = 'Fitness (Optimal)';
        else if (bf < 25) cat = 'Average';
        else cat = 'Overweight';

        if (bfVal) bfVal.textContent = `${bf}%`;
        if (leanVal) leanVal.textContent = `${leanMass} kg`;
        if (fatVal) fatVal.textContent = `${fatMass} kg`;
        if (catVal) catVal.textContent = cat;
    };

    [heightRange, weightRange, waistRange, neckRange, genderSelect].forEach(el => {
        if (el) el.addEventListener('input', updateComp);
    });

    updateComp();
}

/* 4. Copy SHA256 Checksum */
function initCopyChecksum() {
    const copyBtn = document.getElementById('btn-copy-checksum');
    if (!copyBtn) return;

    copyBtn.addEventListener('click', () => {
        const hash = copyBtn.getAttribute('data-hash');
        if (navigator.clipboard) {
            navigator.clipboard.writeText(hash).then(() => {
                const originalText = copyBtn.innerHTML;
                copyBtn.innerHTML = '✓ Copied!';
                copyBtn.style.color = '#00E676';
                setTimeout(() => {
                    copyBtn.innerHTML = originalText;
                    copyBtn.style.color = '';
                }, 2000);
            });
        }
    });
}
