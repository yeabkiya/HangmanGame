<?php
session_start();
unset($_SESSION['word']);             // Clear the word to force a new random word
unset($_SESSION['guesses']);          // Clear guessed letters
unset($_SESSION['remaining_attempts']);// Clear remaining attempts
header("Location: game.php");
exit;
?>
