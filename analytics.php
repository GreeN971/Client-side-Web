<?php
session_start();

if (!isset($_SESSION['userid'])) {
    header("Location: index.php");
    exit();
}

$userId   = (int) $_SESSION['userid'];
$username = htmlspecialchars($_SESSION['userusername']);

require_once "classes/dbh.classes.php";
require_once "classes/admin.classes.php";
require_once "classes/checkin.classes.php";

// Update presence and enforce ban status on every page load
$adminModel = new Admin();

$stmt = $adminModel->connect()->prepare('SELECT is_banned FROM users WHERE users_id = ?');
$stmt->execute([$userId]);
$userRow = $stmt->fetch(PDO::FETCH_ASSOC);
if ($userRow && !empty($userRow['is_banned'])) {
    session_destroy();
    header("Location: index.php?error=banned");
    exit();
}

$adminModel->touchLastActive($userId);

$checkinModel = new Checkin();
$today        = date('Y-m-d');

// ── Date-range parameters ─────────────────────────────────────────────────────
// Defaults: last 7 days. Accept ?from=YYYY-MM-DD&to=YYYY-MM-DD from calendar.
// Constraints: min range = 1 day, max range = 365 days. Values are sanitised
// by verifying they parse as valid dates and clamped against the limits.

$defaultFrom = date('Y-m-d', strtotime('-6 days'));
$defaultTo   = $today;

$rawFrom = isset($_GET['from']) ? preg_replace('/[^0-9\-]/', '', $_GET['from']) : $defaultFrom;
$rawTo   = isset($_GET['to'])   ? preg_replace('/[^0-9\-]/', '', $_GET['to'])   : $defaultTo;

// Validate that both values are real calendar dates
$dtFrom = DateTime::createFromFormat('Y-m-d', $rawFrom);
$dtTo   = DateTime::createFromFormat('Y-m-d', $rawTo);

if (!$dtFrom || !$dtTo || $dtFrom->format('Y-m-d') !== $rawFrom || $dtTo->format('Y-m-d') !== $rawTo) {
    $dtFrom = new DateTime($defaultFrom);
    $dtTo   = new DateTime($defaultTo);
}

// Ensure from <= to
if ($dtFrom > $dtTo) { [$dtFrom, $dtTo] = [$dtTo, $dtFrom]; }

// Clamp: max 365 days
$diffDays = (int) $dtFrom->diff($dtTo)->days;
if ($diffDays > 365) {
    $dtTo = clone $dtFrom;
    $dtTo->modify('+365 days');
}

$rangeFrom = $dtFrom->format('Y-m-d');
$rangeTo   = $dtTo->format('Y-m-d');
$isDefault = ($rangeFrom === $defaultFrom && $rangeTo === $defaultTo);

// ── Chart data (covers the full selected range) ───────────────────────────────
$rangeCheckins = $checkinModel->getCheckinsByDateRange($userId, $rangeFrom, $rangeTo);
$checkinsByDate = [];
foreach ($rangeCheckins as $c) {
    $dateKey = substr($c['created_at'], 0, 10);
    $checkinsByDate[$dateKey][] = $c;
}

// Today's entries for the Day Details section (always today, not range-dependent)
$allTodayCheckins = $checkinModel->getCheckinsByDateRange($userId, $today, $today);
$todayCheckins    = [];
foreach ($allTodayCheckins as $c) {
    $todayCheckins[] = $c;
}

// Most recent check-in (any day)
$lastCheckin = $checkinModel->getLastCheckin($userId);

// Build per-day array for JS chart (one entry per day in range, oldest first)
$weeklyData  = [];
$cursor      = clone $dtFrom;
while ($cursor <= $dtTo) {
    $date    = $cursor->format('Y-m-d');
    // For longer ranges, show abbreviated date; for ≤14 days show day name
    $diffDaysTotal = (int) $dtFrom->diff($dtTo)->days + 1;
    $dayLabel = $diffDaysTotal <= 14
        ? $cursor->format('D')
        : $cursor->format('M j');
    $weeklyData[] = [
        'day'      => $dayLabel,
        'date'     => $date,
        'checkins' => $checkinsByDate[$date] ?? [],
    ];
    $cursor->modify('+1 day');
}

