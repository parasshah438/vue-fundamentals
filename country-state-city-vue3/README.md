# Country State City Dropdown - Vue.js 3 with PHP MySQL

A complete Vue.js 3 application with dependent country, state, and city dropdowns powered by PHP backend and MySQL database with real-time validation.

## 🚀 **Features**

### 🌍 **Dependent Dropdowns**
- **Country → State → City** cascading selection
- **Real-time data loading** from MySQL database via PHP APIs
- **Automatic clearing** of dependent fields when parent changes

### 🗄️ **MySQL Database Backend**
- **Complete SQL schema** with foreign key relationships
- **5 Countries**: India, USA, UK, Canada, Australia
- **25+ States/Provinces** across all countries
- **90+ Cities** including Indian cities like Vadodara (Baroda), Ahmedabad, Surat
- **Form submissions tracking** with database storage

### ✅ **Pure Validation System**
- **Required field validation** for all dropdowns
- **Real-time error messages** with icons
- **Visual error indicators** (red borders)
- **Form submission prevention** until valid
- **Dynamic button states** (disabled when invalid)

### 🎨 **Modern Tech Stack**
- **Vue.js 3** with Composition API
- **PHP 8** with PDO for database operations
- **MySQL** with proper relationships
- **Bootstrap 5** responsive design
- **RESTful APIs** with JSON responses

## 📁 **Project Structure**

```
country-state-city-vue3/
├── index.html              # Main Vue.js 3 application
├── config/
│   └── database.php        # Database configuration & helpers
├── api/
│   ├── countries.php       # GET /api/countries.php
│   ├── states.php          # GET /api/states.php?country_id=1
│   ├── cities.php          # GET /api/cities.php?state_id=1
│   └── submit.php          # POST /api/submit.php
├── database/
│   └── create_database.sql # Complete database setup
├── js/
│   └── app.js              # Vue.js 3 application logic
├── SETUP.md                # Detailed setup instructions
└── README.md               # This file
```

## 🛠️ **Quick Setup**

### 1. **Database Setup**
```sql
-- Import the SQL file in phpMyAdmin or MySQL
mysql -u root -p < database/create_database.sql
```

### 2. **Configure Database** (if needed)
Edit `config/database.php`:
```php
private $host = 'localhost';
private $db_name = 'country_state_city';
private $username = 'root';
private $password = '';  // Empty for XAMPP
```

### 3. **Run Application**
```
http://localhost/vue-js/country-state-city-vue3/
```

## 🎯 **API Endpoints**

### GET `/api/countries.php`
Returns all countries
```json
{
    "success": true,
    "data": [
        {"id": 1, "name": "India", "code": "IN"},
        {"id": 2, "name": "United States", "code": "US"}
    ]
}
```

### GET `/api/states.php?country_id=1`
Returns states for a country
```json
{
    "success": true,
    "data": [
        {"id": 1, "name": "Gujarat", "countryId": 1},
        {"id": 2, "name": "Maharashtra", "countryId": 1}
    ]
}
```

### GET `/api/cities.php?state_id=1`
Returns cities for a state
```json
{
    "success": true,
    "data": [
        {"id": 1, "name": "Ahmedabad", "stateId": 1},
        {"id": 3, "name": "Vadodara (Baroda)", "stateId": 1}
    ]
}
```

### POST `/api/submit.php`
Submit form data
```json
// Request
{
    "countryId": 1,
    "stateId": 1,
    "cityId": 3
}

// Response
{
    "success": true,
    "data": {
        "country": "India",
        "state": "Gujarat", 
        "city": "Vadodara (Baroda)",
        "timestamp": "2024-01-15 10:30:45",
        "submission_id": 1
    }
}
```

## 🗃️ **Database Schema**

### Countries Table
```sql
CREATE TABLE countries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(10) NOT NULL
);
```

### States Table
```sql
CREATE TABLE states (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    country_id INT NOT NULL,
    FOREIGN KEY (country_id) REFERENCES countries(id)
);
```

### Cities Table
```sql
CREATE TABLE cities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    state_id INT NOT NULL,
    FOREIGN KEY (state_id) REFERENCES states(id)
);
```

