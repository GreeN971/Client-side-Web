// Analytics Page JavaScript
// WEEKLY_DATA and CALENDAR_DATA are injected by analytics.php as inline <script> vars.

const EMOTIONS = [
    { id: 'joy',      name: 'Joy',      color: '#facc15', bgColor: '#fef9c3' },
    { id: 'sadness',  name: 'Sadness',  color: '#60a5fa', bgColor: '#dbeafe' },
    { id: 'anger',    name: 'Anger',    color: '#ef4444', bgColor: '#fee2e2' },
    { id: 'calmness', name: 'Calmness', color: '#34d399', bgColor: '#d1fae5' },
    { id: 'neutral',  name: 'Neutral',  color: '#d5c7b4', bgColor: 'rgba(213,206,180,0.5)' },
    { id: 'anxiety',  name: 'Anxiety',  color: '#c084fc', bgColor: '#f3e8ff' }
];

// ── Helpers ───────────────────────────────────────────────────────────────────

function formatDateForDisplay(dateObj) {
    const months = ['January','February','March','April','May','June',
                    'July','August','September','October','November','December'];
    const day = dateObj.getDate();
    const suffix = (day >= 11 && day <= 13) ? 'th'
                 : ({1:'st',2:'nd',3:'rd'}[day % 10] || 'th');
    return `${months[dateObj.getMonth()]} ${day}${suffix}`;
}

function formatDateForBackend(date) {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
}

function getEmotionColorById(id) {
    const e = EMOTIONS.find(e => e.id === id);
    return e ? e.color : '#ccc';
}

// ── State ─────────────────────────────────────────────────────────────────────

let currentMonth    = new Date();
let rangeStart      = null;   // Date object — first click
let rangeEnd        = null;   // Date object — second click
let rangeSelecting  = false;  // true after first click, waiting for second
let selectedEmotion = null;
let checkinStep     = 1;

// ── Boot ──────────────────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', function () {
    initEmotionLegend();
    initCalendar();
    initWeeklyChart();
    initCheckinModal();
    initDetailsModal();
    initLogout();
});

// ── Logout ────────────────────────────────────────────────────────────────────

function initLogout() {
    const btn = document.getElementById('logoutBtn');
    if (btn) {
        btn.addEventListener('click', function () {
            window.location.href = 'includes/logout.inc.php';
        });
    }
}

// ── Emotion helpers ───────────────────────────────────────────────────────────

function getEmotionColor(name) {
    const e = EMOTIONS.find(e => e.name.toLowerCase() === name.toLowerCase());
    return e ? e.color : '#ccc';
}

// ── Emotion legend ────────────────────────────────────────────────────────────

function initEmotionLegend() {
    const legendContainer = document.getElementById('emotionLegend');
    if (!legendContainer) return;
    
    legendContainer.innerHTML = EMOTIONS.map(emotion => `
        <div class="legend-item">
            <span class="legend-dot" style="background-color: ${emotion.color}"></span>
            <span class="legend-label">${emotion.name}</span>
        </div>
    `).join('');
}

// ── Calendar ──────────────────────────────────────────────────────────────────
// CALENDAR_DATA shape (from PHP): { "YYYY-MM-DD": ["joy","calmness", …], … }
// Range selection: first click sets rangeStart, second click sets rangeEnd.
// Constraints: max 365 days. Submit navigates to ?from=...&to=...

function initCalendar() {
    // Pre-fill from PHP-injected range so the calendar reflects the active range on load
    if (typeof RANGE_FROM !== 'undefined' && typeof IS_DEFAULT !== 'undefined' && !IS_DEFAULT) {
        rangeStart = parseDateStr(RANGE_FROM);
        rangeEnd   = parseDateStr(RANGE_TO);
    }

    updateCalendarMonth();

    document.getElementById('prevMonth').addEventListener('click', function () {
        currentMonth.setMonth(currentMonth.getMonth() - 1);
        updateCalendarMonth();
    });

    document.getElementById('nextMonth').addEventListener('click', function () {
        currentMonth.setMonth(currentMonth.getMonth() + 1);
        updateCalendarMonth();
    });

    document.getElementById('calendarSubmitBtn').addEventListener('click', submitCalendarRange);
    document.getElementById('clearRangeBtn').addEventListener('click', clearRange);

    updateRangeDisplay();
    updateCalendarSubmitButton();
}

