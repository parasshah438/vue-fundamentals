<?php
/**
 * Database Configuration for Vue Advanced CRUD
 * 
 * This file contains database connection settings and utility functions
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'vue_advanced_crud';
    private $username = 'root';
    private $password = '';
    private $conn;

    /**
     * Get database connection
     */
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}

/**
 * User model class for database operations
 */
class User {
    private $conn;
    private $table_name = "users";

    // User properties
    public $id;
    public $first_name;
    public $last_name;
    public $email;
    public $phone;
    public $date_of_birth;
    public $gender;
    public $age;
    public $marital_status;
    public $address;
    public $city;
    public $state;
    public $zip_code;
    public $country;
    public $job_title;
    public $company;
    public $department;
    public $salary;
    public $hire_date;
    public $employment_type;
    public $status;
    public $date_joined;
    public $avatar;
    public $website;
    public $linkedin_profile;
    public $favorite_color;
    public $experience_level;
    public $preferred_work_time;
    public $last_login;
    public $skills;
    public $work_mode;
    public $email_notifications;
    public $sms_notifications;
    public $notes;
    public $resume;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Read all users with pagination and search
     */
    public function read($page = 1, $limit = 10, $search = '') {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT * FROM " . $this->table_name;
        
        if (!empty($search)) {
            $query .= " WHERE first_name LIKE :search 
                       OR last_name LIKE :search 
                       OR email LIKE :search 
                       OR phone LIKE :search 
                       OR job_title LIKE :search 
                       OR company LIKE :search 
                       OR country LIKE :search 
                       OR status LIKE :search";
        }
        
        $query .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);

        if (!empty($search)) {
            $searchTerm = "%" . htmlspecialchars(strip_tags($search)) . "%";
            $stmt->bindParam(":search", $searchTerm);
        }

        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Count total users for pagination
     */
    public function count($search = '') {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        
        if (!empty($search)) {
            $query .= " WHERE first_name LIKE :search 
                       OR last_name LIKE :search 
                       OR email LIKE :search 
                       OR phone LIKE :search 
                       OR job_title LIKE :search 
                       OR company LIKE :search 
                       OR country LIKE :search 
                       OR status LIKE :search";
        }

        $stmt = $this->conn->prepare($query);

        if (!empty($search)) {
            $searchTerm = "%" . htmlspecialchars(strip_tags($search)) . "%";
            $stmt->bindParam(":search", $searchTerm);
        }

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'];
    }

