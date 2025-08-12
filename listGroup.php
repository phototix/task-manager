<?php
$apiUrl = 'https://waha.ezy.chat/api/default/groups?sortBy=id';
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .group-card {
            margin-bottom: 20px;
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
        .edit-btn {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .group-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <h2 class="mb-4">Manage Contacts</h2>

    <a href="/index.php/manageContacts?user_id=<?php echo $_GET['user_id'] ?? ''; ?>" style="margin-left:10px;margin-top:10px;">
        <button class="btn btn-warning" style="margin-top:10px;margin-bottom:10px;"><i class="fas fa-list me-2"></i>Manage Contacts</button>
    </a>

    <h2 class="mb-4">WhatsApp Group Listing</h2>
    <?php if (!empty($data)): ?>
        <?php foreach ($data as $group): 
            $groupId = $group['id']['_serialized'] ?? '';
            $groupName = htmlspecialchars($group['name'] ?? 'N/A');
            $groupDesc = htmlspecialchars($group['groupMetadata']['desc'] ?? 'No description');
            $picApi = 'https://waha.ezy.chat/api/default/groups/' . urlencode($groupId) . '/picture?refresh=false';
            $picCh = curl_init($picApi);
            curl_setopt($picCh, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($picCh, CURLOPT_HTTPHEADER, ['accept: application/json']);
            $picResponse = curl_exec($picCh);
            curl_close($picCh);
            $picData = json_decode($picResponse, true);
            $groupPic = $picData['url'] ?? 'https://cloud.webbypage.com/index.php/s/kwzFAtinnHtzDiy/download';
        ?>
            <div class="card group-card shadow-sm">
                <div class="card-body position-relative">
                    <a href="/?user_id=<?= htmlspecialchars($groupId) ?>">
                        <button class="btn btn-sm btn-outline-primary edit-btn" style="margin-top:50px;">
                            <i class="bi bi-list"></i> Tasks
                        </button>
                    </a>
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
                            <a href="index.php/manageGroup?user_id=<?= $groupId ?>">
                                <h5 class="card-title group-title mb-0"><?= $groupName ?></h5>
                            </a>
                            <small class="text-muted"><?= htmlspecialchars($groupId) ?></small>
                        </div>
                    </div>
                    
                    <p class="description"><strong>Description:</strong><br><?= $groupDesc ?></p>
                    <h6>Participants (<?= count($group['groupMetadata']['participants'] ?? []) ?>):</h6>
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
                            <img id="currentGroupPic" src="" class="group-avatar mb-2" alt="Current Picture">
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
</script>
</body>
</html>
