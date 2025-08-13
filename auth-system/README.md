# Vue.js 3 Authentication System with PHP

A complete, real-world authentication system built with Vue.js 3, Bootstrap 5, and PHP with MySQL.

## Features

✅ **User Registration** - Complete registration form with validation
✅ **User Login** - Secure login with session management  
✅ **Dashboard** - User dashboard with profile overview
✅ **Profile Management** - Update user profile information
✅ **Change Password** - Secure password change functionality
✅ **Logout** - Complete session cleanup
✅ **Responsive Design** - Beautiful Bootstrap 5 UI
✅ **Real-time Validation** - Client and server-side validation
✅ **Loading States** - User-friendly loading indicators
✅ **Error Handling** - Comprehensive error messages

## Technology Stack

- **Frontend**: Vue.js 3, Bootstrap 5, Font Awesome
- **Backend**: PHP 7.4+, PDO
- **Database**: MySQL
- **Security**: Password hashing, SQL injection prevention, Session management

## Installation

### 1. Database Setup

1. Create a MySQL database named `vue_auth_system`
2. Run the SQL script in `database.sql` to create tables and sample data

### 2. Configuration

Update database credentials in `config/database.php`:

```php
private $host = "localhost";
private $db_name = "vue_auth_system";
private $username = "root";
private $password = "";
```

### 3. File Structure

```
auth-system/
├── api/
│   ├── login.php
│   ├── register.php
│   ├── logout.php
│   ├── profile.php
│   └── change-password.php
├── classes/
│   └── User.php
├── config/
│   └── database.php
├── index.html
└── database.sql
```

### 4. Access the Application

Open `http://localhost/vue-js/auth-system/` in your browser.

## Default Login Credentials

- **Email**: john@example.com
- **Password**: password123

## API Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/login.php` | POST | User login |
| `/api/register.php` | POST | User registration |
| `/api/logout.php` | POST | User logout |
| `/api/profile.php` | GET/POST | Get/Update user profile |
| `/api/change-password.php` | POST | Change user password |

## Security Features

- Password hashing with `password_hash()`
- SQL injection prevention with prepared statements
- Input validation and sanitization
- Session-based authentication
- CORS headers for API endpoints

## Vue.js 3 Features Used

- Composition API concepts
- Reactive data binding
- Event handling
- Conditional rendering
- Form validation
- HTTP requests with Fetch API
- Component lifecycle

## Bootstrap 5 Features

- Responsive grid system
- Form controls and validation
- Navigation components
- Card layouts
- Alert messages
- Loading spinners
- Dropdown menus

## Usage

1. **Registration**: Create a new account with required information
2. **Login**: Sign in with email and password
3. **Dashboard**: View account overview and statistics
4. **Profile**: Update personal information
5. **Change Password**: Securely change account password
6. **Logout**: Sign out and clear session

## Customization

- Modify styles in the `<style>` section of `index.html`
- Update API endpoints in `api/` directory
- Extend User class in `classes/User.php`
- Add new database fields by updating the schema

## Browser Support

- Chrome 60+
- Firefox 60+
- Safari 12+
- Edge 79+

## License

This project is open-source and available under the MIT License.
