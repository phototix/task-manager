<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .task-card {
            transition: all 0.3s ease;
            margin-bottom: 15px;
        }
        .task-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .completed {
            opacity: 0.7;
            background-color: #f8f9fa;
        }
        .completed .task-description {
            text-decoration: line-through;
            color: #6c757d;
        }
        .priority-1 { border-left: 4px solid #dc3545; } /* Urgent */
        .priority-2 { border-left: 4px solid #fd7e14; } /* High */
        .priority-3 { border-left: 4px solid #ffc107; } /* Medium */
        .priority-4 { border-left: 4px solid #198754; } /* Low */
        .priority-5 { border-left: 4px solid #0dcaf0; } /* Optional */
        .user-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="row mb-4">
            <div class="col-md-8 mx-auto text-center">
                <h1 class="mb-3">Task Management System</h1>
                <div class="user-info">
                    <h4 id="userGreeting">Welcome!</h4>
                    <p id="currentDate" class="text-muted"></p>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-8 mx-auto">
                <button class="btn btn-primary add-task-btn"><i class="fas fa-plus me-2"></i>Add New Task</button>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3>Today's Tasks</h3>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-secondary filter-btn active" data-filter="all">All</button>
                        <button type="button" class="btn btn-outline-secondary filter-btn" data-filter="pending">Pending</button>
                        <button type="button" class="btn btn-outline-secondary filter-btn" data-filter="completed">Completed</button>
                    </div>
                </div>

                <div id="tasksContainer">
                    <!-- Tasks will be loaded here -->
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading tasks...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Task Add Modal -->
    <div class="modal fade" id="addTaskModal" tabindex="-1" aria-labelledby="addTaskModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="taskForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Add NewTask</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="taskDescription" class="form-label">Task Description</label>
                            <textarea class="form-control" id="taskDescription" rows="2" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="taskPriority" class="form-label">Priority</label>
                                <select class="form-select" id="taskPriority">
                                    <option value="1">Urgent</option>
                                    <option value="2">High</option>
                                    <option value="3" selected>Medium</option>
                                    <option value="4">Low</option>
                                    <option value="5">Optional</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="taskTime" class="form-label">Time (optional)</label>
                                <input type="time" class="form-control" id="taskTime">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="taskRemarks" class="form-label">Remarks (optional)</label>
                            <textarea class="form-control" id="taskRemarks" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Add Task
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Task Edit Modal -->
    <div class="modal fade" id="editTaskModal" tabindex="-1" aria-labelledby="editTaskModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTaskModalLabel">Edit Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editTaskForm">
                        <input type="hidden" id="editTaskId">
                        <div class="mb-3">
                            <label for="editTaskDescription" class="form-label">Task Description</label>
                            <textarea class="form-control" id="editTaskDescription" rows="2" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editTaskPriority" class="form-label">Priority</label>
                                <select class="form-select" id="editTaskPriority">
                                    <option value="1">Urgent</option>
                                    <option value="2">High</option>
                                    <option value="3">Medium</option>
                                    <option value="4">Low</option>
                                    <option value="5">Optional</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editTaskTime" class="form-label">Time (optional)</label>
                                <input type="time" class="form-control" id="editTaskTime">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="editTaskRemarks" class="form-label">Remarks (optional)</label>
                            <textarea class="form-control" id="editTaskRemarks" rows="2"></textarea>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="editTaskCompleted">
                            <label class="form-check-label" for="editTaskCompleted">
                                Mark as completed
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger me-auto" id="deleteTaskBtn">
                        <i class="fas fa-trash me-1"></i> Delete
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveTaskBtn">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Get user_id from URL parameter
            const urlParams = new URLSearchParams(window.location.search);
            const userId = urlParams.get('user_id');
            
            if (!userId) {
                alert('User ID is required in the URL parameter (e.g., ?user_id=123)');
                return;
            }
            
            // Display user greeting
            $('#userGreeting').text(`Welcome, User ${userId}!`);
            
            // Display current date
            const today = new Date();
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            $('#currentDate').text(today.toLocaleDateString('en-US', options));
            
            // Load tasks
            loadTasks();

            // Add task modal
            let addTaskModal = new bootstrap.Modal(document.getElementById('addTaskModal'));
            $(document).on('click', '.add-task-btn', function() {
                addTaskModal.show();
            });
            
            // Add new task
            $('#taskForm').submit(function(e) {
                e.preventDefault();
                
                const taskData = {
                    user_id: userId,
                    task_description: $('#taskDescription').val(),
                    priority: $('#taskPriority').val(),
                    remarks: $('#taskRemarks').val(),
                    time: $('#taskTime').val()
                };
                
                $.ajax({
                    url: `api/tasks.php?user_id=${userId}`,
                    type: 'POST',
                    data: JSON.stringify(taskData),
                    contentType: 'application/json',
                    success: function(response) {
                        if (response.success) {
                            $('#taskForm')[0].reset();
                            loadTasks();
                            addTaskModal.hide();
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Error adding task: ' + error);
                    }
                });
            });
            
            // Filter tasks
            $('.filter-btn').click(function() {
                $('.filter-btn').removeClass('active');
                $(this).addClass('active');
                const filter = $(this).data('filter');
                
                if (filter === 'all') {
                    $('.task-card').show();
                } else if (filter === 'pending') {
                    $('.task-card').show();
                    $('.task-card.completed').hide();
                } else if (filter === 'completed') {
                    $('.task-card').hide();
                    $('.task-card.completed').show();
                }
            });
            
            // Edit task modal
            let editTaskModal = new bootstrap.Modal(document.getElementById('editTaskModal'));
            
            $(document).on('click', '.edit-task-btn', function() {
                const taskId = $(this).data('task-id');
                
                $.ajax({
                    url: `api/tasks.php?id=${taskId}`,
                    type: 'GET',
                    success: function(task) {
                        $('#editTaskId').val(task.id);
                        $('#editTaskDescription').val(task.task_description);
                        $('#editTaskPriority').val(task.priority);
                        $('#editTaskTime').val(task.time);
                        $('#editTaskRemarks').val(task.remarks);
                        $('#editTaskCompleted').prop('checked', task.is_completed == 1);
                        
                        editTaskModal.show();
                    },
                    error: function(xhr, status, error) {
                        alert('Error loading task: ' + error);
                    }
                });
            });
            
            // Save edited task
            $('#saveTaskBtn').click(function() {
                const taskId = $('#editTaskId').val();
                const taskData = {
                    task_description: $('#editTaskDescription').val(),
                    priority: $('#editTaskPriority').val(),
                    time: $('#editTaskTime').val(),
                    remarks: $('#editTaskRemarks').val(),
                    is_completed: $('#editTaskCompleted').is(':checked') ? 1 : 0
                };
                
                $.ajax({
                    url: `api/tasks.php?id=${taskId}`,
                    type: 'PUT',
                    data: JSON.stringify(taskData),
                    contentType: 'application/json',
                    success: function(response) {
                        if (response.success) {
                            editTaskModal.hide();
                            loadTasks();
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Error updating task: ' + error);
                    }
                });
            });
            
            // Delete task
            $('#deleteTaskBtn').click(function() {
                if (confirm('Are you sure you want to delete this task?')) {
                    const taskId = $('#editTaskId').val();
                    
                    $.ajax({
                        url: `api/tasks.php?id=${taskId}`,
                        type: 'DELETE',
                        success: function(response) {
                            if (response.success) {
                                editTaskModal.hide();
                                loadTasks();
                            } else {
                                alert('Error: ' + response.message);
                            }
                        },
                        error: function(xhr, status, error) {
                            alert('Error deleting task: ' + error);
                        }
                    });
                }
            });
            
            // Toggle task completion
            $(document).on('click', '.complete-task-btn', function() {
                const taskId = $(this).data('task-id');
                const isCompleted = $(this).is(':checked') ? 1 : 0;
                
                $.ajax({
                    url: `api/tasks.php?id=${taskId}`,
                    type: 'PATCH',
                    data: JSON.stringify({ is_completed: isCompleted }),
                    contentType: 'application/json',
                    success: function(response) {
                        if (!response.success) {
                            alert('Error: ' + response.message);
                            loadTasks(); // Reload to revert UI if error
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Error updating task: ' + error);
                        loadTasks(); // Reload to revert UI if error
                    }
                });
            });
            
            // Function to load tasks
            function loadTasks() {
                $.ajax({
                    url: `api/tasks.php?user_id=${userId}`,
                    type: 'GET',
                    success: function(tasks) {
                        let html = '';
                        
                        if (tasks.length === 0) {
                            html = `<div class="alert alert-info">No tasks found for today. Add a new task to get started!</div>`;
                        } else {
                            tasks.forEach(task => {
                                const priorityClass = `priority-${task.priority}`;
                                const completedClass = task.is_completed == 1 ? 'completed' : '';
                                const priorityText = getPriorityText(task.priority);
                                const timeDisplay = task.time ? `<small class="text-muted"><i class="far fa-clock me-1"></i>${task.time}</small>` : '';
                                
                                html += `
                                <div class="card task-card ${priorityClass} ${completedClass}">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input complete-task-btn" type="checkbox" 
                                                    id="complete-${task.id}" ${task.is_completed == 1 ? 'checked' : ''}
                                                    data-task-id="${task.id}">
                                            </div>
                                            <div class="flex-grow-1 mx-3">
                                                <p class="card-text task-description mb-1">${task.task_description}</p>
                                                ${timeDisplay}
                                                ${task.remarks ? `<p class="text-muted small mt-2 mb-0">${task.remarks}</p>` : ''}
                                            </div>
                                            <div class="d-flex flex-column align-items-end">
                                                <span class="badge ${getPriorityBadgeClass(task.priority)} mb-2">${priorityText}</span>
                                                <button class="btn btn-sm btn-outline-secondary edit-task-btn" data-task-id="${task.id}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                `;
                            });
                        }
                        
                        $('#tasksContainer').html(html);
                    },
                    error: function(xhr, status, error) {
                        $('#tasksContainer').html(`<div class="alert alert-danger">Error loading tasks: ${error}</div>`);
                    }
                });
            }
            
            // Helper functions
            function getPriorityText(priority) {
                const priorities = {
                    1: 'Urgent',
                    2: 'High',
                    3: 'Medium',
                    4: 'Low',
                    5: 'Optional'
                };
                return priorities[priority] || 'Medium';
            }
            
            function getPriorityBadgeClass(priority) {
                const classes = {
                    1: 'bg-danger',
                    2: 'bg-warning text-dark',
                    3: 'bg-info text-dark',
                    4: 'bg-success',
                    5: 'bg-primary'
                };
                return classes[priority] || 'bg-secondary';
            }
        });
    </script>
</body>
</html>