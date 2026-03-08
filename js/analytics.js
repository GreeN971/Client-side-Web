// Analytics Page JavaScript

// Emotion data
const EMOTIONS = [
    { id: 'joy', name: 'Joy', color: '#facc15', bgColor: '#fef9c3' },
    { id: 'sadness', name: 'Sadness', color: '#60a5fa', bgColor: '#dbeafe' },
    { id: 'anger', name: 'Anger', color: '#ef4444', bgColor: '#fee2e2' },
    { id: 'calmness', name: 'Calmness', color: '#34d399', bgColor: '#d1fae5' },
    { id: 'neutral', name: 'Neutral', color: '#d5c7b4', bgColor: 'rgba(213, 206, 180, 0.5)' },
    { id: 'anxiety', name: 'Anxiety', color: '#c084fc', bgColor: '#f3e8ff' }
];

// Weekly chart data
const WEEKLY_DATA = [
    { day: 'Mon', emotions: ['Calmness', 'Joy'], values: [40, 60] },
    { day: 'Tue', emotions: ['Sadness', 'Anxiety', 'Joy'], values: [30, 30, 40] },
    { day: 'Wed', emotions: ['Anger', 'Neutral'], values: [20, 80] },
    { day: 'Thu', emotions: ['Joy', 'Calmness'], values: [70, 30] },
    { day: 'Fri', emotions: ['Anxiety', 'Sadness'], values: [50, 50] },
    { day: 'Sat', emotions: ['Joy', 'Joy'], values: [100, 0] },
    { day: 'Sun', emotions: ['Neutral', 'Calmness'], values: [60, 40] }
];

// Storage key for check-ins
const CHECKINS_STORAGE_KEY = 'emotionalFlow_checkIns';

// Check-ins array - will be loaded from localStorage
let checkIns = [];

/**
 * Load check-ins from localStorage
 */
function loadCheckIns() {
    const stored = localStorage.getItem(CHECKINS_STORAGE_KEY);
    if (stored) {
        checkIns = JSON.parse(stored);
    } else {
        checkIns = [];
    }
}

/**
 * Save check-ins to localStorage
 */
function saveCheckIns() {
    localStorage.setItem(CHECKINS_STORAGE_KEY, JSON.stringify(checkIns));
}

/**
 * Add a new check-in
 */
function addCheckIn(emotionId, description) {
    const emotion = EMOTIONS.find(e => e.id === emotionId);
    if (!emotion) return;
    
    const now = new Date();
    const checkIn = {
        id: Date.now(),
        timestamp: now.toISOString(),
        date: now.toDateString(),
        time: formatTime(now),
        emotionId: emotionId,
        emotionName: emotion.name,
        description: description || ''
    };
    
    checkIns.unshift(checkIn); // Add to beginning (newest first)
    saveCheckIns();
    
    // Update UI
    initDayEntries();
    initTimelineEntries();
    updateLastCheckin();
    renderCalendarDays();
}

/**
 * Format time as 12-hour format
 */
function formatTime(date) {
    let hours = date.getHours();
    const minutes = date.getMinutes().toString().padStart(2, '0');
    const ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    hours = hours ? hours : 12; // 0 should be 12
    return `${hours.toString().padStart(2, '0')}:${minutes} ${ampm}`;
}

/**
 * Format date for display
 */
function formatDateForDisplay(dateObj) {
    const months = ['January', 'February', 'March', 'April', 'May', 'June',
                    'July', 'August', 'September', 'October', 'November', 'December'];
    const day = dateObj.getDate();
    const suffix = getDaySuffix(day);
    return `${months[dateObj.getMonth()]} ${day}${suffix}`;
}

/**
 * Get day suffix (st, nd, rd, th)
 */
function getDaySuffix(day) {
    if (day >= 11 && day <= 13) return 'th';
    switch (day % 10) {
        case 1: return 'st';
        case 2: return 'nd';
        case 3: return 'rd';
        default: return 'th';
    }
}

/**
 * Get today's check-ins
 */
function getTodayCheckIns() {
    const today = new Date().toDateString();
    return checkIns.filter(c => c.date === today);
}

/**
 * Update the Day Details title with today's date
 */
function updateDayDetailsTitle() {
    const titleElement = document.querySelector('.details-widget .widget-title');
    if (titleElement) {
        const today = new Date();
        titleElement.textContent = `Day Details: ${formatDateForDisplay(today)}`;
    }
}

/**
 * Update last check-in display
 */