### Form Submissions Table
```sql
CREATE TABLE form_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    country_id INT NOT NULL,
    state_id INT NOT NULL,
    city_id INT NOT NULL,
    country_name VARCHAR(100),
    state_name VARCHAR(100),
    city_name VARCHAR(100),
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## 🎮 **Demo Flow**

1. **Load Page** → Countries load from MySQL via PHP API
2. **Select "India"** → States load (Gujarat, Maharashtra, Karnataka, etc.)
3. **Select "Gujarat"** → Cities load (Ahmedabad, Surat, Vadodara (Baroda), etc.)
4. **Select "Vadodara (Baroda)"** → Form becomes valid
5. **Submit** → Data saves to MySQL database
6. **Success Message** → Shows saved data with database ID
7. **Auto Reset** → Form clears after 5 seconds

## 🔧 **Vue.js 3 Features Used**

- **Composition API** with `setup()` function
- **Reactive references** with `ref()` and `reactive()`
- **Computed properties** for form validation
- **Watchers** for real-time validation
- **Lifecycle hooks** with `onMounted()`
- **Async/await** for API calls
- **Error handling** with try/catch

## 🛡️ **Security Features**

- **PDO prepared statements** prevent SQL injection
- **Input validation** on both frontend and backend
- **CORS headers** properly configured
- **Error handling** without exposing sensitive data
- **Foreign key constraints** maintain data integrity

## 📱 **Responsive Design**

- **Mobile-first** approach with Bootstrap 5
- **Touch-friendly** dropdowns and buttons
- **Responsive typography** and spacing
- **Loading states** with spinners
- **Smooth animations** and transitions

## 🎨 **Customization**

### Add New Countries/States/Cities
```sql
-- Add new country
INSERT INTO countries (name, code) VALUES ('Germany', 'DE');

-- Add new state
INSERT INTO states (name, country_id) VALUES ('Bavaria', 6);

-- Add new city  
INSERT INTO cities (name, state_id) VALUES ('Munich', 26);
```

### Modify Styling
Edit CSS in `index.html` to customize colors, layouts, animations.

### Add Validation Rules
Update `js/app.js` to add custom validation logic.

## 🔍 **Troubleshooting**

### Database Connection Issues
- Check MySQL service is running
- Verify credentials in `config/database.php`
- Ensure database `country_state_city` exists

### API Not Working
- Check browser console for errors
- Test API endpoints directly in browser
- Verify file permissions and paths

### Empty Dropdowns
- Check database has data: `SELECT * FROM countries;`
- Verify API returns JSON: `/api/countries.php`
- Check browser network tab for failed requests

## 📋 **Requirements**

- **PHP 7.4+** (PHP 8 recommended)
- **MySQL 5.7+** or MariaDB
- **Web server** (Apache/Nginx)
- **Modern browser** with ES6+ support

## ✨ **Key Highlights**

✅ **Real Database Integration** - MySQL with proper relationships  
✅ **RESTful PHP APIs** - Clean, secure endpoints  
✅ **Vue.js 3 Composition API** - Modern reactive framework  
✅ **Dependent Dropdowns** - Cascading selection logic  
✅ **Form Validation** - Real-time error handling  
✅ **Responsive Design** - Bootstrap 5 mobile-friendly UI  
✅ **Data Persistence** - Form submissions saved to database  
✅ **Error Handling** - Graceful error management  
✅ **Loading States** - Visual feedback during API calls  
✅ **Security** - SQL injection prevention, input validation  

Perfect for learning Vue.js 3 with PHP backend integration and dependent dropdown implementation!

## 📖 **Documentation**

- **[SETUP.md](SETUP.md)** - Detailed setup instructions
- **[Database Schema](database/create_database.sql)** - Complete SQL setup
- **[API Documentation](#-api-endpoints)** - Endpoint specifications

## 🤝 **Contributing**

Feel free to submit issues, fork the repository, and create pull requests for any improvements.

---

**Built with ❤️ using Vue.js 3, PHP 8, MySQL, and Bootstrap 5**