<!DOCTYPE html>
<html>

<head>
    <title>Sentence Formatter Tool</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        margin: 40px;
        background-color: #f9f9f9;
    }

    textarea {
        width: 100%;
        height: 200px;
        padding: 10px;
        font-size: 16px;
        resize: vertical;
    }

    .container {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px #ccc;
        max-width: 800px;
        margin: auto;
    }

    button {
        padding: 10px 20px;
        font-size: 16px;
        margin-top: 10px;
        background-color: #007BFF;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
    }

    button:hover {
        background-color: #0056b3;
    }

    pre {
        background: #f1f1f1;
        padding: 15px;
        white-space: pre-wrap;
        border-radius: 6px;
    }

    h2 {
        text-align: center;
    }
    </style>
</head>

<body>

    <div class="container">
        <h2>AI Sentence Formatter Tool</h2>
        <form method="post">
            <label for="input">Paste your raw script here:</label><br>
            <textarea name="input" id="input"
                required><?php echo isset($_POST['input']) ? htmlspecialchars($_POST['input']) : ''; ?></textarea><br>
            <button type="submit">Format Sentences</button>
        </form>

        <?php
    if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST['input'])) {
        $text = trim($_POST['input']);
        $text = preg_replace('/\s+/', ' ', $text); // normalize spaces

        // Use regex to split by common sentence boundaries
        $pattern = '/(?<!\w\.\w.)(?<![A-Z][a-z]\.)(?<=\.|\?|!|\n)\s+/';
        $rawSentences = preg_split($pattern, $text);

        echo "<h3>Formatted Output:</h3><pre>";

        foreach ($rawSentences as $s) {
            $s = trim($s);

            if ($s === '') continue;

            // Ensure sentence ends with a period.
            if (!preg_match('/[.!?]$/', $s)) {
                $s .= '.';
            }

            echo $s . "\n";
        }

        echo "</pre>";
    }
    ?>
    </div>

</body>

</html>