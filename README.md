# Task Management App - PHP Challenge 3

A complete task management application built with PHP, MySQL, and vanilla JavaScript featuring user authentication, CRUD operations, and dynamic frontend-backend interaction.

## 🚀 Features

### Core Functionality
- **User Authentication**: Secure login/register system with password hashing
- **Task Management**: Create, read, update, and delete tasks
- **Real-time Updates**: AJAX-powered status toggles without page reloads
- **Task Ownership**: Users can only access their own tasks
- **Statistics Dashboard**: Live counts of pending/completed tasks

### Security Features
- **CSRF Protection**: All state-changing actions use CSRF tokens
- **Input Validation**: Server-side validation for all user inputs
- **SQL Injection Protection**: Prepared statements throughout
- **Session Management**: Secure session handling
- **Ownership Validation**: Tasks can only be modified by their owners

### User Experience
- **Responsive Design**: Works on desktop and mobile devices
- **Dynamic Interface**: Status updates without page reloads
- **Visual Feedback**: Loading states and success/error messages
- **Confirmation Dialogs**: Prevent accidental deletions

## 📁 Project Structure

```
task-manager/
├── index.php              # Redirect to dashboard or login
├── login.php              # User login form
├── register.php           # User registration form
├── dashboard.php          # Main task management interface
├── logout.php             # Session cleanup
├── includes/
│   ├── database.php       # Database connection and utilities
│   ├── create_task.php    # Task creation handler
│   ├── update_task.php    # Task status update handler
│   └── delete_task.php    # Task deletion handler
└── README.md             # This file
```

## 🛠️ Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)

### Step 1: Database Setup

1. Create a MySQL database:
```sql
CREATE DATABASE task_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Create the tables by running the SQL from the schema file:
```sql
-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tasks table
CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    status ENUM('pending', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Indexes for performance
CREATE INDEX idx_tasks_user_id ON tasks(user_id);
CREATE INDEX idx_tasks_status ON tasks(status);
CREATE INDEX idx_tasks_created_at ON tasks(created_at);
```

### Step 2: Configuration

1. Update database credentials in `includes/database.php`:
```php
$host = 'localhost';
$dbname = 'task_manager';
$username = 'your_username';
$password = 'your_password';
```

### Step 3: File Deployment

1. Upload all files to your web server
2. Ensure proper permissions (755 for directories, 644 for files)
3. Make sure the `includes/` directory is accessible by PHP

## 🔒 Security Implementation

### CSRF Protection
- All state-changing operations require CSRF tokens
- Tokens are generated per session and validated on each request
- AJAX requests include tokens in the request body

### Input Validation
- Server-side validation for all user inputs
- Title length limits (100 characters)
- Email format validation
- Password strength requirements

### Database Security
- All queries use prepared statements
- No direct SQL concatenation
- Proper error handling without exposing sensitive information

### Session Security
- Secure session configuration
- Session regeneration on login
- Proper session cleanup on logout

## 🎯 Usage Guide

### Registration
1. Visit `register.php`
2. Fill out the registration form
3. Automatically logged in after successful registration

### Login
1. Visit `login.php`
2. Enter your credentials
3. Redirected to dashboard on success

### Task Management
1. **Create Task**: Use the form at the top of the dashboard
2. **Toggle Status**: Click "Mark Complete" or "Mark Pending"
3. **Delete Task**: Click "Delete" and confirm in the popup

### Dashboard Features
- **Statistics**: View counts of pending, completed, and total tasks
- **Task Cards**: Visual representation of each task
- **Real-time Updates**: Status changes update immediately
- **Responsive Design**: Works on all device sizes

## 🔧 Technical Details

### Frontend-Backend Communication
- **AJAX Requests**: Fetch API for modern JavaScript
- **JSON Responses**: All API endpoints return JSON
- **Error Handling**: Proper error messages and user feedback
- **Loading States**: Visual feedback during operations

### Database Relations
- Foreign key constraint between `tasks.user_id` and `users.id`
- Cascade deletion (when user is deleted, their tasks are too)
- Proper indexing for performance

### Code Organization
- **Separation of Concerns**: Logic separated into appropriate files
- **Reusable Functions**: Common operations in `database.php`
- **Consistent Error Handling**: Standardized error responses
- **Clean HTML/CSS**: Well-structured and maintainable frontend

## 🐛 Error Handling

### Frontend Errors
- Network errors are caught and displayed to users
- Invalid responses are handled gracefully
- User-friendly error messages

### Backend Errors
- Database errors are logged and generic messages shown
- Input validation errors are specific and helpful
- Authentication errors are handled securely

## 📊 Performance Considerations

### Database Optimization
- Proper indexing on frequently queried columns
- Efficient queries with appropriate LIMIT clauses
- Foreign key constraints for data integrity

### Frontend Performance
- Minimal JavaScript libraries (vanilla JS only)
- Efficient DOM manipulation
- CSS transitions for smooth interactions

## 🔍 Testing Checklist

### Authentication Testing
- [ ] Register with valid data
- [ ] Register with duplicate username/email
- [ ] Login with correct credentials
- [ ] Login with incorrect credentials
- [ ] Access protected pages without login

### Task Management Testing
- [ ] Create task with title only
- [ ] Create task with title and description
- [ ] Toggle task status (pending ↔ completed)
- [ ] Delete task with confirmation
- [ ] Try to access another user's tasks

### Security Testing
- [ ] CSRF token validation
- [ ] Input sanitization
- [ ] SQL injection prevention
- [ ] Session security

## 🚀 Potential Enhancements

### Features to Add
1. **Due Dates**: Add deadline functionality
2. **Categories**: Organize tasks by category
3. **Priority Levels**: High, medium, low priority
4. **Search/Filter**: Find tasks quickly
5. **Bulk Operations**: Select multiple tasks
6. **Task Sharing**: Share tasks with other users

### Technical Improvements
1. **API Endpoints**: RESTful API structure
2. **Pagination**: Handle large numbers of tasks
3. **Caching**: Improve performance with caching
4. **File Uploads**: Attach files to tasks
5. **Email Notifications**: Task reminders

## 📝 Code Quality

### Best Practices Implemented
- **DRY Principle**: Reusable functions and components
- **Security First**: All inputs validated and sanitized
- **Error Handling**: Comprehensive error management
- **Documentation**: Clear comments and structure
- **Responsive Design**: Mobile-first approach

### Code Standards
- **PSR-4 Autoloading**: Proper PHP namespacing (if expanded)
- **Consistent Naming**: Clear variable and function names
- **Separation of Concerns**: Logic, presentation, and data separated
- **Version Control Ready**: Structured for Git workflow

This task management application demonstrates mastery of PHP fundamentals, database relationships, security practices, and modern web development techniques. The combination of server-side PHP with client-side JavaScript creates a dynamic, secure, and user-friendly experience.