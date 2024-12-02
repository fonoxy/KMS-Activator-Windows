<?php

$keysFile = 'keys.json';
$codesFile = 'codes.json';
$usedCodesFile = 'used_codes.json';
$adminUsername = 'your_username';
$adminPassword = 'your_password';

// Get the data from the files
$keys = json_decode(file_get_contents($keysFile), true);
$codes = json_decode(file_get_contents($codesFile), true);
$usedCodes = json_decode(file_get_contents($usedCodesFile), true);

// Start session to manage login state
session_start();

// Login check
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($username === $adminUsername && $password === $adminPassword) {
        $_SESSION['loggedin'] = true;
    } else {
        $loginError = "Invalid username or password.";
    }
}

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    // Show login form if not logged in
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Admin Login</title>
        <style>
            body { font-family: Arial, sans-serif; background-color: #f4f4f9; padding: 20px; text-align: center; }
            .login-container { max-width: 400px; margin: auto; padding: 20px; background-color: #fff; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
            input[type='text'], input[type='password'] { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 5px; }
            input[type='submit'] { background-color: #4CAF50; color: white; border: none; cursor: pointer; padding: 10px; border-radius: 5px; }
            input[type='submit']:hover { background-color: #45a049; }
            .error { color: red; }
        </style>
    </head>
    <body>
        <div class='login-container'>
            <h2>Admin Login</h2>";
            if (isset($loginError)) {
                echo "<p class='error'>$loginError</p>";
            }
            echo '<form method="POST">
                    <label for="username">Username:</label>
                    <input type="text" name="username" required><br>
                    <label for="password">Password:</label>
                    <input type="password" name="password" required><br>
                    <input type="submit" name="login" value="Login">
                </form>
        </div>
    </body>
    </html>';
    exit;
}

// Handle code deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_code'])) {
    $codeToDelete = $_POST['delete_code'];

    // Remove the code from the codes and used codes data
    unset($codes[$codeToDelete]);
    unset($usedCodes[$codeToDelete]);

    // Save updated data
    file_put_contents($codesFile, json_encode($codes, JSON_PRETTY_PRINT));
    file_put_contents($usedCodesFile, json_encode($usedCodes, JSON_PRETTY_PRINT));

    // Redirect to avoid form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Generate a 6-digit unique code
function generateCode() {
    return strtoupper(substr(uniqid(), -6)); // Generates a 6-digit code
}

// Handle URL query string to generate and assign a code
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['os'], $_GET['edition'], $_GET['username'])) {
    $osSelection = $_GET['os'];
    $editionSelection = $_GET['edition'];
    $username = $_GET['username'];

    if (empty($osSelection) || empty($editionSelection) || empty($username)) {
        echo "Please provide a valid OS, Edition, and Username in the URL.";
        exit;
    }

    $key = $keys[$osSelection][$editionSelection] ?? '';
    if (empty($key)) {
        echo "Invalid OS or Edition selection.";
        exit;
    }

    $code = generateCode();

    $codes[$code] = [
        'username' => $username,
        'key' => $key,
        'os' => $osSelection,
        'edition' => $editionSelection
    ];

    // Set the initial used status to false for the new code
    $usedCodes[$code] = false;

    // Save the new code and status to files
    file_put_contents($codesFile, json_encode($codes, JSON_PRETTY_PRINT));
    file_put_contents($usedCodesFile, json_encode($usedCodes, JSON_PRETTY_PRINT));

    echo "<div class='message'>Registration Code: " . $code . "<br>";
    echo "Assigned OS: " . $osSelection . " (" . $editionSelection . ")<br>";
    echo "Assigned Key: " . $key . "</div>";

    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Code</title>
    <style>

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h2, h3 {
            text-align: center;
            color: #333;
        }
        label {
            font-weight: bold;
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        .message {
            background-color: #e7f7e7;
            color: #4caf50;
            padding: 10px;
            border-radius: 5px;
        }
        .code-entry {
            background-color: #f9f9f9;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
    
    </style>
</head>
<body>
<div class="container">
    <h2>Generate Registration Code</h2>
    <form method="GET">
        <label for="os">Select OS:</label>
        <select name="os" id="os" onchange="updateEditions()">
            <option value="">-- Select OS --</option>
            <?php foreach ($keys as $os => $editions): ?>
                <option value="<?= $os ?>"><?= $os ?></option>
            <?php endforeach; ?>
        </select><br>

        <label for="edition">Select Edition:</label>
        <select name="edition" id="edition">
            <option value="">-- Select Edition --</option>
        </select><br>

        <label for="username">Enter Name:</label>
        <input type="text" name="username" required><br>

        <input type="submit" value="Generate Code">
    </form>
</div>

<h3>Current Registration Codes:</h3>
<table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th>Code</th>
            <th>Username</th>
            <th>OS</th>
            <th>Edition</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($codes as $code => $data): ?>
            <tr>
                <td><?= $code ?></td>
                <td><?= htmlspecialchars($data['username']) ?></td>
                <td><?= htmlspecialchars($data['os']) ?></td>
                <td><?= htmlspecialchars($data['edition']) ?></td>
                <td><?= $usedCodes[$code] ? "<span style='color: red;'>Used</span>" : "<span style='color: green;'>Not Used</span>" ?></td>
                <td>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="delete_code" value="<?= $code ?>">
                        <input type="submit" value="Delete" onclick="return confirm('Are you sure you want to delete this code?');">
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
    function updateEditions() {
        var os = document.getElementById("os").value;
        var editionSelect = document.getElementById("edition");
        editionSelect.innerHTML = "<option value=''>-- Select Edition --</option>";
        var editions = <?= json_encode($keys); ?>[os] || {};
        for (var edition in editions) {
            var option = document.createElement("option");
            option.value = edition;
            option.text = edition;
            editionSelect.appendChild(option);
        }
    }
</script>
</body>
</html>