function updateLastCheckin() {
    const lastCheckinContainer = document.querySelector('.last-checkin');
    if (!lastCheckinContainer) return;
    
    const todayCheckIns = getTodayCheckIns();
    if (todayCheckIns.length > 0) {
        const lastCheckIn = todayCheckIns[0];
        const emotion = EMOTIONS.find(e => e.id === lastCheckIn.emotionId);
        const color = emotion ? emotion.color : '#ccc';
        
        lastCheckinContainer.innerHTML = `
            <span>Last check-in:</span>
            <span class="emotion-dot" style="background-color: ${color};"></span>
            <span class="last-emotion">Feeling ${lastCheckIn.emotionName}</span>
        `;
    } else {
        lastCheckinContainer.innerHTML = `
            <span>No check-ins yet today</span>
        `;
    }
}

/**
 * Get emotion color by id
 */
function getEmotionColorById(emotionId) {
    const emotion = EMOTIONS.find(e => e.id === emotionId);
    return emotion ? emotion.color : '#ccc';
}

/**
 * Get emotion bg color by id
 */
function getEmotionBgColorById(emotionId) {
    const emotion = EMOTIONS.find(e => e.id === emotionId);
    return emotion ? emotion.bgColor : 'rgba(200, 200, 200, 0.2)';
}

// State
let currentMonth = new Date(); // Use current month
let selectedDate = null; // Currently selected calendar date
let selectedEmotion = null;
let checkinStep = 1;

document.addEventListener('DOMContentLoaded', function() {
    loadCheckIns();
    initEmotionLegend();
    initCalendar();
    initWeeklyChart();
    initDayEntries();
    initTimelineEntries();
    initCheckinModal();
    initDetailsModal();
    updateLastCheckin();
    updateDayDetailsTitle();
    initLogout();
});

/**
 * Initialize logout button
 */
function initLogout() {
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function() {
            // Clear localStorage login state
            localStorage.removeItem('isLoggedIn');
            localStorage.removeItem('isAdmin');
            localStorage.removeItem('userEmail');
            
            // Hit the PHP logout to destroy session, then redirect to login
            window.Location.href = 'includes/logout.inc.php';
        });
    }
}

/**
 * Get emotion color by name
 */
function getEmotionColor(emotionName) {
    const emotion = EMOTIONS.find(e => e.name.toLowerCase() === emotionName.toLowerCase());
    return emotion ? emotion.color : '#ccc';
}

/**
 * Get emotion bg color by name
 */
function getEmotionBgColor(emotionName) {
    const emotion = EMOTIONS.find(e => e.name.toLowerCase() === emotionName.toLowerCase());
    return emotion ? emotion.bgColor : 'rgba(200, 200, 200, 0.2)';
}

/**
 * Initialize emotion legend
 */
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

/**
 * Initialize calendar
 */
