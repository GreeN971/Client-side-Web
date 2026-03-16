<?php

class CheckinContr extends Checkin {

    private static $validEmotions = ['joy', 'sadness', 'anger', 'calmness', 'neutral', 'anxiety'];

    private $userId;
    private $emotion;
    private $note;

    public function __construct($userId, $emotion, $note) {
        $this->userId  = (int) $userId;
        $this->emotion = strtolower(trim($emotion));
        $this->note    = trim($note);
    }

    public function addCheckin() {
        if ($this->emptyInput()) {
            header("Location: ../analytics.php?error=emptyinput");
            exit();
        }
        if (!$this->validEmotion()) {
            header("Location: ../analytics.php?error=invalidemotion");
            exit();
        }
        $this->insertCheckin($this->userId, $this->emotion, $this->note);
        header("Location: ../analytics.php");
        exit();
    }

    private function emptyInput() {
        return $this->userId === 0 || empty($this->emotion);
    }

    private function validEmotion() {
        return in_array($this->emotion, self::$validEmotions, true);
    }
}
