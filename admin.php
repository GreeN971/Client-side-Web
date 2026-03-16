<?php
session_start();

if (empty($_SESSION['userid']) || empty($_SESSION['is_admin']) || (int)$_SESSION['is_admin'] !== 1) {
    header("Location: index.php?error=notauthorized");
    exit();
}

require_once "classes/dbh.classes.php";
require_once "classes/admin.classes.php";

$admin   = new Admin();
$stats   = $admin->getStats();
$users   = $admin->getAllUsers();
$dailyAU = $admin->getDailyActiveUsers();
$dailyAU = array_combine(array_column($dailyAU, 'date'), array_column($dailyAU, 'count'));

// Build a full 30-day series so days with 0 are included
$dauData = [];
for ($i = 29; $i >= 0; $i--) {
    $date          = date('Y-m-d', strtotime("-{$i} days"));
    $shortDate     = date('M j', strtotime($date));
    $dauData[]     = ['label' => $shortDate, 'count' => (int)($dailyAU[$date] ?? 0)];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emotional Flow — Admin</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
<div class="admin-wrap">

    <!-- ── Header ──────────────────────────────────────────────────────────── -->
    <header class="admin-header">
        <div class="header-left">
            <span class="logo">EF</span>
            <span class="brand-name-sm">Admin Panel</span>
        </div>
        <nav class="header-nav">
            <a href="includes/logout.inc.php" class="logout-btn" id="logoutBtn">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
                Logout
            </a>
        </nav>
    </header>

    <main class="admin-main">

        <!-- ── Stat cards ───────────────────────────────────────────────── -->
        <section class="stats-row" aria-label="Overview statistics">
            <div class="stat-card">
                <span class="stat-value"><?= $stats['total_users'] ?></span>
                <span class="stat-label">Total Users</span>
            </div>
            <div class="stat-card">
                <span class="stat-value"><?= $stats['total_checkins'] ?></span>
                <span class="stat-label">Total Check-ins</span>
            </div>
            <div class="stat-card">
                <span class="stat-value"><?= $stats['active_today'] ?></span>
                <span class="stat-label">Active Today</span>
            </div>
            <div class="stat-card highlight">
                <span class="stat-value" id="onlineCount"><?= $stats['online_now'] ?></span>
                <span class="stat-label">
                    <span class="online-dot"></span>Online Now
                </span>
            </div>
        </section>

        <!-- ── Daily active users chart ─────────────────────────────────── -->
        <section class="panel dau-panel">
            <h2 class="panel-title">Daily Active Users — last 30 days</h2>
            <div class="dau-chart-wrap">
                <div class="dau-chart" id="dauChart"></div>
            </div>
        </section>

        <!-- ── Online users ─────────────────────────────────────────────── -->
        <section class="panel online-panel">
            <h2 class="panel-title">
                Online Users
                <span class="refresh-note">(refreshes every 5 s)</span>
            </h2>
            <div class="table-wrap">
                <table class="user-table" id="onlineTable">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Last Active</th>
                        </tr>
                    </thead>
                    <tbody id="onlineTableBody">
                        <tr><td colspan="2" class="empty-row">Loading…</td></tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- ── All users ────────────────────────────────────────────────── -->
        <section class="panel users-panel">
            <h2 class="panel-title">All Users</h2>
            <?php if (isset($_GET['success'])): ?>
                <p class="flash flash-ok">
                    User has been <?= htmlspecialchars($_GET['success']) ?>ned successfully.
                </p>
            <?php elseif (isset($_GET['error'])): ?>
                <p class="flash flash-err">
                    Action failed: <?= htmlspecialchars($_GET['error']) ?>
                </p>
            <?php endif; ?>
            <div class="table-wrap">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Last Active</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                        <tr><td colspan="5" class="empty-row">No users found.</td></tr>
                        <?php else: ?>
                        <?php foreach ($users as $u): ?>
                        <tr class="<?= $u['is_banned'] ? 'row-banned' : '' ?>">
                            <td><?= htmlspecialchars($u['username']) ?></td>
                            <td><?= htmlspecialchars($u['users_email']) ?></td>
                            <td><?= $u['last_active']
                                    ? htmlspecialchars(date('d M Y H:i', strtotime($u['last_active'])))
                                    : '—' ?></td>
                            <td>
                                <?php if ($u['is_banned']): ?>
                                    <span class="badge banned">Banned</span>
                                <?php else: ?>
                                    <span class="badge active">Active</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form action="includes/ban.inc.php" method="POST">
                                    <input type="hidden" name="target_user_id"
                                           value="<?= (int)$u['users_id'] ?>">
                                    <input type="hidden" name="action"
                                           value="<?= $u['is_banned'] ? 'unban' : 'ban' ?>">
                                    <button type="submit" name="ban_submit"
                                            class="ban-btn <?= $u['is_banned'] ? 'unban' : 'ban' ?>">
                                        <?= $u['is_banned'] ? 'Unban' : 'Ban' ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </main>
</div>

<script>
const DAU_DATA = <?= json_encode(array_values($dauData)) ?>;
</script>
<script src="js/admin.js"></script>
</body>
</html>
