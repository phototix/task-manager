<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Calendar</title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .fc-event {
            cursor: pointer;
        }
        .priority-0 { background-color: #6610f2; border-color: #6610f2; color: #FFF; } /* General - Purple */
        .priority-1 { background-color: #dc3545; border-color: #dc3545; } /* Urgent */
        .priority-2 { background-color: #fd7e14; border-color: #fd7e14; } /* High */
        .priority-3 { background-color: #ffc107; border-color: #ffc107; color: #212529; } /* Medium */
        .priority-4 { background-color: #0d6efd; border-color: #0d6efd; } /* Low */
        .priority-5 { background-color: #6c757d; border-color: #6c757d; color: #FFF; } /* Appointment */
        .priority-6 { background-color: #6c757d; border-color: #6c757d; color: #FFF; } /* Appointment(booked) */
        .completed-task {
            text-decoration: line-through;
            opacity: 0.7;
        }
        .user-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        #taskModal .priority-badge {
            display: inline-block;
            padding: 0.25em 0.4em;
            border-radius: 0.25rem;
            color: white;
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="row mb-4">
            <div class="col-md-8 mx-auto text-center">
                <h1 class="mb-3"><img src="/list.png" style="height:20px;"> <span id="appTitle">Task Management</span></h1>
                <div class="user-info">
                    <h4 id="userGreeting">Welcome!</h4>
                    <p id="currentDate" class="text-muted"></p>
                </div>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="input-group">
                    <span class="input-group-text" style="display:none;">User ID</span>
                    <input type="text" class="form-control" id="userIdInput" value="<?php echo $_GET['user_id'] ?? ''; ?>" style="display:none;">
                    <button class="btn btn-success" id="loadCalendarBtn" style="display:none;">Load Calendar</button>
                    <button class="btn btn-primary" id="addTaskBtn"><i class="fas fa-plus me-2"></i>Add New Task</button> 
                    <a href="/?user_id=<?php echo $_GET['user_id'] ?? ''; ?>" style="margin-left:10px;">
                        <button class="btn btn-warning"><i class="fas fa-list me-2"></i>Tasks List</button>
                    </a>
                </div>
            </div>
        </div>
        <div id="calendar"></div>
    </div>

    <!-- Task Modal -->
    <div class="modal fade" id="taskModal" tabindex="-1" aria-labelledby="taskModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="taskModalLabel">Task Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="taskForm">
                        <input type="hidden" id="taskId">
                        <div class="mb-3">
                            <label for="taskDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="taskDescription" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="taskDate" class="form-label">Date</label>
                            <input type="date" class="form-control" id="taskDate" lang="en-GB" 
                               placeholder="DD/MM/YYYY" 
                               pattern="\d{2}/\d{2}/\d{4}" 
                               title="Please enter date in DD/MM/YYYY format" 
                               required>
                        </div>
                        <div class="mb-3">
                            <label for="taskTime" class="form-label">Time</label>
                            <input type="time" class="form-control" id="taskTime">
                        </div>
                        <div class="mb-3">
                            <label for="taskPriority" class="form-label">Priority</label>
                            <select class="form-select" id="taskPriority">
                                <option value="0">General</option>
                                <option value="1">Urgent</option>
                                <option value="2">High</option>
                                <option value="3">Medium</option>
                                <option value="4">Low</option>
                                <option value="5">Appointment</option>
                                <option value="6">Appointment (Booked)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="taskRemarks" class="form-label">Remarks</label>
                            <textarea class="form-control" id="taskRemarks" rows="2"></textarea>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="taskCompleted">
                            <label class="form-check-label" for="taskCompleted">
                                Task Completed
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveTaskBtn">Save Task</button>
                    <button type="button" class="btn btn-danger" id="deleteTaskBtn">Delete Task</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <!-- jQuery (for AJAX) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const userIdInput = document.getElementById('userIdInput');
            const loadCalendarBtn = document.getElementById('loadCalendarBtn');
            const addTaskBtn = document.getElementById('addTaskBtn');
            const taskModal = new bootstrap.Modal(document.getElementById('taskModal'));
            const saveTaskBtn = document.getElementById('saveTaskBtn');
            const deleteTaskBtn = document.getElementById('deleteTaskBtn');
            
            let calendar;
            let currentUserId = '';

            // Display user greeting
            $('#userGreeting').text(`Welcome, User ${userIdInput.value}!`);

            // Display current date
            const today = new Date();
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            $('#currentDate').text(today.toLocaleDateString('en-US', options));

            // Helper functions for date formatting
            function formatDateForDisplay(dateStr) {
                if (!dateStr) return '';
                const date = new Date(dateStr);
                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const year = date.getFullYear();
                return `${year}-${month}-${day}`;
            }

            function formatDateForStorage(dateStr) {
                if (!dateStr) return '';
                // Convert from DD/MM/YYYY to YYYY-MM-DD
                const parts = dateStr.split('/');
                if (parts.length === 3) {
                    return `${parts[2]}-${parts[1]}-${parts[0]}`;
                }
                return dateStr; // fallback
            }

            /**
             * Fetch user details from contacts API
             * @param {string} userId 
             * @returns {Promise} Promise with user details
             */
            function fetchUserDetails(userId) {
                return new Promise((resolve, reject) => {
                    
                    // Make API request
                    $.ajax({
                        url: `/api/contact.php?user_id=${userId}`,
                        type: 'GET',
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                resolve(response.data);
                            } else {
                                reject(response.message || 'Failed to fetch user details');
                            }
                        },
                        error: function(xhr, status, error) {
                            reject(error);
                        }
                    });
                });
            }

            initUserDetails();
            
            // Initialize calendar
            function initCalendar(userId) {
                if (calendar) {
                    calendar.destroy();
                }
                
                currentUserId = userId;
                
                calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay'
                    },
                    events: function(fetchInfo, successCallback, failureCallback) {
                        if (!currentUserId) {
                            failureCallback('User ID is required');
                            return;
                        }
                        
                        const startDate = fetchInfo.startStr.split('T')[0];
                        const endDate = fetchInfo.endStr.split('T')[0];
                        
                        $.ajax({
                            url: '/api/tasks.php',
                            data: {
                                user_id: currentUserId,
                                start: startDate,
                                end: endDate
                            },
                            success: function(response) {
                                const events = response.map(task => ({
                                    id: task.id,
                                    title: task.task_description,
                                    start: `${task.task_date}T${task.time || '00:00:00'}`,
                                    allDay: !task.time,
                                    extendedProps: {
                                        description: task.task_description,
                                        is_completed: task.is_completed,
                                        priority: task.priority,
                                        remarks: task.remarks,
                                        time: task.time
                                    },
                                    className: `priority-${task.priority} ${task.is_completed ? 'completed-task' : ''}`
                                }));
                                successCallback(events);
                            },
                            error: function(xhr, status, error) {
                                failureCallback(error);
                            }
                        });
                    },
                    eventClick: function(info) {
                        openTaskModal(info.event);
                    },
                    dateClick: function(info) {
                        openNewTaskModal(info.dateStr);
                    }
                });
                
                calendar.render();
            }
            
            // Open modal for existing task
            function openTaskModal(event) {
                document.getElementById('taskModalLabel').textContent = 'Edit Task';
                document.getElementById('taskId').value = event.id;
                document.getElementById('taskDescription').value = event.extendedProps.description;

                // Format date for display (convert from YYYY-MM-DD to DD/MM/YYYY)
                const eventDate = event.startStr.split('T')[0];
                console.log("BrowserDate:"+eventDate);
                document.getElementById('taskDate').value = formatDateForDisplay(eventDate);
                console.log("ConvertedDate:"+formatDateForDisplay(eventDate));

                document.getElementById('taskTime').value = event.extendedProps.time || '';
                document.getElementById('taskPriority').value = event.extendedProps.priority;
                document.getElementById('taskRemarks').value = event.extendedProps.remarks || '';
                document.getElementById('taskCompleted').checked = event.extendedProps.is_completed;
                
                deleteTaskBtn.style.display = 'inline-block';
                taskModal.show();
            }
            
            // Open modal for new task
            function openNewTaskModal(dateStr) {
                document.getElementById('taskModalLabel').textContent = 'Add New Task';
                document.getElementById('taskId').value = '';
                document.getElementById('taskDescription').value = '';
                document.getElementById('taskDate').value = formatDateForDisplay(dateStr);
                document.getElementById('taskTime').value = '';
                document.getElementById('taskPriority').value = '0';
                document.getElementById('taskRemarks').value = '';
                document.getElementById('taskCompleted').checked = false;
                
                deleteTaskBtn.style.display = 'none';
                taskModal.show();
            }
            
            // Save task (create or update)
            function saveTask() {
                const taskId = document.getElementById('taskId').value;
                const userId = document.getElementById('userIdInput').value;
                const taskData = {
                    user_id: currentUserId,
                    task_description: document.getElementById('taskDescription').value,
                    task_date: formatDateForStorage(document.getElementById('taskDate').value),
                    time: document.getElementById('taskTime').value || null,
                    priority: document.getElementById('taskPriority').value,
                    remarks: document.getElementById('taskRemarks').value || null,
                    is_completed: document.getElementById('taskCompleted').checked ? 1 : 0
                };
                
                const url = '/api/tasks.php' + `?user_id=<?php echo $_GET['user_id'] ?? ''; ?>` + (taskId ? `&id=${taskId}` : '');
                const method = taskId ? 'PUT' : 'POST';
                
                $.ajax({
                    url: url,
                    type: method,
                    data: JSON.stringify(taskData),
                    contentType: 'application/json',
                    success: function() {
                        calendar.refetchEvents();
                        taskModal.hide();
                    },
                    error: function(xhr, status, error) {
                        alert('Error saving task: ' + error);
                    }
                });
            }
            
            // Delete task
            function deleteTask() {
                const taskId = document.getElementById('taskId').value;
                const userId = document.getElementById('userIdInput').value;
                
                if (!confirm('Are you sure you want to delete this task?')) {
                    return;
                }
                
                $.ajax({
                    url: `/api/tasks.php?id=${taskId}`,
                    type: 'DELETE',
                    success: function() {
                        calendar.refetchEvents();
                        taskModal.hide();
                    },
                    error: function(xhr, status, error) {
                        alert('Error deleting task: ' + error);
                    }
                });
            }
                
            function initUserDetails() {
                const userId = document.getElementById('userIdInput').value;
                
                if (userId) {
                    fetchUserDetails(userId)
                        .then(user => {
                            // Update UI with user details
                            $('#userGreeting').text(`Welcome, ${user.name || 'User'}!`);
                            
                            // Set language preference
                            if (user.language) {
                                // Implement language switching if needed
                                console.log('User language:', user.language);
                            }

                            if （user.type=="personal"）{
                                $('#appTitle').text(`Personal Task Management`);
                            }else{
                                $('#appTitle').text(`Group Task Management`);
                            }
                            
                            // Use other user details as needed
                            console.log('User details:', user);
                        })
                        .catch(error => {
                            console.error('Error loading user details:', error);
                            $('#userGreeting').text(`Welcome, User ${userId}!`);
                        });
                }
            }
            
            // Event listeners
            loadCalendarBtn.addEventListener('click', function() {
                const userId = userIdInput.value.trim();
                if (userId) {
                    // Update URL with user_id parameter
                    window.history.pushState({}, '', `?user_id=${userId}`);
                    initCalendar(userId);
                } else {
                    alert('Please enter a User ID');
                }
            });
            
            addTaskBtn.addEventListener('click', function() {
                if (!currentUserId) {
                    alert('Please load a calendar first by entering a User ID');
                    return;
                }
                openNewTaskModal(new Date().toISOString().split('T')[0]);
            });
            
            saveTaskBtn.addEventListener('click', saveTask);
            deleteTaskBtn.addEventListener('click', deleteTask);
            
            // Initialize calendar if user_id is in URL
            const urlParams = new URLSearchParams(window.location.search);
            const userIdFromUrl = urlParams.get('user_id');
            
            if (userIdFromUrl) {
                userIdInput.value = userIdFromUrl;
                initCalendar(userIdFromUrl);
            }
        });
    </script>
</body>
</html>