    /**
     * Read single user
     */
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->email = $row['email'];
            $this->phone = $row['phone'];
            $this->date_of_birth = $row['date_of_birth'];
            $this->gender = $row['gender'];
            $this->age = $row['age'];
            $this->marital_status = $row['marital_status'];
            $this->address = $row['address'];
            $this->city = $row['city'];
            $this->state = $row['state'];
            $this->zip_code = $row['zip_code'];
            $this->country = $row['country'];
            $this->job_title = $row['job_title'];
            $this->company = $row['company'];
            $this->department = $row['department'];
            $this->salary = $row['salary'];
            $this->hire_date = $row['hire_date'];
            $this->employment_type = $row['employment_type'];
            $this->status = $row['status'];
            $this->date_joined = $row['date_joined'];
            $this->avatar = $row['avatar'];
            $this->website = $row['website'];
            $this->linkedin_profile = $row['linkedin_profile'];
            $this->favorite_color = $row['favorite_color'];
            $this->experience_level = $row['experience_level'];
            $this->preferred_work_time = $row['preferred_work_time'];
            $this->last_login = $row['last_login'];
            $this->skills = $row['skills'];
            $this->work_mode = $row['work_mode'];
            $this->email_notifications = $row['email_notifications'];
            $this->sms_notifications = $row['sms_notifications'];
            $this->notes = $row['notes'];
            return true;
        }

        return false;
    }

    /**
     * Create new user
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET first_name=:first_name, last_name=:last_name, email=:email, phone=:phone,
                    date_of_birth=:date_of_birth, gender=:gender, age=:age, marital_status=:marital_status,
                    address=:address, city=:city, state=:state, zip_code=:zip_code, country=:country,
                    job_title=:job_title, company=:company, department=:department, salary=:salary,
                    hire_date=:hire_date, employment_type=:employment_type, status=:status,
                    date_joined=:date_joined, avatar=:avatar, resume=:resume, website=:website, linkedin_profile=:linkedin_profile,
                    favorite_color=:favorite_color, experience_level=:experience_level, preferred_work_time=:preferred_work_time,
                    last_login=:last_login, skills=:skills, work_mode=:work_mode,
                    email_notifications=:email_notifications, sms_notifications=:sms_notifications, notes=:notes";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->job_title = htmlspecialchars(strip_tags($this->job_title));
        $this->company = htmlspecialchars(strip_tags($this->company));
        
        // Bind parameters
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":date_of_birth", $this->date_of_birth);
        $stmt->bindParam(":gender", $this->gender);
        $stmt->bindParam(":age", $this->age);
        $stmt->bindParam(":marital_status", $this->marital_status);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":city", $this->city);
        $stmt->bindParam(":state", $this->state);
        $stmt->bindParam(":zip_code", $this->zip_code);
        $stmt->bindParam(":country", $this->country);
        $stmt->bindParam(":job_title", $this->job_title);
        $stmt->bindParam(":company", $this->company);
        $stmt->bindParam(":department", $this->department);
        $stmt->bindParam(":salary", $this->salary);
        $stmt->bindParam(":hire_date", $this->hire_date);
        $stmt->bindParam(":employment_type", $this->employment_type);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":date_joined", $this->date_joined);
        $stmt->bindParam(":avatar", $this->avatar);
        $stmt->bindParam(":resume", $this->resume);
        $stmt->bindParam(":website", $this->website);
        $stmt->bindParam(":linkedin_profile", $this->linkedin_profile);
        $stmt->bindParam(":favorite_color", $this->favorite_color);
        $stmt->bindParam(":experience_level", $this->experience_level);
        $stmt->bindParam(":preferred_work_time", $this->preferred_work_time);
        $stmt->bindParam(":last_login", $this->last_login);
        $stmt->bindParam(":skills", $this->skills);
        $stmt->bindParam(":work_mode", $this->work_mode);
        $stmt->bindParam(":email_notifications", $this->email_notifications);
        $stmt->bindParam(":sms_notifications", $this->sms_notifications);
        $stmt->bindParam(":notes", $this->notes);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        return false;
    }

    /**
     * Update user
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET first_name=:first_name, last_name=:last_name, email=:email, phone=:phone,
                    date_of_birth=:date_of_birth, gender=:gender, age=:age, marital_status=:marital_status,
                    address=:address, city=:city, state=:state, zip_code=:zip_code, country=:country,
                    job_title=:job_title, company=:company, department=:department, salary=:salary,
                    hire_date=:hire_date, employment_type=:employment_type, status=:status,
                    avatar=:avatar, resume=:resume, website=:website, linkedin_profile=:linkedin_profile,
                    favorite_color=:favorite_color, experience_level=:experience_level, preferred_work_time=:preferred_work_time,
                    last_login=:last_login, skills=:skills, work_mode=:work_mode,
                    email_notifications=:email_notifications, sms_notifications=:sms_notifications, notes=:notes
                WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->job_title = htmlspecialchars(strip_tags($this->job_title));
        $this->company = htmlspecialchars(strip_tags($this->company));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind parameters
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":date_of_birth", $this->date_of_birth);
        $stmt->bindParam(":gender", $this->gender);
        $stmt->bindParam(":age", $this->age);
        $stmt->bindParam(":marital_status", $this->marital_status);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":city", $this->city);
        $stmt->bindParam(":state", $this->state);
        $stmt->bindParam(":zip_code", $this->zip_code);
        $stmt->bindParam(":country", $this->country);
        $stmt->bindParam(":job_title", $this->job_title);
        $stmt->bindParam(":company", $this->company);
        $stmt->bindParam(":department", $this->department);
        $stmt->bindParam(":salary", $this->salary);
        $stmt->bindParam(":hire_date", $this->hire_date);
        $stmt->bindParam(":employment_type", $this->employment_type);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":avatar", $this->avatar);
        $stmt->bindParam(":resume", $this->resume);
        $stmt->bindParam(":website", $this->website);
        $stmt->bindParam(":linkedin_profile", $this->linkedin_profile);
        $stmt->bindParam(":favorite_color", $this->favorite_color);
        $stmt->bindParam(":experience_level", $this->experience_level);
        $stmt->bindParam(":preferred_work_time", $this->preferred_work_time);
        $stmt->bindParam(":last_login", $this->last_login);
        $stmt->bindParam(":skills", $this->skills);
        $stmt->bindParam(":work_mode", $this->work_mode);
        $stmt->bindParam(":email_notifications", $this->email_notifications);
        $stmt->bindParam(":sms_notifications", $this->sms_notifications);
        $stmt->bindParam(":notes", $this->notes);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    /**
     * Delete user
     */
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    /**
     * Get user statistics
     */
    public function getStats() {
        $query = "SELECT * FROM user_stats";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Check if email exists (for validation)
     */
    public function emailExists() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $this->email = htmlspecialchars(strip_tags($this->email));
        $stmt->bindParam(1, $this->email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return true;
        }

        return false;
    }
}

/**
 * Utility functions
 */
function sendJsonResponse($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function handleFileUpload($file, $upload_dir = 'uploads/') {
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_types)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }
    
    if ($file['size'] > 5000000) { // 5MB limit
        return ['success' => false, 'message' => 'File too large'];
    }
    
    $new_filename = uniqid() . '.' . $file_extension;
    $upload_path = $upload_dir . $new_filename;
    
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return ['success' => true, 'filename' => $new_filename, 'path' => $upload_path];
    }
    
    return ['success' => false, 'message' => 'Upload failed'];
}
?>
