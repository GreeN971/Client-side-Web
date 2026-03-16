<?php
require_once "dbh.classes.php";

class Admin extends Dbh {

    /**
     * Returns all non-admin users with their ban status and last_active time.
     * Ordered by username ASC.
     */
    public function getAllUsers() {
        $stmt = $this->connect()->prepare(
            'SELECT users_id, username, users_email, is_banned, last_active
             FROM users
             WHERE is_admin = 0
             ORDER BY username ASC'
        );
        if (!$stmt->execute()) {
            return [];
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Returns users whose last_active is within the last 5 minutes.
     */
    public function getActiveUsers() {
        $stmt = $this->connect()->prepare(
            'SELECT users_id, username, last_active
             FROM users
             WHERE is_admin = 0
               AND last_active >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
             ORDER BY last_active DESC'
        );
        if (!$stmt->execute()) {
            return [];
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Set is_banned = 1 for a given user id. Cannot ban admins.
     */
    public function banUser($userId) {
        $stmt = $this->connect()->prepare(
            'UPDATE users SET is_banned = 1 WHERE users_id = ? AND is_admin = 0'
        );
        return $stmt->execute([(int) $userId]);
    }

    /**
     * Set is_banned = 0 for a given user id.
     */
    public function unbanUser($userId) {
        $stmt = $this->connect()->prepare(
            'UPDATE users SET is_banned = 0 WHERE users_id = ? AND is_admin = 0'
        );
        return $stmt->execute([(int) $userId]);
    }

    /**
     * Overall stats: total users, total check-ins, active users today.
     */
    public function getStats() {
        $db = $this->connect();

        $r = $db->query('SELECT COUNT(*) AS total FROM users WHERE is_admin = 0');
        $totalUsers = (int) $r->fetchColumn();

        $r = $db->query('SELECT COUNT(*) AS total FROM checkins');
        $totalCheckins = (int) $r->fetchColumn();

        $r = $db->query(
            'SELECT COUNT(DISTINCT user_id) AS total
             FROM checkins
             WHERE DATE(created_at) = CURDATE()'
        );
        $activeToday = (int) $r->fetchColumn();

        $r = $db->query(
            'SELECT COUNT(*) AS total
             FROM users
             WHERE is_admin = 0
               AND last_active >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)'
        );
        $onlineNow = (int) $r->fetchColumn();

        return [
            'total_users'    => $totalUsers,
            'total_checkins' => $totalCheckins,
            'active_today'   => $activeToday,
            'online_now'     => $onlineNow,
        ];
    }

    /**
     * Daily active users (distinct users with a check-in) for the last 30 days.
     * Returns array of [ 'date' => 'YYYY-MM-DD', 'count' => N ]
     */
    public function getDailyActiveUsers() {
        $stmt = $this->connect()->prepare(
            'SELECT DATE(created_at) AS date, COUNT(DISTINCT user_id) AS count
             FROM checkins
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)
             GROUP BY DATE(created_at)
             ORDER BY date ASC'
        );
        if (!$stmt->execute()) {
            return [];
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update last_active timestamp for a user.
     */
    public function touchLastActive($userId) {
        $stmt = $this->connect()->prepare(
            'UPDATE users SET last_active = NOW() WHERE users_id = ?'
        );
        $stmt->execute([(int) $userId]);
    }
}