function parseDateStr(str) {
    const [y, m, d] = str.split('-').map(Number);
    return new Date(y, m - 1, d);
}

/**
 * Update calendar month display
 */
function updateCalendarMonth() {
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                        'July', 'August', 'September', 'October', 'November', 'December'];
    
    document.getElementById('currentMonth').textContent = 
        `${monthNames[currentMonth.getMonth()]} ${currentMonth.getFullYear()}`;
    
    renderCalendarDays();
}

/**
 * Render calendar days — highlights range start, in-range, and end days
 */
function renderCalendarDays() {
    const container = document.getElementById('calendarDays');
    if (!container) return;

    const year  = currentMonth.getFullYear();
    const month = currentMonth.getMonth();
    const todayMs = new Date().setHours(0, 0, 0, 0);

    const firstDay    = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();

    let html = '';

    for (let i = 0; i < firstDay; i++) {
        html += '<button class="calendar-day empty" tabindex="-1" aria-hidden="true"></button>';
    }

    for (let day = 1; day <= daysInMonth; day++) {
        const dateStr = `${year}-${String(month + 1).padStart(2,'0')}-${String(day).padStart(2,'0')}`;
        const dateMs  = new Date(year, month, day).getTime();
        const isToday = dateMs === todayMs;

        let isStart   = false;
        let isEnd     = false;
        let isInRange = false;

        if (rangeStart) {
            const startMs = rangeStart.getTime();
            isStart = dateMs === startMs;

            if (rangeEnd) {
                const endMs = rangeEnd.getTime();
                isEnd    = dateMs === endMs;
                isInRange = dateMs > startMs && dateMs < endMs;
            }
        }

        const dayEmotions = CALENDAR_DATA[dateStr] || [];
        const dotColor    = dayEmotions.length > 0 ? getEmotionColor(dayEmotions[0]) : null;
        const dot = (dotColor && !isStart && !isEnd)
            ? `<span class="day-indicator" style="background-color:${dotColor}"></span>`
            : '';

        let classes = 'calendar-day';
        if (isToday)   classes += ' today';
        if (isStart)   classes += ' range-start';
        if (isEnd)     classes += ' range-end';
        if (isInRange) classes += ' in-range';

        html += `<button class="${classes}" data-date="${dateStr}">${day}${dot}</button>`;
    }

    container.innerHTML = html;

    container.querySelectorAll('.calendar-day:not(.empty)').forEach(btn => {
        btn.addEventListener('click', function () {
            handleDayClick(this.dataset.date);
        });
    });
}

function handleDayClick(dateStr) {
    const clicked = parseDateStr(dateStr);

    if (!rangeStart || (rangeStart && rangeEnd)) {
        // Start fresh: set start only
        rangeStart = clicked;
        rangeEnd   = null;
    } else {
        // Second click — finalise the range
        if (clicked.getTime() === rangeStart.getTime()) {
            // Same day = single-day range
            rangeEnd = clicked;
        } else {
            if (clicked < rangeStart) {
                rangeEnd   = rangeStart;
                rangeStart = clicked;
            } else {
                rangeEnd = clicked;
            }

            // Clamp to max 365 days
            const diffDays = Math.round((rangeEnd - rangeStart) / 86400000);
            if (diffDays > 365) {
                rangeEnd = new Date(rangeStart.getTime() + 365 * 86400000);
            }
        }
    }

    renderCalendarDays();
    updateRangeDisplay();
    updateCalendarSubmitButton();
}

function clearRange() {
    rangeStart = null;
    rangeEnd   = null;
    renderCalendarDays();
    updateRangeDisplay();
    updateCalendarSubmitButton();
}

function updateRangeDisplay() {
    const fromEl = document.getElementById('rangeFromDisplay');
    const toEl   = document.getElementById('rangeToDisplay');
    const hintEl = document.getElementById('rangeHint');

    if (!fromEl || !toEl) return;

    if (!rangeStart) {
        fromEl.textContent = '—';
        toEl.textContent   = '—';
        if (hintEl) hintEl.textContent = 'Click a day to set start date';
    } else if (!rangeEnd) {
        fromEl.textContent = formatDateShort(rangeStart);
        toEl.textContent   = '—';
        if (hintEl) hintEl.textContent = 'Now click an end date';
    } else {
        fromEl.textContent = formatDateShort(rangeStart);
        toEl.textContent   = formatDateShort(rangeEnd);
        const days = Math.round((rangeEnd - rangeStart) / 86400000) + 1;
        if (hintEl) hintEl.textContent = `${days} day${days !== 1 ? 's' : ''} selected`;
    }
}

