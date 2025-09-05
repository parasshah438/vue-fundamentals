# Image Upload Module - Vue.js 3 with PHP MySQL

A comprehensive image upload module built with Vue.js 3, PHP backend, and MySQL database featuring both single and multiple image uploads with advanced validation, gallery management, and delete functionality.

## 🚀 **Features**

### 📤 **Single Image Upload**
- **Drag & Drop** support with visual feedback
- **Click to select** file functionality
- **Real-time validation** (client & server-side)
- **Progress tracking** with animated progress bar
- **Thumbnail generation** automatically
- **File size & dimension validation**

### 📤 **Multiple Image Upload**
- **Batch upload** up to 10 images simultaneously
- **Drag & Drop** multiple files support
- **Individual file validation** before upload
- **Total size limit** enforcement (50MB)
- **Batch tracking** with unique batch IDs
- **Progress tracking** for entire batch

### 🖼️ **Image Gallery**
- **Grid layout** with responsive design
- **Thumbnail previews** for fast loading
- **Sorting & filtering** options
- **Pagination** for large collections
- **Image details** modal view
- **Bulk delete** functionality

### ✅ **Advanced Validation**
- **File type validation**: JPEG, PNG, GIF, WebP
- **File size limits**: 5MB per image, 50MB per batch
- **Image dimensions**: Min 50x50, Max 4000x4000 pixels
- **MIME type verification** server-side
- **File extension validation**
- **Real image validation** (not just extension)

### 🗄️ **Database Features**
- **Complete tracking** of all uploads
- **Batch management** for multiple uploads
- **Upload logs** for activity tracking
- **Foreign key relationships** for data integrity
- **Automatic cleanup** on deletion

## 📁 **Project Structure**

```
image-module/
├── index.html                  # Main Vue.js 3 application
├── config/
│   └── database.php           # Database config & validation helpers
├── api/
│   ├── upload-single.php      # Single image upload endpoint
│   ├── upload-multiple.php    # Multiple images upload endpoint
│   ├── images.php             # Get images with filtering/pagination
│   └── delete.php             # Delete images (single/multiple/batch)
├── database/
│   └── create_database.sql    # Complete database schema
├── js/
│   └── app.js                 # Vue.js 3 application logic
├── uploads/                   # Image storage directory
│   ├── .htaccess             # Security & optimization rules
│   └── thumbnails/           # Auto-generated thumbnails
│       └── .htaccess         # Thumbnail directory rules
└── README.md                  # This documentation
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
private $db_name = 'image_module';
private $username = 'root';
private $password = '';  // Empty for XAMPP
```

### 3. **Set Directory Permissions**
```bash
# Make upload directories writable (Linux/Mac)
chmod 755 uploads/
chmod 755 uploads/thumbnails/

# Windows (XAMPP) - No additional setup needed
```

### 4. **Run Application**
```
http://localhost/vue-js/image-module/
```

## 🎯 **API Endpoints**

### POST `/api/upload-single.php`
Upload single image
```javascript
// FormData with 'image' field
const formData = new FormData();
formData.append('image', file);

// Response
{
    "success": true,
    "message": "Image uploaded successfully",
    "data": {
        "id": 1,
        "original_name": "photo.jpg",
        "file_name": "photo_unique123.jpg",
        "file_size": 1024000,
        "dimensions": "1920x1080",
        "thumbnail_path": "uploads/thumbnails/thumb_photo_unique123.jpg"
    }
}
```

### POST `/api/upload-multiple.php`
Upload multiple images
```javascript
// FormData with 'images[]' field
const formData = new FormData();
files.forEach(file => formData.append('images[]', file));

// Response
{
    "success": true,
    "message": "5 images uploaded successfully",
    "data": {
        "batch_id": "batch_unique123",
        "total_images": 5,
        "total_size": 5120000,
        "images": [...]
    }
}
```

### GET `/api/images.php`
Get images with filtering and pagination
```javascript
// Query parameters
?type=all&sort_by=uploaded_at&sort_order=DESC&limit=24&offset=0

// Response
{
    "success": true,
    "data": {
        "images": [...],
        "pagination": {
            "total_count": 100,
            "current_page": 1,
            "total_pages": 5
        }
    }
}
```

### DELETE `/api/delete.php`
Delete images (single, multiple, or batch)
```javascript
// Single image
{ "image_id": 123 }

// Multiple images
{ "image_ids": [123, 124, 125] }

// Entire batch
{ "batch_id": "batch_unique123" }
```

## 🗃️ **Database Schema**

### Images Table
```sql
CREATE TABLE images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    original_name VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    width INT DEFAULT NULL,
    height INT DEFAULT NULL,
    upload_type ENUM('single', 'multiple') NOT NULL,
    batch_id VARCHAR(50) DEFAULT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Upload Batches Table
```sql
CREATE TABLE upload_batches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    batch_id VARCHAR(50) UNIQUE NOT NULL,
    total_images INT NOT NULL DEFAULT 0,
    total_size BIGINT NOT NULL DEFAULT 0,
    upload_type ENUM('single', 'multiple') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Upload Logs Table
