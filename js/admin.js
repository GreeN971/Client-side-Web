// Admin Panel JavaScript

document.addEventListener('DOMContentLoaded', function () {
    renderDauChart();
    pollOnlineUsers();
    setInterval(pollOnlineUsers, 5000);
});

// ── DAU bar chart ─────────────────────────────────────────────────────────────
function renderDauChart() {
    const container = document.getElementById('dauChart');
    if (!container || !window.DAU_DATA) return;

    const max = Math.max(...DAU_DATA.map(d => d.count), 1);

    container.style.setProperty('--bar-count', DAU_DATA.length);
    container.innerHTML = '';

    DAU_DATA.forEach(function (d) {
        const col = document.createElement('div');
        col.className = 'dau-col';

        const bar = document.createElement('div');
        bar.className = 'dau-bar';
        bar.style.setProperty('--h', ((d.count / max) * 100) + '%');
        bar.title = d.label + ': ' + d.count + ' user' + (d.count !== 1 ? 's' : '');

        const lbl = document.createElement('span');
        lbl.className = 'dau-label';
        lbl.textContent = d.label;

        col.appendChild(bar);
        col.appendChild(lbl);
        container.appendChild(col);
    });
}

// ── Online users polling ──────────────────────────────────────────────────────
function pollOnlineUsers() {
    fetch('includes/api/active-users.inc.php')
        .then(function (r) {
            if (!r.ok) throw new Error('Non-OK response');
            return r.json();
        })
        .then(function (users) {
            updateOnlineTable(users);

            const countEl = document.getElementById('onlineCount');
            if (countEl) countEl.textContent = users.length;
        })
        .catch(function () {
            // Silently ignore transient network errors between polls
        });
}

function updateOnlineTable(users) {
    const tbody = document.getElementById('onlineTableBody');
    if (!tbody) return;

    if (!users.length) {
        tbody.innerHTML = '<tr><td colspan="2" class="empty-row">No users online.</td></tr>';
        return;
    }

    tbody.innerHTML = users.map(function (u) {
        const ago = u.last_active ? timeSince(u.last_active) : '—';
        return '<tr>' +
            '<td>' + escHtml(u.username) + '</td>' +
            '<td>' + ago + '</td>' +
            '</tr>';
    }).join('');
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function escHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function timeSince(mysqlDatetime) {
    const past = new Date(mysqlDatetime.replace(' ', 'T') + 'Z');
    const secs = Math.floor((Date.now() - past.getTime()) / 1000);
    if (secs < 60)   return secs + ' s ago';
    if (secs < 3600) return Math.floor(secs / 60) + ' min ago';
    return Math.floor(secs / 3600) + ' h ago';
}

