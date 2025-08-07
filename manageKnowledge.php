<?php
// Check if user_id is provided
$userId = $_GET['user_id'] ?? null;
if (!$userId) {
    header("Location: /index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Knowledge Management <?=$userId?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .knowledge-item {
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 5px;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>Knowledge Management</h1>
        
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="input-group">
                    <span class="input-group-text" style="display:none;">User ID</span>
                    <input type="text" class="form-control" id="userIdInput" value="<?php echo $_GET['user_id'] ?? ''; ?>" style="display:none;">
                    <button class="btn btn-success" id="loadCalendarBtn" style="display:none;">Load Calendar</button>

                    <button class="btn btn-primary" id="addTaskBtn" style="margin-top:10px;"><i class="fas fa-plus me-2"></i>Add New Task</button> 

                    <a href="/?user_id=<?php echo $_GET['user_id'] ?? ''; ?>" style="margin-left:10px;margin-top:10px;">
                        <button class="btn btn-warning"><i class="fas fa-list me-2"></i>Tasks List</button>
                    </a>

                    <a href="/index.php/ticket?user_id=<?php echo $_GET['user_id'] ?? ''; ?>" style="margin-left:10px;margin-top:10px;">
                        <button class="btn btn-warning"><i class="fas fa-ticket-alt me-2"></i>Tickets</button>
                    </a>

                    <?php
                    $isGroup = isset($_GET['user_id']) && strpos($_GET['user_id'], '@g.us') !== false;
                    if($isGroup==true){
                    ?>
                    <a href="/index.php/manageGroup?user_id=<?php echo $_GET['user_id'] ?? ''; ?>">
                        <button class="btn btn-warning" style="margin-left:10px;margin-top:10px;"><i class="fas fa-users me-2"></i>ManageGroup</button>
                    </a>
                    <?php } ?>

                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div id="knowledge-list" class="mb-4"></div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Add/Edit Knowledge</h5>
                    </div>
                    <div class="card-body">
                        <form id="knowledge-form">
                            <input type="hidden" id="knowledge-id">
                            <div class="mb-3">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="title" required>
                            </div>
                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label>
                                <input type="text" class="form-control" id="category">
                            </div>
                            <div class="mb-3">
                                <label for="tags" class="form-label">Tags (comma separated)</label>
                                <input type="text" class="form-control" id="tags">
                            </div>
                            <div class="mb-3">
                                <label for="content" class="form-label">Content</label>
                                <textarea class="form-control" id="content" rows="5" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Save</button>
                            <button type="button" id="cancel-edit" class="btn btn-secondary" style="display:none;">Cancel</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const userId = '<?php echo $userId; ?>';
        
        // Load knowledge items
        function loadKnowledgeItems() {
            fetch(`/api/knowledge.php?user_id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    const knowledgeList = document.getElementById('knowledge-list');
                    knowledgeList.innerHTML = '';
                    
                    if (data.length === 0) {
                        knowledgeList.innerHTML = '<p>No knowledge items found.</p>';
                        return;
                    }
                    
                    data.forEach(item => {
                        const knowledgeItem = document.createElement('div');
                        knowledgeItem.className = 'knowledge-item';
                        knowledgeItem.innerHTML = `
                            <h3>${item.title}</h3>
                            ${item.category ? `<p><strong>Category:</strong> ${item.category}</p>` : ''}
                            ${item.tags ? `<p><strong>Tags:</strong> ${item.tags}</p>` : ''}
                            <p>${item.content}</p>
                            <small class="text-muted">Created: ${new Date(item.created_at).toLocaleString()}</small>
                            <div class="mt-2">
                                <button class="btn btn-sm btn-warning edit-btn" data-id="${item.id}">Edit</button>
                                <button class="btn btn-sm btn-danger delete-btn" data-id="${item.id}">Delete</button>
                            </div>
                        `;
                        knowledgeList.appendChild(knowledgeItem);
                    });
                    
                    // Add event listeners to edit and delete buttons
                    document.querySelectorAll('.edit-btn').forEach(btn => {
                        btn.addEventListener('click', editKnowledge);
                    });
                    
                    document.querySelectorAll('.delete-btn').forEach(btn => {
                        btn.addEventListener('click', deleteKnowledge);
                    });
                });
        }
        
        // Edit knowledge item
        function editKnowledge(e) {
            const knowledgeId = e.target.getAttribute('data-id');
            fetch(`/api/knowledge.php?id=${knowledgeId}`)
                .then(response => response.json())
                .then(item => {
                    document.getElementById('knowledge-id').value = item.id;
                    document.getElementById('title').value = item.title;
                    document.getElementById('category').value = item.category || '';
                    document.getElementById('tags').value = item.tags || '';
                    document.getElementById('content').value = item.content;
                    document.getElementById('cancel-edit').style.display = 'inline-block';
                });
        }
        
        // Delete knowledge item
        function deleteKnowledge(e) {
            if (confirm('Are you sure you want to delete this knowledge item?')) {
                const knowledgeId = e.target.getAttribute('data-id');
                fetch(`/api/knowledge.php?id=${knowledgeId}&user_id=${userId}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadKnowledgeItems();
                    }
                });
            }
        }
        
        // Cancel edit
        document.getElementById('cancel-edit').addEventListener('click', () => {
            document.getElementById('knowledge-form').reset();
            document.getElementById('knowledge-id').value = '';
            document.getElementById('cancel-edit').style.display = 'none';
        });
        
        // Submit form (create or update)
        document.getElementById('knowledge-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const knowledgeId = document.getElementById('knowledge-id').value;
            const method = knowledgeId ? 'PUT' : 'POST';
            const url = knowledgeId ? `/api/knowledge.php?id=${knowledgeId}&user_id=${userId}` : `/api/knowledge.php?user_id=${userId}`;
            
            const data = {
                title: document.getElementById('title').value,
                content: document.getElementById('content').value,
                category: document.getElementById('category').value,
                tags: document.getElementById('tags').value
            };
            
            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadKnowledgeItems();
                    this.reset();
                    document.getElementById('cancel-edit').style.display = 'none';
                }
            });
        });
        
        // Initial load
        loadKnowledgeItems();
    </script>
</body>
</html>