// Calendar data (visible month dots) — always show full month the user navigates to.
// We load the entire range so JS can mark dots on any month the user browses.
$calendarData = [];
foreach ($rangeCheckins as $c) {
    $dateKey = substr($c['created_at'], 0, 10);
    $calendarData[$dateKey][] = $c['emotion'];
}

// Colour maps used in PHP-rendered sections
$emotionColors = [
    'joy'      => '#facc15',
    'sadness'  => '#60a5fa',
    'anger'    => '#ef4444',
    'calmness' => '#34d399',
    'neutral'  => '#d5c7b4',
    'anxiety'  => '#c084fc',
];
$emotionBgColors = [
    'joy'      => '#fef9c3',
    'sadness'  => '#dbeafe',
    'anger'    => '#fee2e2',
    'calmness' => '#d1fae5',
    'neutral'  => 'rgba(213,206,180,0.5)',
    'anxiety'  => '#f3e8ff',
];

// Error messages from check-in submission redirect
$errorMessages = [
    'emptyinput'     => 'Please select an emotion.',
    'invalidemotion' => 'Invalid emotion selected.',
    'failedtosave'   => 'Failed to save check-in. Please try again.',
];
$error        = isset($_GET['error']) ? $_GET['error'] : null;
$errorMessage = ($error && isset($errorMessages[$error])) ? $errorMessages[$error] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emotional Flow - Analytics</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/analytics.css">
</head>
<body>
    <div class="app-container">
        <!-- Header -->
        <header class="app-header">
            <div class="header-left">
                <div class="logo">EF</div>
                <h1 class="brand-name-sm">Emotional Flow</h1>
            </div>
            <nav class="header-nav">
                <button class="logout-button" id="logoutBtn" aria-label="Logout">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    <span>Logout</span>
                </button>
            </nav>
        </header>

        <!-- Main Content -->
        <main class="main-content">
            <?php if ($errorMessage): ?>
            <div class="error-banner"><?php echo htmlspecialchars($errorMessage); ?></div>
            <?php endif; ?>
            <div class="content-wrapper">
                <!-- Left Column: Calendar & Check-in -->
                <aside class="sidebar">
                    <!-- Calendar Widget -->
                    <div class="widget calendar-widget">
                        <!-- Legend -->
                        <div class="emotion-legend" id="emotionLegend">
                            <!-- Populated by JS -->
                        </div>

                        <div class="divider"></div>

                        <!-- Month Nav -->
                        <div class="month-nav">
                            <button class="nav-btn" id="prevMonth" aria-label="Previous month">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="15 18 9 12 15 6"></polyline>
                                </svg>
                            </button>
                            <h2 class="current-month" id="currentMonth">October 2023</h2>
                            <button class="nav-btn" id="nextMonth" aria-label="Next month">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </button>
                        </div>

                        <!-- Calendar Grid -->
                        <div class="calendar-grid">
                            <div class="calendar-header">
                                <span>S</span>
                                <span>M</span>
                                <span>T</span>
                                <span>W</span>
                                <span>T</span>
                                <span>F</span>
                                <span>S</span>
                            </div>
                            <div class="calendar-days" id="calendarDays">
                                <!-- Populated by JS -->
                            </div>
                        </div>

                        <!-- Calendar Range Selection -->
                        <div class="calendar-submit-section">
                            <div class="range-display" id="rangeDisplay">
                                <div class="range-display-item">
                                    <span class="range-display-label">From</span>
                                    <span class="range-display-date" id="rangeFromDisplay">&mdash;</span>
                                </div>
                                <div class="range-display-arrow">&rarr;</div>
                                <div class="range-display-item">
                                    <span class="range-display-label">To</span>
                                    <span class="range-display-date" id="rangeToDisplay">&mdash;</span>
                                </div>
                            </div>
                            <p class="range-hint" id="rangeHint">Click a day to set start date</p>
                            <div class="range-actions">
                                <button class="btn btn-ghost btn-sm" id="clearRangeBtn" disabled>Clear</button>
                                <button class="btn btn-secondary btn-full" id="calendarSubmitBtn" disabled>Apply Range</button>
                            </div>
                        </div>
                    </div>

                    <!-- Start Check-in Widget -->
                    <div class="widget checkin-widget">
                        <p class="checkin-prompt">How are you feeling right now?</p>
                        <button class="btn btn-primary btn-full" id="startCheckin">Start Check-in</button>
                        <div class="last-checkin">
                            <?php if ($lastCheckin):
                                $lcEmotion = $lastCheckin['emotion'];
                                $lcColor   = $emotionColors[$lcEmotion] ?? '#ccc';
                            ?>
                                <span>Last check-in:</span>
                                <span class="emotion-dot" style="background-color: <?php echo $lcColor; ?>;"></span>
                                <span class="last-emotion">Feeling <?php echo ucfirst(htmlspecialchars($lcEmotion)); ?></span>
                            <?php else: ?>
                                <span>No check-ins yet.</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </aside>

                <!-- Right Column: Weekly Trends & Day Details -->
                <div class="main-column">
                    <!-- Weekly Trends -->
                    <section class="widget trends-widget">
                        <div class="trends-header">
                            <h2 class="widget-title">
                                <?php if ($isDefault): ?>
                                    Your Weekly Trends
                                <?php else: ?>
                                    Trends: <?php echo htmlspecialchars(date('M j', strtotime($rangeFrom))); ?> &ndash; <?php echo htmlspecialchars(date('M j, Y', strtotime($rangeTo))); ?>
                                <?php endif; ?>
                            </h2>
                            <?php if (!$isDefault): ?>
                            <a href="analytics.php" class="btn btn-ghost btn-sm reset-range-btn" title="Reset to current week">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="1 4 1 10 7 10"></polyline>
                                    <path d="M3.51 15a9 9 0 1 0 .49-4.95"></path>
                                </svg>
                                Reset to this week
                            </a>
                            <?php endif; ?>
                        </div>

                        <div class="chart-container">
                            <div class="chart-scroll-wrap">
                                <div class="bar-chart" id="weeklyChart">
                                    <!-- Populated by JS -->
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Day Details Section -->
                    <section class="widget details-widget">
                        <div class="widget-header">
                            <h2 class="widget-title">Day Details: <?php echo date('F jS'); ?></h2>
                            <button class="link-btn" id="readMoreBtn">Read more</button>
                        </div>

                        <div class="day-entries" id="dayEntries">
                            <?php if (empty($todayCheckins)): ?>
                                <div class="empty-state">
                                    <p class="empty-message">No check-ins yet today. Click &ldquo;Start Check-in&rdquo; to log your first emotion!</p>
                                </div>
                            <?php else: foreach ($todayCheckins as $checkIn):
                                $em    = $checkIn['emotion'];
                                $col   = $emotionColors[$em]   ?? '#ccc';
                                $bgCol = $emotionBgColors[$em] ?? 'rgba(200,200,200,0.2)';
                                $time  = date('h:i A', strtotime($checkIn['created_at']));
                            ?>
                                <article class="day-entry">
                                    <div class="entry-time">
                                        <time><?php echo htmlspecialchars($time); ?></time>
                                    </div>
                                    <div class="entry-emotion">
                                        <div class="emotion-badge" style="background-color: <?php echo $bgCol; ?>">
                                            <span class="emotion-badge-dot" style="background-color: <?php echo $col; ?>"></span>
                                            <span class="emotion-badge-text"><?php echo ucfirst(htmlspecialchars($em)); ?></span>
                                        </div>
                                    </div>
                                    <div class="entry-description">
                                        <p><?php echo !empty($checkIn['note']) ? htmlspecialchars($checkIn['note']) : 'No description provided.'; ?></p>
                                    </div>
                                </article>
                            <?php endforeach; endif; ?>
                        </div>
                    </section>
                </div>
            </div>
        </main>
    </div>

    <!-- Check-in Modal -->
    <div class="modal-overlay" id="checkinModal">
        <div class="modal checkin-modal">
            <form action="includes/checkin.inc.php" method="post" id="checkinForm">
                <input type="hidden" name="emotion" id="selectedEmotionInput" value="">

                <!-- Header / Progress -->
                <div class="modal-header">
                    <div class="progress-container">
                        <div class="progress-bar">
                            <div class="progress-fill" id="checkinProgress"></div>
                        </div>
                        <button type="button" class="close-btn" id="closeCheckin" aria-label="Close modal">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                    <div class="step-nav">
                        <button type="button" class="back-btn hidden" id="backBtn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="19" y1="12" x2="5" y2="12"></line>
                                <polyline points="12 19 5 12 12 5"></polyline>
                            </svg>
                            Back
                        </button>
                        <span class="step-indicator" id="stepIndicator">Step 1/2</span>
                    </div>
                </div>

                <hr class="modal-divider">

                <!-- Step 1: Emotion Selection -->
                <div class="modal-content" id="step1Content">
                    <h2 class="modal-title">Which category best describes your feeling?</h2>
                    <div class="emotion-grid" id="emotionGrid">
                        <!-- Populated by JS -->
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn btn-primary" id="nextBtn" disabled>Next</button>
                    </div>
                </div>

                <!-- Step 2: Description -->
                <div class="modal-content hidden" id="step2Content">
                    <h2 class="modal-title">Why do you feel this way?</h2>
                    <div class="description-container">
                        <textarea
                            class="description-input"
                            name="note"
                            id="descriptionInput"
                            placeholder="Describe your feelings..."
                        ></textarea>
                    </div>
                    <div class="modal-actions">
                        <button type="submit" class="btn btn-primary btn-submit" id="submitBtn">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Daily Details Modal -->
    <div class="modal-overlay" id="detailsModal">
        <div class="modal details-modal">
            <div class="modal-header-details">
                <h2 class="modal-title-sm">Daily Details: <?php echo date('F jS, Y'); ?></h2>
                <button type="button" class="close-btn" id="closeDetails" aria-label="Close modal">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <div class="modal-body-details">
                <!-- Notes & Timeline -->
                <main class="timeline-content">
                    <div class="timeline-wrapper">
                        <h3 class="timeline-title">Your Notes and Timeline</h3>

                        <div class="timeline" id="timelineEntries">
                            <?php if (empty($todayCheckins)): ?>
                                <div class="empty-state">
                                    <p class="empty-message">No check-ins yet today.</p>
                                </div>
                            <?php else: foreach ($todayCheckins as $checkIn):
                                $em   = $checkIn['emotion'];
                                $col  = $emotionColors[$em] ?? '#ccc';
                                $time = date('h:i A', strtotime($checkIn['created_at']));
                            ?>
                                <article class="timeline-entry">
                                    <div class="entry-meta">
                                        <div class="entry-time-wrap">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <polyline points="12 6 12 12 16 14"></polyline>
                                            </svg>
                                            <time><?php echo htmlspecialchars($time); ?></time>
                                        </div>
                                        <div class="entry-emotions">
                                            <span class="emotion-dot" style="background-color: <?php echo $col; ?>"></span>
                                            <span class="emotion-badge-text" style="color: <?php echo $col; ?>"><?php echo ucfirst(htmlspecialchars($em)); ?></span>
                                        </div>
                                    </div>
                                    <div class="entry-note">
                                        <?php echo !empty($checkIn['note']) ? htmlspecialchars($checkIn['note']) : 'No description provided.'; ?>
                                    </div>
                                </article>
                            <?php endforeach; endif; ?>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <script>
        const WEEKLY_DATA   = <?php echo json_encode($weeklyData,   JSON_HEX_TAG); ?>;
        const CALENDAR_DATA = <?php echo json_encode($calendarData, JSON_HEX_TAG); ?>;
        const RANGE_FROM    = <?php echo json_encode($rangeFrom); ?>;
        const RANGE_TO      = <?php echo json_encode($rangeTo);   ?>;
        const IS_DEFAULT    = <?php echo json_encode($isDefault);  ?>;
    </script>
    <script src="js/analytics.js"></script>
</body>
</html>
