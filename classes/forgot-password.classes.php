<?php
require_once "dbh.classes.php";

class ForgotPassword extends Dbh {

    /**
     * Look up a user by username or email AND matching pet name.
     * Returns the users_id on success, or null if not found.
     */
    protected function findUser($identification, $petsName) {
        $stmt = $this->connect()->prepare(
            'SELECT users_id FROM users
             WHERE (username = ? OR users_email = ?)
               AND LOWER(pets_name) = LOWER(?)
             LIMIT 1'
        );

        if (!$stmt->execute([$identification, $identification, $petsName])) {
            return null;
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int) $row['users_id'] : null;
    }

    /**
     * Update the password for a given user id.
     */
    protected function updatePassword($userId, $newPassword) {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->connect()->prepare(
            'UPDATE users SET users_pwd = ? WHERE users_id = ?'
        );
        return $stmt->execute([$hash, (int) $userId]);
    }
}
