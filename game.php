<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Initialize game variables if starting a new game
if (!isset($_SESSION['word'])) {
    // Read words from words.txt into an array
    $words = file('words.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($words === false) {
        die("Error: Could not read words from words.txt. Please ensure the file exists and is readable.");
    }

    // Track recently used words to avoid repetition
    if (!isset($_SESSION['recent_words'])) {
        $_SESSION['recent_words'] = [];
    }

    // Select a random word that hasn't been recently used
    do {
        $new_word = $words[array_rand($words)];
    } while (in_array($new_word, $_SESSION['recent_words']));

    // Set up new game state
    $_SESSION['word'] = $new_word;
    $_SESSION['guesses'] = [];
    $_SESSION['remaining_attempts'] = 6;

    // Update recent words to limit it to the last 5 words
    array_push($_SESSION['recent_words'], $_SESSION['word']);
    if (count($_SESSION['recent_words']) > 5) {
        array_shift($_SESSION['recent_words']);
    }
}

$error_message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $guess = strtolower($_POST['guess']);
    if (!ctype_alpha($guess)) {
        $error_message = "Please enter a valid letter (A-Z).";
    } elseif (in_array($guess, $_SESSION['guesses'])) {
        $error_message = "You have already guessed that letter. Try a new one!";
    } else {
        $_SESSION['guesses'][] = $guess;
        if (strpos($_SESSION['word'], $guess) === false) {
            $_SESSION['remaining_attempts']--;
        }
    }
}

$word = $_SESSION['word'];
$remaining_attempts = $_SESSION['remaining_attempts'];
$guessed_word = '';
foreach (str_split($word) as $letter) {
    $guessed_word .= in_array($letter, $_SESSION['guesses']) ? "<span class='correct'>$letter</span>" : "_ ";
}

$image_path = (6 - $remaining_attempts) . ".png";

if ($remaining_attempts <= 0) {
    $message = "Game Over! The word was '$word'.";
} elseif (str_replace("<span class='correct'>", "", strip_tags($guessed_word)) == $word) {
    if (!isset($_SESSION['score'])) {
        $_SESSION['score'] = 0;
    }
    $_SESSION['score'] += 1;
    $message = "Congratulations! You guessed the word!";
} else {
    $message = "Guess a letter!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hangman Game</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="game-container">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
        <h3>Hangman Game</h3>

        <!-- Display Hangman Image -->
        <div class="hangman-image">
            <img src="<?php echo $image_path; ?>" alt="Hangman">
        </div>

        <p class="remaining-attempts">Attempts remaining: <?php echo $remaining_attempts; ?></p>

        <!-- Display Word with Correct Guesses -->
        <p class="word-display"><?php echo $guessed_word; ?></p>

        <!-- Display guessed letters -->
        <p>Guessed letters: <?php echo implode(' ', $_SESSION['guesses']); ?></p>

        <!-- Display Error Message if Present -->
        <?php if ($error_message): ?>
            <p class="error"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <!-- Guess Form -->
        <?php if ($remaining_attempts > 0 && str_replace("<span class='correct'>", "", strip_tags($guessed_word)) != $word): ?>
            <form method="post">
                <label for="guess">Your guess:</label>
                <input type="text" name="guess" id="guess" maxlength="1" required>
                <button type="submit">Submit</button>
            </form>
        <?php endif; ?>

        <p><?php echo $message; ?></p>
        <a href="leaderboard.php" class="start-button">View Leaderboard</a>
        <a href="logout.php" class="start-button">Logout</a>
    </div>

    <!-- Game Over / Win Modal -->
    <?php if ($remaining_attempts <= 0 || str_replace("<span class='correct'>", "", strip_tags($guessed_word)) == $word): ?>
    <div class="modal">
        <div class="modal-content">
            <h2><?php echo $remaining_attempts <= 0 ? "Game Over!" : "Congratulations!"; ?></h2>
            <p><?php echo $remaining_attempts <= 0 ? "The word was '$word'." : "You guessed the word!"; ?></p>
            <a href="reset.php" class="start-button">Play Again</a>
        </div>
    </div>
    <?php endif; ?>
</body>
</html>
