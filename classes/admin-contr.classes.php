<?php
require_once "admin.classes.php";

class AdminContr extends Admin {

    private $targetUserId;
    private $action;

    public function __construct($targetUserId, $action) {
        $this->targetUserId = (int) $targetUserId;
        $this->action       = $action;
    }

    public function handleBan() {
        if ($this->targetUserId <= 0) {
            header("Location: ../admin.php?error=invaliduser");
            exit();
        }

        if ($this->action === 'ban') {
            $ok = $this->banUser($this->targetUserId);
        } elseif ($this->action === 'unban') {
            $ok = $this->unbanUser($this->targetUserId);
        } else {
            header("Location: ../admin.php?error=invalidaction");
            exit();
        }

        if (!$ok) {
            header("Location: ../admin.php?error=actionfailed");
            exit();
        }

        header("Location: ../admin.php?success=" . urlencode($this->action));
        exit();
    }
}
