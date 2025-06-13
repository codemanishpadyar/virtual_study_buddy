<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Planner - Virtual Study Buddy</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 20px;
            padding: 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .task-section, .calendar-section {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            height: fit-content;
        }

        .section-title {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.5em;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 2px solid #f0f0f0;
        }

        .section-title h2 {
            font-weight: 600;
            color: #667eea;
        }

        .gradient-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .gradient-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
        }

        .task-list {
            margin-top: 20px;
        }

        .task-item {
            background: #f8f9ff;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
            border: 1px solid #e0e4ff;
        }

        .task-item:hover {
            transform: translateX(5px);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .task-info {
            flex-grow: 1;
        }

        .task-title {
            font-weight: 500;
            color: #333;
            margin-bottom: 5px;
        }

        .task-meta {
            font-size: 12px;
            color: #666;
            display: flex;
            gap: 15px;
            margin-top: 5px;
        }

        .task-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .task-meta i {
            color: #667eea;
        }

        .task-actions {
            display: flex;
            gap: 8px;
        }

        .task-btn {
            width: 36px;
            height: 36px;
            padding: 0;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            color: white;
        }

        .task-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .task-btn i {
            font-size: 14px;
        }

        .edit-btn {
            background: #667eea;
        }

        .delete-btn {
            background: #ff6b6b;
        }

        .complete-btn {
            background: #2ecc71;
        }

        .calendar {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 8px;
            margin-top: 20px;
            padding: 10px;
            background: #f8f9ff;
            border-radius: 12px;
            border: 1px solid #e0e4ff;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .calendar-nav {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .calendar-nav button {
            width: 36px;
            height: 36px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .calendar-nav button:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 10px rgba(102, 126, 234, 0.2);
        }

        .calendar-day {
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
            border: 1px solid #e0e4ff;
            position: relative;
        }

        .calendar-day.header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 500;
            border: none;
        }

        .calendar-day.empty {
            background: transparent;
            border: none;
            cursor: default;
        }

        .calendar-day:not(.empty):not(.header):hover {
            background: #f0f2ff;
            transform: translateY(-2px);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .calendar-day.selected {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
        }

        #currentMonth {
            font-size: 16px;
            font-weight: 500;
            color: #333;
            min-width: 150px;
            text-align: center;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #e1e1e1;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2);
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .task-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid #e0e4ff;
        }

        .stat-number {
            font-size: 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #666;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
            }

            .task-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <!-- Simple navigation for testing -->
    <div style="background: white; padding: 15px; margin-bottom: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);">
        <h1 style="color: #667eea; margin: 0;">Study Planner</h1>
    </div>

    <div class="container">
        <div class="task-section">
            <div class="section-title">
                <h2>Tasks</h2>
                <button class="gradient-btn" onclick="showAddTaskModal()">
                    <i class="fas fa-plus"></i> Add Task
                </button>
            </div>

            <div class="task-stats">
                <div class="stat-card">
                    <div class="stat-number" id="total-tasks">0</div>
                    <div class="stat-label">Total Tasks</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="completed-tasks">0</div>
                    <div class="stat-label">Completed</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="pending-tasks">0</div>
                    <div class="stat-label">Pending</div>
                </div>
            </div>

            <div id="taskList" class="task-list">
                <!-- Sample Task (for visibility testing) -->
                <div class="task-item">
                    <div class="task-info">
                        <div class="task-title">Sample Task</div>
                        <div class="task-meta">
                            <span><i class="fas fa-calendar"></i> Mar 20, 2024</span>
                            <span><i class="fas fa-align-left"></i> Sample description</span>
                        </div>
                    </div>
                    <div class="task-actions">
                        <button class="task-btn edit-btn">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="task-btn delete-btn">
                            <i class="fas fa-trash"></i>
                        </button>
                        <button class="task-btn complete-btn">
                            <i class="fas fa-check"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="calendar-section">
            <div class="section-title">
                <h2>Calendar</h2>
                <div class="calendar-nav">
                    <button onclick="prevMonth()">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <span id="currentMonth"></span>
                    <button onclick="nextMonth()">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
            <div id="calendar" class="calendar">
                <!-- Calendar will be generated by JavaScript -->
            </div>
        </div>
    </div>

    <!-- Add/Edit Task Modal -->
    <div class="modal" id="taskModal">
        <div class="modal-content">
            <h2 id="modalTitle">Add New Task</h2>
            <form id="taskForm" onsubmit="event.preventDefault(); handleTaskSubmit();">
                <div class="form-group">
                    <label for="taskTitle">Title</label>
                    <input type="text" id="taskTitle" required>
                </div>
                <div class="form-group">
                    <label for="taskDescription">Description</label>
                    <textarea id="taskDescription" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="taskDueDate">Due Date</label>
                    <input type="date" id="taskDueDate" required>
                </div>
                <input type="hidden" id="taskId">
                <div class="modal-actions">
                    <button type="button" class="gradient-btn" onclick="closeTaskModal()" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        Cancel
                    </button>
                    <button type="submit" class="gradient-btn">
                        Save Task
                    </button>
                </div>