```sql
CREATE TABLE upload_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    action ENUM('upload', 'delete') NOT NULL,
    image_id INT DEFAULT NULL,
    batch_id VARCHAR(50) DEFAULT NULL,
    file_name VARCHAR(255),
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## ✅ **Validation Rules**

### Client-Side Validation (Vue.js)
- **File Types**: image/jpeg, image/png, image/gif, image/webp
- **File Size**: Maximum 5MB per image
- **Total Size**: Maximum 50MB per batch upload
- **File Count**: Maximum 10 files per batch
- **Image Dimensions**: 50x50 to 4000x4000 pixels

### Server-Side Validation (PHP)
- **MIME Type Verification**: Using `finfo_file()`
- **File Extension Check**: Against whitelist
- **Image Validation**: Using `getimagesize()`
- **File Size Limits**: Enforced on server
- **Upload Error Handling**: Complete error checking
- **Security**: No PHP execution in upload directories

## 🎮 **Usage Examples**

### Single Image Upload
1. **Select Tab**: Click "Single Upload"
2. **Choose Image**: Click upload area or drag & drop
3. **Validation**: Automatic validation with error messages
4. **Upload**: Click "Upload Image" button
5. **Progress**: Watch real-time progress bar
6. **Success**: Image appears in gallery

### Multiple Image Upload
1. **Select Tab**: Click "Multiple Upload"
2. **Choose Images**: Select multiple files or drag & drop
3. **Review**: See selected files list with sizes
4. **Remove**: Remove unwanted files before upload
5. **Upload**: Click "Upload Images" for batch upload
6. **Tracking**: All images get same batch ID

### Gallery Management
1. **View Gallery**: Click "Gallery" tab
2. **Filter**: Use type, sort, and pagination controls
3. **View Image**: Click image for full-size modal
4. **Delete**: Individual or bulk delete options
5. **Refresh**: Reload gallery after uploads

## 🔧 **Vue.js 3 Features Used**

- **Composition API** with `setup()` function
- **Reactive data** with `ref()` and `reactive()`
- **Computed properties** for dynamic values
- **Event handling** with drag & drop support
- **Async/await** for API calls
- **File validation** with Promise-based image loading
- **Progress tracking** with intervals
- **Modal management** with Bootstrap integration

## 🛡️ **Security Features**

- **File type validation** (client & server)
- **MIME type verification** server-side
- **File size limits** enforced
- **Upload directory protection** (.htaccess)
- **No PHP execution** in upload folders
- **SQL injection prevention** (PDO prepared statements)
- **Input sanitization** and validation
- **Error handling** without sensitive data exposure

## 📱 **Responsive Design**

- **Mobile-first** approach with Bootstrap 5
- **Touch-friendly** drag & drop areas
- **Responsive grid** for image gallery
- **Optimized typography** for mobile
- **Collapsible navigation** on small screens
- **Touch gestures** support

## 🎨 **Customization**

### Upload Limits
Edit `config/database.php`:
```php
class ImageConfig {
    public static $maxFileSize = 10 * 1024 * 1024; // 10MB
    public static $maxFilesPerUpload = 20; // 20 files
    public static $maxWidth = 8000; // 8000px
    public static $maxHeight = 8000; // 8000px
}
```

### Allowed File Types
```php
public static $allowedTypes = [
    'image/jpeg', 'image/png', 'image/gif', 
    'image/webp', 'image/bmp', 'image/tiff'
];
```

### Styling
Modify CSS in `index.html` to customize:
- Colors and gradients
- Card layouts and spacing
- Animation effects
- Grid layouts

## 🔍 **Troubleshooting**

### Upload Fails
- Check file permissions on `uploads/` directory
- Verify PHP `upload_max_filesize` and `post_max_size`
- Check database connection
- Review PHP error logs

### Images Not Displaying
- Verify `.htaccess` files in upload directories
- Check file paths in database
- Ensure web server can serve static files
- Verify image files exist on disk

### Validation Errors
- Check client-side validation rules
- Verify server-side validation configuration
- Test with different image formats
- Check image dimensions and file sizes

## 📋 **Requirements**

- **PHP 7.4+** with GD extension
- **MySQL 5.7+** or MariaDB
- **Web server** (Apache/Nginx)
- **Modern browser** with ES6+ support
- **File upload permissions** on server

## ✨ **Key Highlights**

✅ **Dual Upload Modes** - Single & multiple image uploads  
✅ **Advanced Validation** - Client & server-side validation  
✅ **Drag & Drop** - Modern file selection interface  
✅ **Progress Tracking** - Real-time upload progress  
✅ **Thumbnail Generation** - Automatic thumbnail creation  
✅ **Gallery Management** - Complete image management system  
✅ **Batch Operations** - Upload and delete in batches  
✅ **Database Tracking** - Complete upload history  
✅ **Security** - File type validation & directory protection  
✅ **Responsive Design** - Mobile-friendly interface  

Perfect for learning Vue.js 3 with file uploads, PHP backend integration, and comprehensive image management!

## 📖 **API Documentation**

All endpoints return JSON responses with consistent structure:
```json
{
    "success": true|false,
    "message": "Description of result",
    "data": { ... } // Only on success
}
```

Error responses include appropriate HTTP status codes and descriptive messages for debugging.

---

**Built with ❤️ using Vue.js 3, PHP 8, MySQL, and Bootstrap 5**