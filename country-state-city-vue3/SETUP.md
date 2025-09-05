# Setup Guide - Country State City Vue.js 3 with PHP MySQL

## Prerequisites

- **XAMPP/WAMP/LAMP** server running
- **PHP 7.4+** (PHP 8 recommended)
- **MySQL 5.7+** or **MariaDB**
- **Web browser** (Chrome, Firefox, Safari, Edge)

## Step-by-Step Setup

### 1. Database Setup

#### Option A: Using phpMyAdmin (Recommended)
1. Open **phpMyAdmin** in your browser: `http://localhost/phpmyadmin`
2. Click **"Import"** tab
3. Choose file: `database/create_database.sql`
4. Click **"Go"** to execute

#### Option B: Using MySQL Command Line
```bash
mysql -u root -p < database/create_database.sql
```

#### Option C: Manual Setup
1. Create database: `country_state_city`
2. Copy and paste the SQL from `database/create_database.sql`
3. Execute the queries

### 2. Database Configuration

Edit `config/database.php` if needed:

```php
private $host = 'localhost';        // Your MySQL host
private $db_name = 'country_state_city';  // Database name
private $username = 'root';         // Your MySQL username
private $password = '';             // Your MySQL password (empty for XAMPP)
```

### 3. File Permissions

Ensure your web server can read the files:
- **Windows (XAMPP)**: No additional setup needed
- **Linux/Mac**: Set proper permissions
```bash
chmod -R 755 /path/to/project
```

### 4. Test the Setup

1. **Start your web server** (XAMPP/WAMP)
2. **Open browser** and navigate to:
   ```
   http://localhost/vue-js/country-state-city-vue3/
   ```
3. **Test the flow**:
   - Select **India** → **Gujarat** → **Vadodara (Baroda)**
   - Submit the form
   - Check if data is saved in database

### 5. Verify Database Connection

Test individual API endpoints:

- **Countries**: `http://localhost/vue-js/country-state-city-vue3/api/countries.php`
- **States**: `http://localhost/vue-js/country-state-city-vue3/api/states.php?country_id=1`
- **Cities**: `http://localhost/vue-js/country-state-city-vue3/api/cities.php?state_id=1`

## Troubleshooting

### Common Issues

#### 1. Database Connection Error
**Error**: "Connection error: SQLSTATE[HY000] [1045]"
**Solution**: 
- Check MySQL credentials in `config/database.php`
- Ensure MySQL service is running
- Verify database exists

#### 2. CORS Error
**Error**: "Access to fetch at '...' from origin '...' has been blocked"
**Solution**: 
- Ensure you're accessing via `http://localhost` (not `file://`)
- Check CORS headers in PHP files

#### 3. 404 Not Found on API calls
**Error**: "404 Not Found" for API endpoints
**Solution**:
- Verify file paths are correct
- Check web server is running
- Ensure `.htaccess` allows PHP execution

#### 4. Empty Dropdowns
**Error**: Dropdowns show "Choose..." but no options
**Solution**:
- Check browser console for JavaScript errors
- Verify API endpoints return data
- Check database has data

### Database Verification

Check if data was inserted correctly:

```sql
-- Check countries
SELECT * FROM countries;

-- Check states for India (country_id = 1)
SELECT * FROM states WHERE country_id = 1;

-- Check cities for Gujarat (state_id = 1)
SELECT * FROM cities WHERE state_id = 1;

-- Check form submissions
SELECT * FROM form_submissions ORDER BY submitted_at DESC;
```

### API Testing

Test APIs using browser or tools like Postman:

```bash
# Get countries
GET http://localhost/vue-js/country-state-city-vue3/api/countries.php

# Get states for India
GET http://localhost/vue-js/country-state-city-vue3/api/states.php?country_id=1

# Get cities for Gujarat
GET http://localhost/vue-js/country-state-city-vue3/api/cities.php?state_id=1

# Submit form (POST)
POST http://localhost/vue-js/country-state-city-vue3/api/submit.php
Content-Type: application/json

{
    "countryId": 1,
    "stateId": 1,
    "cityId": 3
}
```

## Project Structure

```
country-state-city-vue3/
├── index.html              # Main Vue.js 3 application
├── config/
│   └── database.php        # Database configuration
├── api/
│   ├── countries.php       # Get countries API
│   ├── states.php          # Get states API
│   ├── cities.php          # Get cities API
│   └── submit.php          # Submit form API
├── database/
│   └── create_database.sql # Database creation script
├── js/
│   └── app.js              # Vue.js 3 application logic
├── SETUP.md                # This setup guide
└── README.md               # Project documentation
```

## Success Indicators

✅ **Database Created**: Tables `countries`, `states`, `cities`, `form_submissions` exist  
✅ **Data Inserted**: 5 countries, 25+ states, 90+ cities loaded  
✅ **API Working**: All endpoints return JSON responses  
✅ **Vue.js Loading**: Dropdowns populate with data from database  
✅ **Form Submission**: Data saves to `form_submissions` table  
✅ **Validation Working**: Error messages show for empty fields  

## Next Steps

After successful setup:

1. **Customize Data**: Add more countries/states/cities in database
2. **Styling**: Modify CSS in `index.html`
3. **Validation**: Add more validation rules in `js/app.js`
4. **Features**: Add search, filtering, or other enhancements
5. **Security**: Add authentication, input sanitization for production

## Support

If you encounter issues:

1. Check browser console for JavaScript errors
2. Check PHP error logs
3. Verify database connection and data
4. Test API endpoints individually
5. Ensure all files are in correct locations

The application should work perfectly with the default XAMPP/WAMP setup!