function formatDateShort(dateObj) {
    const months = ['Jan','Feb','Mar','Apr','May','Jun',
                    'Jul','Aug','Sep','Oct','Nov','Dec'];
    return `${months[dateObj.getMonth()]} ${dateObj.getDate()}, ${dateObj.getFullYear()}`;
}

function updateCalendarSubmitButton() {
    const submitBtn = document.getElementById('calendarSubmitBtn');
    const clearBtn  = document.getElementById('clearRangeBtn');
    const hasRange  = rangeStart && rangeEnd;
    if (submitBtn) submitBtn.disabled = !hasRange;
    if (clearBtn)  clearBtn.disabled  = !rangeStart;
}

function submitCalendarRange() {
    if (!rangeStart || !rangeEnd) return;
    window.location.href =
        `analytics.php?from=${formatDateForBackend(rangeStart)}&to=${formatDateForBackend(rangeEnd)}`;
}

// ── Weekly chart ──────────────────────────────────────────────────────────────
// WEEKLY_DATA shape (from PHP):
//   [ { day: "Mon", date: "YYYY-MM-DD", checkins: [{emotion, note, created_at}, …] }, … ]

function initWeeklyChart() {
    const container = document.getElementById('weeklyChart');
    if (!container) return;

    // Set --bar-count so CSS can compute bar width and scroll width
    container.style.setProperty('--bar-count', WEEKLY_DATA.length);

    container.innerHTML = WEEKLY_DATA.map(dayData => {
        const checkins = dayData.checkins || [];

        if (checkins.length === 0) {
            return `<div class="bar-column"><div class="bar-stack empty-bar"></div><span class="bar-label">${dayData.day}</span></div>`;
        }

        const share    = 100 / checkins.length;
        const segments = checkins.map(c =>
            `<div class="bar-segment" style="height:${share}%;background-color:${getEmotionColor(c.emotion)}"></div>`
        ).join('');

        return `<div class="bar-column"><div class="bar-stack">${segments}</div><span class="bar-label">${dayData.day}</span></div>`;
    }).join('');
}

// Day entries and timeline entries are PHP-rendered; no JS needed for them.

// ── Check-in modal ────────────────────────────────────────────────────────────
// The modal contains a real <form> POSTing to includes/checkin.inc.php.
// JS handles emotion grid UI, step navigation, and writes the selected emotion
// id into the hidden <input name="emotion"> before the form submits.

function initCheckinModal() {
    const modal    = document.getElementById('checkinModal');
    const openBtn  = document.getElementById('startCheckin');
    const closeBtn = document.getElementById('closeCheckin');
    const backBtn  = document.getElementById('backBtn');
    const nextBtn  = document.getElementById('nextBtn');

    initEmotionGrid();

    openBtn.addEventListener('click', function () {
        modal.classList.add('active');
        resetCheckinModal();
    });

    closeBtn.addEventListener('click', function () {
        modal.classList.remove('active');
    });

    modal.addEventListener('click', function (e) {
        if (e.target === modal) modal.classList.remove('active');
    });

    backBtn.addEventListener('click', function () { showStep(1); });

    nextBtn.addEventListener('click', function () {
        if (selectedEmotion) showStep(2);
    });

    // Guard: ensure hidden emotion value is set before the form submits
    const form = document.getElementById('checkinForm');
    if (form) {
        form.addEventListener('submit', function (e) {
            const hiddenInput = document.getElementById('selectedEmotionInput');
            if (!hiddenInput || !hiddenInput.value) {
                e.preventDefault();
            }
        });
    }
}

/**
 * Initialize emotion selection grid
 */
