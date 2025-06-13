<?php
$userId = $_GET['user_id'] ?? '';
if (!$userId) {
    echo 'No user_id provided';
    exit;
}

$apiUrl = 'https://whatsapp-waha.brandon.my/api/default/groups/' . urlencode($userId);
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'accept: application/json'
]);
$response = curl_exec($ch);
curl_close($ch);
$group = json_decode($response, true);

// Fetch group picture
$picApi = 'https://whatsapp-waha.brandon.my/api/default/groups/' . urlencode($userId) . '/picture?refresh=false';
$picCh = curl_init($picApi);
curl_setopt($picCh, CURLOPT_RETURNTRANSFER, true);
curl_setopt($picCh, CURLOPT_HTTPHEADER, ['accept: application/json']);
$picResponse = curl_exec($picCh);
curl_close($picCh);
$picData = json_decode($picResponse, true);
$groupPic = $picData['url'] ?? 'https://cloud.webbypage.com/index.php/s/kwzFAtinnHtzDiy/download';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Group</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .group-card { margin-top: 20px; }
        .participant { font-family: monospace; }
        .description { white-space: pre-wrap; }
        .edit-btn { position: absolute; top: 10px; right: 10px; }
        .group-avatar { width: 50px; height: 50px; border-radius: 50%; object-fit: cover; margin-right: 15px; }
    </style>
</head>
<body>
<div class="container mt-4">
    <h2 class="mb-4">Manage WhatsApp Group</h2>
    <?php if (!empty($group)): 
        $groupId = $group['id']['_serialized'] ?? '';
        $groupName = htmlspecialchars($group['name'] ?? 'N/A');
        $groupDesc = nl2br(htmlspecialchars($group['groupMetadata']['desc'] ?? 'No description'));
    ?>
    <div class="card group-card shadow-sm">
        <div class="card-body position-relative">
            <button class="btn btn-sm btn-outline-primary edit-btn" 
                    data-bs-toggle="modal" 
                    data-bs-target="#editGroupModal"
                    data-groupid="<?= htmlspecialchars($groupId) ?>"
                    data-groupname="<?= $groupName ?>"
                    data-groupdesc="<?= htmlspecialchars($group['groupMetadata']['desc'] ?? '') ?>"
                    data-grouppic="<?= htmlspecialchars($groupPic) ?>">
                <i class="bi bi-pencil"></i> Edit
            </button>
            
            <div class="d-flex align-items-center mb-3">
                <img src="<?= htmlspecialchars($groupPic) ?>" class="group-avatar" alt="Group Picture">
                <div>
                    <h5 class="card-title group-title mb-0"><?= $groupName ?></h5>
                    <small class="text-muted"><?= htmlspecialchars($groupId) ?></small>
                </div>
            </div>
            
            <p class="description"><strong>Description:</strong><br><?= $groupDesc ?></p>
            <?php
            $participants = $group['groupMetadata']['participants'] ?? [];
            ?>
            <h6>Participants (<?= count($participants) ?>):</h6>
            <ul class="list-group">
            <?php foreach ($participants as $p): 
                $participantId = $p['id']['_serialized'];
                $infoUrl = 'https://whatsapp-waha.brandon.my/api/contacts?contactId=' . urlencode($participantId) . '&session=default';
                $infoCh = curl_init($infoUrl);
                curl_setopt($infoCh, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($infoCh, CURLOPT_HTTPHEADER, ['accept: */*']);
                $infoResp = curl_exec($infoCh);
                curl_close($infoCh);
                $contact = json_decode($infoResp, true);
                $name = htmlspecialchars($contact['name'] ?? 'Unknown');
                $isBusiness = !empty($contact['isBusiness']) ? 'Business' : 'Person';
            ?>
                <li class="list-group-item participant">
                    <strong><?= $name ?></strong><br>
                    <small><?= htmlspecialchars($participantId) ?> | <?= $isBusiness ?></small>
                </li>
            <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php else: ?>
        <div class="alert alert-danger">Group not found or API error.</div>
    <?php endif; ?>
</div>

<!-- Reuse same modal and JS as original -->
<?= file_get_contents('editGroupModal.html'); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Same JS as original list page
let currentGroupId = '';
document.getElementById('editGroupModal').addEventListener('show.bs.modal', function(event) {
    const button = event.relatedTarget;
    currentGroupId = button.getAttribute('data-groupid');
    document.getElementById('currentGroupPic').src = button.getAttribute('data-grouppic');
    document.getElementById('newGroupPic').value = button.getAttribute('data-grouppic');
    document.getElementById('newGroupDesc').value = button.getAttribute('data-groupdesc');
});

function updateGroupPicture() {
    const newPicUrl = document.getElementById('newGroupPic').value;
    if (!newPicUrl) return alert('Please enter an image URL');

    fetch('https://whatsapp-waha.brandon.my/api/default/groups/' + encodeURIComponent(currentGroupId) + '/picture', {
        method: 'PUT',
        headers: { 'accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({
            file: {
                mimetype: 'image/jpeg',
                filename: 'group_picture.jpg',
                url: newPicUrl
            }
        })
    }).then(r => r.ok ? location.reload() : Promise.reject(r.statusText))
    .catch(e => alert('Error updating group picture: ' + e));
}

function updateGroupDescription() {
    const newDesc = document.getElementById('newGroupDesc').value;
    fetch('https://whatsapp-waha.brandon.my/api/default/groups/' + encodeURIComponent(currentGroupId) + '/description', {
        method: 'PUT',
        headers: { 'accept': '*/*', 'Content-Type': 'application/json' },
        body: JSON.stringify({ description: newDesc })
    }).then(r => r.ok ? location.reload() : Promise.reject(r.statusText))
    .catch(e => alert('Error updating group description: ' + e));
}
</script>
</body>
</html>
