<?php
// Get group ID from URL parameter
$groupId = isset($_GET['user_id']) ? $_GET['user_id'] : '';

if (empty($groupId)) {
    die('<div class="alert alert-danger">No group ID specified. Please add ?user_id=GROUP_ID to the URL</div>');
}

$apiUrl = 'https://waha.ezy.chat/api/default/groups/' . urlencode($groupId);
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'accept: application/json'
]);
$response = curl_exec($ch);
curl_close($ch);
$group = json_decode($response, true);

// Get group picture
$picApi = 'https://waha.ezy.chat/api/default/groups/' . urlencode($groupId) . '/picture?refresh=false';
$picCh = curl_init($picApi);
curl_setopt($picCh, CURLOPT_RETURNTRANSFER, true);
curl_setopt($picCh, CURLOPT_HTTPHEADER, ['accept: application/json']);
$picResponse = curl_exec($picCh);
curl_close($picCh);
$picData = json_decode($picResponse, true);
$groupPic = $picData['url'] ?? 'https://cloud.i-dc.institute/index.php/s/CcYDwCCJjYX8qgY/download';
?>
<!DOCTYPE html>
<html>
<head>
    <title>WhatsApp Group Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .group-card {
            margin: 20px auto;
            max-width: 800px;
            transition: transform 0.2s;
        }
        .group-card:hover {
            transform: translateY(-2px);
        }
        .group-title {
            font-weight: bold;
        }
        .participant {
            font-family: monospace;
        }
        .description {
            white-space: pre-wrap;
        }
        .manage-task-btn {
            position: absolute;
            right: 10px;
            top: calc(10px + var(--edit-btn-height, 38px) + 8px); /* 8px margin below */
        }
        .edit-btn {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .group-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <?php if (!empty($group)): ?>
        <div class="card group-card shadow-sm">
            <div class="card-body position-relative">
                <button class="btn btn-sm btn-outline-primary edit-btn" 
                        data-bs-toggle="modal" 
                        data-bs-target="#editGroupModal"
                        data-groupid="<?= htmlspecialchars($groupId) ?>"
                        data-groupname="<?= htmlspecialchars($group['name'] ?? 'N/A') ?>"
                        data-groupdesc="<?= htmlspecialchars($group['groupMetadata']['desc'] ?? '') ?>"
                        data-grouppic="<?= htmlspecialchars($groupPic) ?>">
                    <i class="bi bi-pencil"></i> Edit
                </button>
                <a href="/?user_id=<?php echo $_GET['user_id'] ?? ''; ?>">
                    <button class="btn btn-sm btn-outline-primary manage-task-btn">Manage Tasks</button>
                </a>
                <div class="d-flex align-items-center mb-3">
                    <img src="<?= htmlspecialchars($groupPic) ?>" class="group-avatar" alt="Group Picture">
                    <div>
                        <h3 class="card-title group-title mb-0"><?= htmlspecialchars($group['name'] ?? 'N/A') ?></h3>
                        <small class="text-muted"><?= htmlspecialchars($groupId) ?></small>
                    </div>
                </div>
                
                <p class="description"><strong>Description:</strong><br><?= htmlspecialchars($group['groupMetadata']['desc'] ?? 'No description') ?></p>
                <?php
                $participants = $group['groupMetadata']['participants'] ?? [];
                ?>
                <h6>Participants (<?= count($participants) ?>):</h6>
                <ul class="list-group">
                <?php foreach ($participants as $p): 
                    $participantId = $p['id']['_serialized'];
                    $infoUrl = 'https://waha.ezy.chat/api/contacts?contactId=' . urlencode($participantId) . '&session=default';
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
        <div class="alert alert-danger">Group not found or error fetching group data.</div>
    <?php endif; ?>
</div>

<!-- Edit Group Modal -->
<div class="modal fade" id="editGroupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Group</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs" id="editGroupTabs">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#editPictureTab">Picture</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#editDescTab">Description</a>
                    </li>
                </ul>
                
                <div class="tab-content p-3">
                    <div class="tab-pane fade show active" id="editPictureTab">
                        <div class="text-center mb-3">
                            <img id="currentGroupPic" src="" class="group-avatar mb-2" style="width: 120px; height: 120px;" alt="Current Picture">
                        </div>
                        <div class="mb-3">
                            <label for="newGroupPic" class="form-label">Image URL</label>
                            <input type="text" class="form-control" id="newGroupPic" placeholder="https://example.com/image.jpg">
                        </div>
                        <button class="btn btn-primary" onclick="updateGroupPicture()">
                            <i class="bi bi-image"></i> Update Picture
                        </button>
                    </div>
                    
                    <div class="tab-pane fade" id="editDescTab">
                        <div class="mb-3">
                            <label for="newGroupDesc" class="form-label">Description</label>
                            <textarea class="form-control" id="newGroupDesc" rows="5"></textarea>
                        </div>
                        <button class="btn btn-primary" onclick="updateGroupDescription()">
                            <i class="bi bi-text-paragraph"></i> Update Description
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Store current group ID globally
let currentGroupId = '';
// Initialize modal with group data
document.getElementById('editGroupModal').addEventListener('show.bs.modal', function(event) {
    const button = event.relatedTarget;
    currentGroupId = button.getAttribute('data-groupid');
    
    // Set current values in modal
    document.getElementById('currentGroupPic').src = button.getAttribute('data-grouppic');
    document.getElementById('newGroupPic').value = button.getAttribute('data-grouppic');
    document.getElementById('newGroupDesc').value = button.getAttribute('data-groupdesc');
});

function updateGroupPicture() {
    const newPicUrl = document.getElementById('newGroupPic').value;
    
    if (!newPicUrl) {
        alert('Please enter an image URL');
        return;
    }
    
    fetch('https://waha.ezy.chat/api/default/groups/' + encodeURIComponent(currentGroupId) + '/picture', {
        method: 'PUT',
        headers: {
            'accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            file: {
                mimetype: 'image/jpeg',
                filename: 'group_picture.jpg',
                url: newPicUrl
            }
        })
    })
    .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    })
    .then(data => {
        alert('Group picture updated successfully!');
        location.reload(); // Refresh to show changes
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating group picture: ' + error.message);
    });
}

function updateGroupDescription() {
    const newDesc = document.getElementById('newGroupDesc').value;
    
    fetch('https://waha.ezy.chat/api/default/groups/' + encodeURIComponent(currentGroupId) + '/description', {
        method: 'PUT',
        headers: {
            'accept': '*/*',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            description: newDesc
        })
    })
    .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.text();
    })
    .then(() => {
        alert('Group description updated successfully!');
        location.reload(); // Refresh to show changes
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating group description: ' + error.message);
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const editBtn = document.querySelector('.edit-btn');
    if (editBtn) {
        document.documentElement.style.setProperty('--edit-btn-height', editBtn.offsetHeight + 'px');
    }
});

</script>
</body>
</html>
