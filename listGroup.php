<?php

$apiUrl = 'https://whatsapp-waha.brandon.my/api/default/groups?sortBy=id';

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'accept: application/json'
]);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
?>

<!DOCTYPE html>
<html>
<head>
    <title>WhatsApp Group List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .group-card {
            margin-bottom: 20px;
        }
        .group-title {
            font-weight: bold;
        }
        .participant {
            font-family: monospace;
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <h2 class="mb-4">WhatsApp Group Listing</h2>

    <?php if (!empty($data)): ?>
        <?php foreach ($data as $group): ?>
            <div class="card group-card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title group-title"><?= htmlspecialchars($group['name'] ?? 'N/A') ?></h5>
                    <p><strong>Group ID:</strong> <?= htmlspecialchars($group['id']['_serialized'] ?? 'N/A') ?></p>
                    <h6>Participants:</h6>
                    <ul class="list-group">
                        <?php foreach ($group['groupMetadata']['participants'] ?? [] as $p): ?>
                            <li class="list-group-item participant">
                                <?= htmlspecialchars($p['id']['_serialized']) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-warning">No group data found.</div>
    <?php endif; ?>
</div>
</body>
</html>