function initEmotionGrid() {
    const grid = document.getElementById('emotionGrid');
    if (!grid) return;
    
    const emotionIcons = {
        joy: `<svg class="emotion-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #facc15">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M8 14s1.5 2 4 2 4-2 4-2"></path>
                <line x1="9" y1="9" x2="9.01" y2="9"></line>
                <line x1="15" y1="9" x2="15.01" y2="9"></line>
              </svg>`,
        sadness: `<svg class="emotion-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #60a5fa">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M16 16s-1.5-2-4-2-4 2-4 2"></path>
                    <line x1="9" y1="9" x2="9.01" y2="9"></line>
                    <line x1="15" y1="9" x2="15.01" y2="9"></line>
                  </svg>`,
        anger: `<svg class="emotion-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #ef4444">
                  <circle cx="12" cy="12" r="10"></circle>
                  <path d="M16 16s-1.5-2-4-2-4 2-4 2"></path>
                  <path d="M7.5 8 10 9"></path>
                  <path d="M14 9 16.5 8"></path>
                  <line x1="9" y1="10" x2="9.01" y2="10"></line>
                  <line x1="15" y1="10" x2="15.01" y2="10"></line>
                </svg>`,
        calmness: `<svg class="emotion-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #34d399">
                     <path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"></path>
                     <path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"></path>
                   </svg>`,
        anxiety: `<svg class="emotion-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #c084fc">
                    <path d="M19 16.9A5 5 0 0 0 18 7h-1.26a8 8 0 1 0-11.62 9"></path>
                    <polyline points="13 11 9 17 15 17 11 23"></polyline>
                  </svg>`,
        neutral: `<svg class="emotion-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #9ca3af">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="8" y1="15" x2="16" y2="15"></line>
                    <line x1="9" y1="9" x2="9.01" y2="9"></line>
                    <line x1="15" y1="9" x2="15.01" y2="9"></line>
                  </svg>`
    };
    
    grid.innerHTML = EMOTIONS.map(emotion => `
        <button type="button" class="emotion-option ${emotion.id}" data-emotion="${emotion.id}">
            <div class="emotion-icon-wrapper">${emotionIcons[emotion.id]}</div>
            <span class="emotion-label">${emotion.name}</span>
        </button>
    `).join('');
    
    grid.querySelectorAll('.emotion-option').forEach(btn => {
        btn.addEventListener('click', function () {
            grid.querySelectorAll('.emotion-option').forEach(o => o.classList.remove('selected'));
            this.classList.add('selected');
            selectedEmotion = this.dataset.emotion;
            // Write into the hidden form field
            document.getElementById('selectedEmotionInput').value = selectedEmotion;
            document.getElementById('nextBtn').disabled = false;
        });
    });
}

/**
 * Show specific step in check-in modal
 */
function showStep(step) {
    checkinStep = step;
    
    const step1 = document.getElementById('step1Content');
    const step2 = document.getElementById('step2Content');
    const backBtn = document.getElementById('backBtn');
    const stepIndicator = document.getElementById('stepIndicator');
    const progressFill = document.getElementById('checkinProgress');
    
    if (step === 1) {
        step1.classList.remove('hidden');
        step2.classList.add('hidden');
        backBtn.classList.add('hidden');
        stepIndicator.classList.remove('hidden');
        stepIndicator.textContent = 'Step 1/2';
        progressFill.style.width = '50%';
    } else {
        step1.classList.add('hidden');
        step2.classList.remove('hidden');
        backBtn.classList.remove('hidden');
        stepIndicator.classList.add('hidden');
        progressFill.style.width = '100%';
    }
}

function resetCheckinModal() {
    selectedEmotion = null;
    checkinStep = 1;

    const descEl = document.getElementById('descriptionInput');
    if (descEl) descEl.value = '';

    const hiddenEl = document.getElementById('selectedEmotionInput');
    if (hiddenEl) hiddenEl.value = '';

    document.querySelectorAll('.emotion-option').forEach(o => o.classList.remove('selected'));
    document.getElementById('nextBtn').disabled = true;

    showStep(1);
}

// ── Details modal (read more) ─────────────────────────────────────────────────
// Timeline entries are PHP-rendered; JS only opens/closes the modal.

function initDetailsModal() {
    const modal    = document.getElementById('detailsModal');
    const openBtn  = document.getElementById('readMoreBtn');
    const closeBtn = document.getElementById('closeDetails');

    openBtn.addEventListener('click', function () {
        modal.classList.add('active');
    });

    closeBtn.addEventListener('click', function () {
        modal.classList.remove('active');
    });

    modal.addEventListener('click', function (e) {
        if (e.target === modal) modal.classList.remove('active');
    });
}
