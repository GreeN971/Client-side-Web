<?php
require_once "forgot-password.classes.php";

class ForgotPasswordContr extends ForgotPassword {

    private $identification;
    private $petsName;
    private $newPassword;
    private $confirmPassword;

    public function __construct($identification, $petsName, $newPassword, $confirmPassword) {
        $this->identification  = trim($identification);
        $this->petsName        = trim($petsName);
        $this->newPassword     = $newPassword;
        $this->confirmPassword = $confirmPassword;
    }

    public function resetPassword() {
        if (empty($this->identification) || empty($this->petsName) ||
            empty($this->newPassword)    || empty($this->confirmPassword)) {
            header("Location: ../forgot-password.php?error=emptyinput");
            exit();
        }

        if (strlen($this->newPassword) < 8) {
            header("Location: ../forgot-password.php?error=passwordtooshort");
            exit();
        }

        if ($this->newPassword !== $this->confirmPassword) {
            header("Location: ../forgot-password.php?error=passworddoesnotmatch");
            exit();
        }

        $userId = $this->findUser($this->identification, $this->petsName);

        if ($userId === null) {
            header("Location: ../forgot-password.php?error=usernotfound");
            exit();
        }

        $this->updatePassword($userId, $this->newPassword);

        header("Location: ../index.php?success=passwordreset");
        exit();
    }
}
