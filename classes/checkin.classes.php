<?php
require_once "dbh.classes.php";

class Checkin extends Dbh {

    /**
     * Fetch all check-ins for a user within a date range (inclusive).
     * Returns rows ordered by created_at ASC.
     */
    public function getCheckinsByDateRange($userId, $startDate, $endDate) {
        $stmt = $this->connect()->prepare(
            'SELECT * FROM checkins
             WHERE user_id = ? AND DATE(created_at) BETWEEN ? AND ?
             ORDER BY created_at ASC'
        );
        if (!$stmt->execute([$userId, $startDate, $endDate])) {
            return [];
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetch the single most recent check-in for a user (any date).
     */
    public function getLastCheckin($userId) {
        $stmt = $this->connect()->prepare(
            'SELECT * FROM checkins
             WHERE user_id = ?
             ORDER BY created_at DESC
             LIMIT 1'
        );
        if (!$stmt->execute([$userId])) {
            return null;
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Insert a new check-in record.
     */
    protected function insertCheckin($userId, $emotion, $note) {
        $stmt = $this->connect()->prepare(
            'INSERT INTO checkins (user_id, emotion, note) VALUES (?, ?, ?)'
        );
        if (!$stmt->execute([$userId, $emotion, $note])) {
            header("Location: ../analytics.php?error=failedtosave");
            exit();
        }
    }
}