function initCalendar() {
    updateCalendarMonth();
    
    document.getElementById('prevMonth').addEventListener('click', function() {
        currentMonth.setMonth(currentMonth.getMonth() - 1);
        updateCalendarMonth();
    });
    
    document.getElementById('nextMonth').addEventListener('click', function() {
        currentMonth.setMonth(currentMonth.getMonth() + 1);
        updateCalendarMonth();
    });
    
    // Initialize calendar submit button
    const calendarSubmitBtn = document.getElementById('calendarSubmitBtn');
    if (calendarSubmitBtn) {
        calendarSubmitBtn.addEventListener('click', submitCalendarDate);
    }
    
    // Initialize submit button state
    updateCalendarSubmitButton();
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
 * Render calendar days
 */
function renderCalendarDays() {
    const calendarDays = document.getElementById('calendarDays');
    if (!calendarDays) return;
    
    const year = currentMonth.getFullYear();
    const month = currentMonth.getMonth();
    const today = new Date();
    
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    
    let daysHTML = '';
    
    // Empty cells for days before the first day
    for (let i = 0; i < firstDay; i++) {
        daysHTML += '<button class="calendar-day empty"></button>';
    }
    
    // Days of the month
    for (let day = 1; day <= daysInMonth; day++) {
        const isToday = day === today.getDate() && 
                        month === today.getMonth() && 
                        year === today.getFullYear();
        
        // Check if this day is selected
        const dayDate = new Date(year, month, day);
        const dayDateString = dayDate.toDateString();
        const isSelected = selectedDate && selectedDate.toDateString() === dayDateString;
        
        // Check if there are check-ins for this day
        const dayCheckIns = checkIns.filter(c => c.date === dayDateString);
        const hasCheckIn = dayCheckIns.length > 0;
        const emotionColor = hasCheckIn ? getEmotionColorById(dayCheckIns[0].emotionId) : null;
        
        // Build class list
        let classes = 'calendar-day';
        if (isToday) classes += ' today';
        if (isSelected) classes += ' selected';
        
        daysHTML += `
            <button class="${classes}" data-date="${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}">
                ${day}
                ${emotionColor && !isToday && !isSelected ? `<span class="day-indicator" style="background-color: ${emotionColor}"></span>` : ''}
            </button>
        `;
    }
    
    calendarDays.innerHTML = daysHTML;
    
    // Add click handlers to each day
    calendarDays.querySelectorAll('.calendar-day:not(.empty)').forEach(dayBtn => {
        dayBtn.addEventListener('click', function() {
            const dateStr = this.dataset.date;
            if (dateStr) {
                selectCalendarDate(dateStr);
            }
        });
    });
}

/**
 * Select a date on the calendar
 */
function selectCalendarDate(dateStr) {
    const [year, month, day] = dateStr.split('-').map(Number);
    selectedDate = new Date(year, month - 1, day);
    
    // Re-render to show selection
    renderCalendarDays();
    
    // Update submit button state
    updateCalendarSubmitButton();
    
    // Update the selected date display
    updateSelectedDateDisplay();
}

/**
 * Update the calendar submit button state
 */
function updateCalendarSubmitButton() {
    const submitBtn = document.getElementById('calendarSubmitBtn');
    if (submitBtn) {
        submitBtn.disabled = !selectedDate;
    }
}

/**
 * Update the selected date display text
 */
function updateSelectedDateDisplay() {
    const displayEl = document.getElementById('selectedDateDisplay');
    if (displayEl) {
        if (selectedDate) {
            displayEl.textContent = `Selected: ${formatDateForDisplay(selectedDate)}, ${selectedDate.getFullYear()}`;
            displayEl.classList.remove('hidden');
        } else {
            displayEl.classList.add('hidden');
        }
    }
}

/**
 * Submit the selected calendar date
 * This function is prepared for PHP backend integration
 */
function submitCalendarDate() {
    if (!selectedDate) {
        console.warn('No date selected');
        return;
    }
    
    // Format date for backend (YYYY-MM-DD format)
    const formattedDate = formatDateForBackend(selectedDate);
    
    // Data object ready for PHP backend
    const dateData = {
        date: formattedDate,
        year: selectedDate.getFullYear(),
        month: selectedDate.getMonth() + 1,
        day: selectedDate.getDate(),
        timestamp: selectedDate.toISOString()
    };
    
    console.log('Calendar date submitted:', dateData);
    
    // TODO: Connect to PHP backend
    // Example using fetch:
    // fetch('api/calendar.php', {
    //     method: 'POST',
    //     headers: {
    //         'Content-Type': 'application/json',
    //     },
    //     body: JSON.stringify(dateData)
    // })
    // .then(response => response.json())
    // .then(data => {
    //     console.log('Success:', data);
    //     // Handle success - load day data, etc.
    // })
    // .catch(error => {
    //     console.error('Error:', error);
    // });
    
    // For now, just show feedback
    alert(`Date selected: ${formatDateForDisplay(selectedDate)}, ${selectedDate.getFullYear()}\n\nReady to connect to PHP backend.`);
}

/**
 * Format date for backend (YYYY-MM-DD)
 */
function formatDateForBackend(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

/**
 * Initialize weekly chart
 */
function initWeeklyChart() {
    const chartContainer = document.getElementById('weeklyChart');
    if (!chartContainer) return;
    
    chartContainer.innerHTML = WEEKLY_DATA.map(data => {
        const segments = data.emotions.map((emotion, i) => {
            const height = data.values[i];
            if (height <= 0) return '';
            return `<div class="bar-segment" style="height: ${height}%; background-color: ${getEmotionColor(emotion)}"></div>`;
        }).join('');
        
        return `
            <div class="bar-column">
                <div class="bar-stack">
                    ${segments}
                </div>
                <span class="bar-label">${data.day}</span>
            </div>
        `;
    }).join('');
}

/**
 * Initialize day entries - shows today's check-ins
 */
function initDayEntries() {
    const entriesContainer = document.getElementById('dayEntries');
    if (!entriesContainer) return;
    
    const todayCheckIns = getTodayCheckIns();
    
    if (todayCheckIns.length === 0) {
        entriesContainer.innerHTML = `
            <div class="empty-state">
                <p class="empty-message">No check-ins yet today. Click "Start Check-in" to log your first emotion!</p>
            </div>
        `;
        return;
    }
    
    entriesContainer.innerHTML = todayCheckIns.map(checkIn => `
        <article class="day-entry">
            <div class="entry-time">
                <time>${checkIn.time}</time>
            </div>
            
            <div class="entry-emotion">
                <div class="emotion-badge" style="background-color: ${getEmotionBgColorById(checkIn.emotionId)}">
                    <span class="emotion-badge-dot" style="background-color: ${getEmotionColorById(checkIn.emotionId)}"></span>
                    <span class="emotion-badge-text">${checkIn.emotionName}</span>
                </div>
            </div>

            <div class="entry-description">
                <p>${checkIn.description || 'No description provided.'}</p>
            </div>
        </article>
    `).join('');
}

/**
 * Initialize timeline entries in modal - shows today's check-ins
 */
function initTimelineEntries() {
    const timelineContainer = document.getElementById('timelineEntries');
    if (!timelineContainer) return;
    
    const todayCheckIns = getTodayCheckIns();
    
    if (todayCheckIns.length === 0) {
        timelineContainer.innerHTML = `
            <div class="empty-state">
                <p class="empty-message">No check-ins yet today.</p>
            </div>
        `;
        return;
    }
    
    timelineContainer.innerHTML = todayCheckIns.map(checkIn => {
        const emotionColor = getEmotionColorById(checkIn.emotionId);
        
        return `
            <article class="timeline-entry">
                <div class="entry-meta">
                    <div class="entry-time-wrap">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        <time>${checkIn.time}</time>
                    </div>
                    <div class="entry-emotions">
                        <span class="emotion-dot" style="background-color: ${emotionColor}"></span>
                        <span class="emotion-badge-text" style="color: ${emotionColor}">${checkIn.emotionName}</span>
                    </div>
                </div>
                <div class="entry-note">
                    ${checkIn.description || 'No description provided.'}
                </div>
            </article>
        `;
    }).join('');
}

/**
 * Initialize check-in modal
 */
function initCheckinModal() {
    const modal = document.getElementById('checkinModal');
    const openBtn = document.getElementById('startCheckin');
    const closeBtn = document.getElementById('closeCheckin');
    const backBtn = document.getElementById('backBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    
    // Initialize emotion grid
    initEmotionGrid();
    
    // Open modal
    openBtn.addEventListener('click', function() {
        modal.classList.add('active');
        resetCheckinModal();
    });
    
    // Close modal
    closeBtn.addEventListener('click', function() {
        modal.classList.remove('active');
    });
    
    // Close on overlay click
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.classList.remove('active');
        }
    });
    
    // Back button
    backBtn.addEventListener('click', function() {
        showStep(1);
    });
    
    // Next button
    nextBtn.addEventListener('click', function() {
        if (selectedEmotion) {
            showStep(2);
        }
    });
    
    // Submit button
    submitBtn.addEventListener('click', function() {
        const description = document.getElementById('descriptionInput').value;
        
        // Add the check-in to storage
        addCheckIn(selectedEmotion, description);
        
        // Close modal and reset
        modal.classList.remove('active');
        resetCheckinModal();
    });
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
        <button class="emotion-option ${emotion.id}" data-emotion="${emotion.id}">
            <div class="emotion-icon-wrapper">
                ${emotionIcons[emotion.id]}
            </div>
            <span class="emotion-label">${emotion.name}</span>
        </button>
    `).join('');
    
    // Add click handlers
    grid.querySelectorAll('.emotion-option').forEach(option => {
        option.addEventListener('click', function() {
            // Remove selected from all
            grid.querySelectorAll('.emotion-option').forEach(o => o.classList.remove('selected'));
            // Add selected to clicked
            this.classList.add('selected');
            selectedEmotion = this.dataset.emotion;
            // Enable next button
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

/**
 * Reset check-in modal
 */
function resetCheckinModal() {
    selectedEmotion = null;
    checkinStep = 1;
    
    document.getElementById('descriptionInput').value = '';
    document.querySelectorAll('.emotion-option').forEach(o => o.classList.remove('selected'));
    document.getElementById('nextBtn').disabled = true;
    
    showStep(1);
}

/**
 * Initialize details modal
 */
function initDetailsModal() {
    const modal = document.getElementById('detailsModal');
    const openBtn = document.getElementById('readMoreBtn');
    const closeBtn = document.getElementById('closeDetails');
    
    // Update modal title with today's date
    const modalTitle = modal.querySelector('.modal-title-sm');
    if (modalTitle) {
        const today = new Date();
        modalTitle.textContent = `Daily Details: ${formatDateForDisplay(today)}, ${today.getFullYear()}`;
    }
    
    // Open modal
    openBtn.addEventListener('click', function() {
        initTimelineEntries(); // Refresh entries when opening
        modal.classList.add('active');
    });
    
    // Close modal
    closeBtn.addEventListener('click', function() {
        modal.classList.remove('active');
    });
    
    // Close on overlay click
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.classList.remove('active');
        }
    });
}
