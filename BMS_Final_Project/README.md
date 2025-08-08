# Blood Management System (BMS)

A comprehensive web-based Blood Management System built with PHP and MySQL, designed to manage blood donations, requests, and inventory across hospitals, blood banks, and donors.

## Features

### 🔐 Authentication Module
- **Role-based Login**: Admin, Donor, Hospital, Blood Bank
- **Light/Dark Theme**: Toggle between themes
- **Forgot Password**: Email-based password reset
- **Secure Session Management**

### 📊 Dashboard Module
- **Admin Dashboard**: System metrics, user management, analytics
- **Donor Dashboard**: Donation history, appointments, eligibility status
- **Hospital Dashboard**: Blood requests, inventory status, request tracking
- **Blood Bank Dashboard**: Inventory management, donor management, donation tracking

### 🩸 Request Management
- Create blood requests with urgency levels
- Track request status (Pending, Approved, Rejected, Completed)
- Patient information and medical details

### 📦 Inventory Management
- Blood inventory with status tracking (Low, Normal, Urgent)
- Filter by blood type, location, or status
- Real-time inventory updates

### 👥 Donor Communication Panel
- View donor list and profiles
- Send notifications and reminders
- Eligibility check based on donation history

### 🔍 Search & Reporting
- Search blood availability across regions
- Generate reports by region, date, blood type
- Export data to CSV format

## System Requirements

- **Web Server**: Apache/Nginx
- **PHP**: 7.4 or higher
- **MySQL**: 5.7 or higher
- **Browser**: Modern browsers with JavaScript enabled

## Installation

### 1. Database Setup
```sql
-- Import the database schema
mysql -u root -p < database/bms_database.sql
```

### 2. Configuration
1. Copy the project to your web server directory
2. Update database connection in `config/database.php`:
   ```php
   private $host = 'localhost';
   private $db_name = 'bms_db';
   private $username = 'your_username';
   private $password = 'your_password';
   ```

### 3. File Permissions
Ensure the following directories are writable:
```bash
chmod 755 BMS_Final_Project/
chmod 644 BMS_Final_Project/config/database.php
```

### 4. Access the System
Navigate to `http://localhost/BMS_Final_Project/` in your browser.

## Demo Accounts

The system comes with a pre-configured admin account:

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@bms.com | password |

Other users (Donors, Hospitals, Blood Banks) can create their own accounts through the registration system.

## Project Structure

```
BMS_Final_Project/
├── admin/                 # Admin dashboard and management
│   ├── dashboard.php
│   ├── users.php
│   └── analytics.php
├── donor/                 # Donor management
│   ├── dashboard.php
│   ├── history.php
│   └── appointments.php
├── hospital/              # Hospital management
│   ├── dashboard.php
│   ├── requests.php
│   └── inventory.php
├── bloodbank/             # Blood bank management
│   ├── dashboard.php
│   ├── inventory.php
│   └── donors.php
├── config/                # Configuration files
│   └── database.php
├── includes/              # Shared components
│   ├── auth.php
│   ├── header.php
│   └── footer.php
├── assets/                # Static assets
│   ├── css/
│   └── js/
├── database/              # Database schema
│   └── bms_database.sql
├── index.php              # Main entry point
├── login.php              # Login page
├── logout.php             # Logout handler
└── README.md
```

## Database Schema

### Core Tables
- **users**: User authentication and roles
- **donors**: Donor information and eligibility
- **hospitals**: Hospital details and locations
- **blood_banks**: Blood bank information
- **blood_inventory**: Blood stock management
- **blood_requests**: Blood request tracking
- **donations**: Donation records
- **appointments**: Donation appointments
- **notifications**: System notifications
- **reports**: Generated reports

## Key Features

### 🔒 Security
- Password hashing with bcrypt
- Session-based authentication
- Role-based access control
- SQL injection prevention with prepared statements

### 🎨 User Interface
- Responsive Bootstrap 5 design
- Light/Dark theme toggle
- Interactive charts with Chart.js
- Modern card-based layouts

### 📱 Responsive Design
- Mobile-friendly interface
- Cross-browser compatibility
- Touch-friendly controls

### 📊 Analytics
- Real-time dashboard statistics
- Blood type distribution charts
- Donation trend analysis
- Request status tracking

## Usage Guide

### For Administrators
1. **User Management**: Add, edit, and manage system users
2. **System Analytics**: View comprehensive system statistics
3. **Reports**: Generate and export system reports

### For Donors
1. **Profile Management**: Update personal information
2. **Donation History**: View past donations
3. **Appointments**: Schedule and manage donation appointments
4. **Eligibility Check**: Check donation eligibility status

### For Hospitals
1. **Blood Requests**: Create and track blood requests
2. **Inventory Check**: Check blood availability
3. **Request History**: View all past requests

### For Blood Banks
1. **Inventory Management**: Manage blood stock levels
2. **Donor Management**: Track donor information
3. **Appointment Scheduling**: Manage donation appointments

## API Endpoints

The system provides RESTful API endpoints for:
- User authentication
- Blood inventory queries
- Request management
- Donation tracking

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support and questions:
- Create an issue in the repository
- Contact the development team
- Check the documentation

## Changelog

### Version 1.0.0
- Initial release
- Complete authentication system
- Role-based dashboards
- Blood inventory management
- Request tracking system
- Donor management
- Reporting capabilities

## Future Enhancements

- Mobile application
- SMS notifications
- Advanced analytics
- Integration with external systems
- Multi-language support
- Advanced reporting features

---

**Note**: This is a demonstration system. For production use, ensure proper security measures, data backup, and compliance with healthcare regulations.
