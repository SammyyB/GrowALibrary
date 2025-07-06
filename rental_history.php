<?php
require 'functions.php';

if (!isset($_SESSION['user_id'])) {
    echo "<div class='alert alert-danger'>You must be logged in to view this page.</div>";
    exit;
}

$history = getRentalHistory($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Rental History</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #efefef;
            padding: 20px;
        }

        .container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            max-width: 800px;
            margin: auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: center;
        }

        th {
            background-color: #f8f8f8;
        }

        .returned {
            color: green;
        }

        .not-returned {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Your Rental History</h2>

        <?php if (empty($history)): ?>
            <p>You have no rental history yet.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Video Title</th>
                        <th>Rented On</th>
                        <th>Returned On</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $entry): ?>
                        <tr>
                            <td><?= htmlspecialchars($entry['title']) ?></td>
                            <td><?= htmlspecialchars($entry['rent_date']) ?></td>
                            <td><?= $entry['return_date'] ? htmlspecialchars($entry['return_date']) : '---' ?></td>
                            <td class="<?= $entry['return_date'] ? 'returned' : 'not-returned' ?>">
                                <?= $entry['return_date'] ? 'Returned' : 'Not Returned' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>