</form>
        </div>
    </div>

    <script>
        // Initialize variables
        let currentDate = new Date();
        let selectedDate = null;
        let editingTaskId = null;

        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            generateCalendar();
            loadTasks();
        });

        // Show add task modal
        function showAddTaskModal(date = null) {
            document.getElementById('modalTitle').textContent = 'Add New Task';
            document.getElementById('taskForm').reset();
            document.getElementById('taskId').value = '';
            if (date) {
                document.getElementById('taskDueDate').value = formatDateForInput(date);
            }
            document.getElementById('taskModal').style.display = 'flex';
        }

        // Close task modal
        function closeTaskModal() {
            document.getElementById('taskModal').style.display = 'none';
            editingTaskId = null;
        }

        // Generate calendar
        function generateCalendar() {
            const calendar = document.getElementById('calendar');
            const currentMonthElement = document.getElementById('currentMonth');
            
            // Clear previous calendar
            calendar.innerHTML = '';
            
            // Set current month and year
            currentMonthElement.textContent = currentDate.toLocaleString('default', { month: 'long', year: 'numeric' });
            
            // Add day headers
            const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            days.forEach(day => {
                const dayHeader = document.createElement('div');
                dayHeader.className = 'calendar-day header';
                dayHeader.textContent = day;
                calendar.appendChild(dayHeader);
            });
            
            // Get first day of month and total days
            const firstDay = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
            const lastDay = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0);
            
            // Add empty cells for days before first day of month
            for (let i = 0; i < firstDay.getDay(); i++) {
                const emptyDay = document.createElement('div');
                emptyDay.className = 'calendar-day empty';
                calendar.appendChild(emptyDay);
            }
            
            // Add days of month
            for (let i = 1; i <= lastDay.getDate(); i++) {
                const dayElement = document.createElement('div');
                dayElement.className = 'calendar-day';
                dayElement.textContent = i;
                
                // Check if it's today's date
                const today = new Date();
                if (today.getDate() === i && 
                    today.getMonth() === currentDate.getMonth() && 
                    today.getFullYear() === currentDate.getFullYear()) {
                    dayElement.classList.add('selected');
                }
                
                const currentDateString = `${currentDate.getFullYear()}-${(currentDate.getMonth() + 1).toString().padStart(2, '0')}-${i.toString().padStart(2, '0')}`;
                
                dayElement.addEventListener('click', () => {
                    // Remove selected class from all days
                    document.querySelectorAll('.calendar-day').forEach(day => {
                        day.classList.remove('selected');
                    });
                    // Add selected class to clicked day
                    dayElement.classList.add('selected');
                    selectedDate = currentDateString;
                    showAddTaskModal(new Date(currentDateString));
                });
                
                calendar.appendChild(dayElement);
            }
            
            // Add empty cells for remaining days to complete the grid
            const totalCells = calendar.children.length;
            const remainingCells = 42 - totalCells; // 6 rows × 7 days = 42 total cells
            for (let i = 0; i < remainingCells; i++) {
                const emptyDay = document.createElement('div');
                emptyDay.className = 'calendar-day empty';
                calendar.appendChild(emptyDay);
            }
        }

        // Navigation functions
        function prevMonth() {
            currentDate.setMonth(currentDate.getMonth() - 1);
            generateCalendar();
        }

        function nextMonth() {
            currentDate.setMonth(currentDate.getMonth() + 1);
            generateCalendar();
        }

        // Utility functions
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('default', { year: 'numeric', month: 'short', day: 'numeric' });
        }

        function formatDateForInput(dateString) {
            const date = new Date(dateString);
            return date.toISOString().split('T')[0];
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('taskModal');
            if (event.target == modal) {
                closeTaskModal();
            }
        }

        // Load tasks from server
        function loadTasks() {
            fetch('task_operations.php')
                .then(response => response.json())
                .then(tasks => {
                    const taskList = document.getElementById('taskList');
                    taskList.innerHTML = '';
                    
                    if (Array.isArray(tasks)) {
                        tasks.forEach(task => {
                            const taskElement = createTaskElement(task);
                            taskList.appendChild(taskElement);
                        });
                    }
                    
                    updateTaskStats();
                })
                .catch(error => {
                    console.error('Error loading tasks:', error);
                    document.getElementById('taskList').innerHTML = '<p>Error loading tasks. Please try again later.</p>';
                });
        }

        // Create task element
        function createTaskElement(task) {
            const div = document.createElement('div');
            div.className = `task-item ${task.status === 'completed' ? 'completed' : ''}`;
            div.innerHTML = `
                <div class="task-info">
                    <div class="task-title">${task.title}</div>
                    <div class="task-meta">
                        <span><i class="fas fa-calendar"></i> ${formatDate(task.due_date)}</span>
                        ${task.description ? `<span><i class="fas fa-align-left"></i> ${task.description}</span>` : ''}
                    </div>
                </div>
                <div class="task-actions">
                    <button class="task-btn edit-btn" onclick="editTask(${task.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="task-btn delete-btn" onclick="deleteTask(${task.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                    <button class="task-btn complete-btn" onclick="completeTask(${task.id})">
                        <i class="fas fa-check"></i>
                    </button>
                </div>
            `;
            return div;
        }

        // Handle task form submission
        function handleTaskSubmit() {
            const title = document.getElementById('taskTitle').value;
            const description = document.getElementById('taskDescription').value;
            const dueDate = document.getElementById('taskDueDate').value;
            const taskId = document.getElementById('taskId').value;

            const formData = new FormData();
            formData.append('title', title);
            formData.append('description', description);
            formData.append('due_date', dueDate);

            if (taskId) {
                formData.append('task_id', taskId);
                formData.append('action', 'update');
            } else {
                formData.append('action', 'create');
            }

            fetch('task_operations.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadTasks();
                    closeTaskModal();
                } else {
                    alert('Failed to save task');
                }
            })
            .catch(error => console.error('Error saving task:', error));
        }

        // Edit task
        function editTask(taskId) {
            fetch(`task_operations.php?task_id=${taskId}`)
                .then(response => response.json())
                .then(task => {
                    document.getElementById('modalTitle').textContent = 'Edit Task';
                    document.getElementById('taskTitle').value = task.title;
                    document.getElementById('taskDescription').value = task.description || '';
                    document.getElementById('taskDueDate').value = formatDateForInput(task.due_date);
                    document.getElementById('taskId').value = task.id;
                    document.getElementById('taskModal').style.display = 'flex';
                })
                .catch(error => console.error('Error loading task:', error));
        }

        // Delete task
        function deleteTask(taskId) {
            if (confirm('Are you sure you want to delete this task?')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('task_id', taskId);

                fetch('task_operations.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadTasks();
                    } else {
                        alert('Failed to delete task');
                    }
                })
                .catch(error => console.error('Error deleting task:', error));
            }
        }

        // Complete task
        function completeTask(taskId) {
            const formData = new FormData();
            formData.append('action', 'complete');
            formData.append('task_id', taskId);

            fetch('task_operations.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadTasks();
                } else {
                    alert('Failed to complete task');
                }
            })
            .catch(error => console.error('Error completing task:', error));
        }

        // Update task statistics
        function updateTaskStats() {
            fetch('task_operations.php?action=stats')
                .then(response => response.json())
                .then(stats => {
                    document.getElementById('total-tasks').textContent = stats.total;
                    document.getElementById('completed-tasks').textContent = stats.completed;
                    document.getElementById('pending-tasks').textContent = stats.pending;
                })
                .catch(error => console.error('Error loading stats:', error));
        }
    </script>
</body>
</html>
