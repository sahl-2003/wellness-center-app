# GreenLife Wellness Center - Booking System

A comprehensive PHP-based web application for managing appointments and services at a holistic wellness center located in Colombo, Sri Lanka. This system facilitates seamless interaction between clients, therapists, and administrators.

## 📋 Table of Contents

- [Features](#features)
- [Technology Stack](#technology-stack)
- [Installation](#installation)
- [Database Setup](#database-setup)
- [Configuration](#configuration)
- [User Roles](#user-roles)
- [Project Structure](#project-structure)
- [Usage](#usage)

## ✨ Features

### For Clients
- **User Registration & Authentication** - Secure registration and login system
- **Service Browsing** - View available wellness services with details, pricing, and duration
- **Appointment Booking** - Book appointments with preferred therapists and services
- **Appointment Management** - View, cancel, and reschedule appointments
- **Profile Management** - Update personal information and profile picture
- **Messaging System** - Direct messaging with therapists
- **Therapist Discovery** - Browse and view therapist profiles, specializations, and availability
- **Dashboard** - Personalized dashboard with appointment history and upcoming sessions

### For Therapists
- **Dashboard** - Overview of appointments, clients, and schedule
- **Appointment Management** - View and manage client appointments
- **Schedule Management** - Set and update availability schedules
- **Client Management** - View client profiles and appointment history
- **Messaging** - Communicate with clients through the messaging system
- **Profile Management** - Update professional profile, qualifications, and bio

### For Administrators
- **Dashboard** - Comprehensive overview of system statistics
- **User Management** - Add, edit, and manage users (clients and therapists)
- **Service Management** - Create, update, and manage wellness services
- **Therapist Management** - Manage therapist profiles and information
- **Appointment Oversight** - View and manage all appointments
- **Contact Messages** - Handle and reply to contact form submissions
- **System Analytics** - Monitor bookings, users, and system performance

### General Features
- **Responsive Design** - Mobile-friendly interface
- **Contact Form** - Contact page for inquiries
- **Blog Section** - Wellness articles and insights
- **Service Categories** - Organized services by categories (Ayurvedic, Yoga & Meditation, Massage Therapy, etc.)
- **Secure Authentication** - Password hashing and session management
- **File Upload** - Profile pictures and service images

## 🛠 Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Libraries**: 
  - Font Awesome 6.0.0 (Icons)
  - Google Fonts (Typography)
- **Server**: Apache/Nginx with PHP support

## 📦 Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Apache/Nginx web server
- Composer (optional, for dependency management)

### Steps

1. **Clone or download the repository**
   ```bash
   git clone <https://github.com/sahl-2003/wellness-center-app.git>
   cd green2
   ```

2. **Place files in web server directory**
   - For Apache: Place in `htdocs` or `www` directory
   - For XAMPP: Place in `C:\xampp\htdocs\green2`
   - For WAMP: Place in `C:\wamp\www\green2`

3. **Configure PHP**
   - Ensure PHP extensions are enabled: `mysqli`, `pdo`, `session`
   - Verify `upload_max_filesize` and `post_max_size` in `php.ini`

4. **Set proper permissions**
   - Ensure `uploads/profiles/` and `uploads/services/` directories are writable
   - On Linux/Mac: `chmod 755 uploads/profiles uploads/services`

## 💾 Database Setup

1. **Create Database**
   ```sql
   CREATE DATABASE greenlife;
   ```

2. **Import Database Schema**
   - Open phpMyAdmin or MySQL command line
   - Select the `greenlife` database
   - Import the `greenlife.sql` file
   ```bash
   mysql -u root -p greenlife < greenlife.sql
   ```
   Or use phpMyAdmin's import feature

3. **Database Tables**
   - `users` - User accounts (clients, therapists, admins)
   - `client_profiles` - Client profile information
   - `therapists` - Therapist profiles and specializations
   - `services` - Wellness services catalog
   - `appointments` - Appointment bookings
   - `therapist_availability` - Therapist schedule/availability
   - `messages` - Internal messaging system
   - `message_replies` - Admin replies to messages
   - `contact_messages` - Contact form submissions

## ⚙️ Configuration

1. **Database Connection**
   Edit `dbconnect.php` with your database credentials:
   ```php
   $server = "localhost";
   $user = "root";
   $password = "";  // Your MySQL password
   $dbase = "greenlife";
   ```

2. **File Paths**
   - If your project is not in the root directory, update file paths in PHP includes
   - Update image paths if necessary

3. **Session Configuration**
   - Ensure PHP sessions are properly configured
   - Check `session.save_path` in `php.ini`

## 👥 User Roles

The system supports three user roles:

### 1. Client (Default Role)
- Default role for new registrations
- Access to booking, appointments, and messaging
- Dashboard: `client_dashboard.php`

### 2. Therapist
- Manages appointments and schedule
- Communicates with clients
- Dashboard: `therapist/dashboard.php`
- Assigned by administrator

### 3. Administrator
- Full system access
- Manages users, services, and appointments
- Dashboard: `admin/dashboard.php`
- Must be created directly in database or assigned manually

## 📁 Project Structure

```
green2/
├── admin/                  # Admin panel files
│   ├── dashboard.php
│   ├── users.php
│   ├── services.php
│   ├── therapists.php
│   ├── appointments.php
│   └── messages.php
├── therapist/              # Therapist panel files
│   ├── dashboard.php
│   ├── appointments.php
│   ├── schedule.php
│   └── clients.php
├── uploads/                # Uploaded files
│   ├── profiles/           # User profile pictures
│   └── services/           # Service images
├── image/                  # Static images and assets
├── *.php                   # Main application files
├── *.css                   # Stylesheet files
├── dbconnect.php           # Database connection
├── greenlife.sql           # Database schema
└── README.md               # This file
```

## 🚀 Usage

### Accessing the Application

1. **Homepage**: Navigate to `index.php` in your browser
   ```
   http://localhost/green2/index.php
   ```

2. **Registration**: New users can register at `register.php`
   - Clients register automatically with 'client' role
   - Therapists and admins must be created by administrators

3. **Login**: Access login page at `login.php`
   - After login, users are redirected based on their role:
     - Clients → Homepage
     - Therapists → Therapist Dashboard
     - Admins → Admin Dashboard

### Creating Admin User

To create an admin user, insert directly into the database:

```sql
INSERT INTO users (username, email, pwd, role) 
VALUES ('admin', 'admin@greenlife.lk', 
        '$2y$10$YourHashedPasswordHere', 'admin');
```

Or use password_hash in PHP to generate a hashed password.

### Default Services

The system includes default services:
- Yoga & Meditation
- Ayurvedic Therapy
- Shirodhara
- Hatha Yoga Group Session
- Personalized Diet Plan
- Sports Injury Rehabilitation
- Deep Tissue Massage

## 🔒 Security Notes

- Passwords are hashed using PHP's `password_hash()` function
- SQL injection protection using prepared statements
- XSS protection with `htmlspecialchars()` where needed
- Session management for authentication
- File upload validation recommended (currently basic)

## 📝 Development Notes

- The project uses procedural PHP with some object-oriented patterns
- Session-based authentication
- Responsive CSS with custom stylesheets per section
- Font Awesome icons and Google Fonts for typography
- Image uploads stored in `uploads/` directory

## 🤝 Contributing

This appears to be an academic/project assignment. For improvements:
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

## 📄 License

This project appears to be part of an academic assignment. Please check with the project owner regarding licensing.

## 📧 Contact

For inquiries about the GreenLife Wellness Center:
- **Address**: 123 Wellness Street, Colombo
- **Phone**: +94 11 234 5678
- **Email**: info@greenlifewellness.lk

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Verify database credentials in `dbconnect.php`
   - Ensure MySQL service is running
   - Check database name matches

2. **Upload Errors**
   - Check `uploads/` directory permissions
   - Verify PHP `upload_max_filesize` settings
   - Ensure directory exists

3. **Session Issues**
   - Verify PHP session support is enabled
   - Check `session.save_path` is writable
   - Clear browser cookies if needed

4. **404 Errors**
   - Verify file paths are correct
   - Check Apache/Nginx configuration
   - Ensure `.htaccess` rules (if any) are correct

---

**Note**: This is a working prototype for a wellness center booking system. Ensure proper security measures are implemented before deploying to production.

