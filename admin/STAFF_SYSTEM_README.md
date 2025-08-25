# Staff Management System - HotelAdmin

## Overview
The Staff Management System has been completely redesigned and made functional with the following features:

1. **Add Role Module** - Create new roles for staff members
2. **Add Staff Module** - Add new staff members with assigned roles
3. **Staff Listing** - View all staff members with filtering and search
4. **Database Integration** - Full CRUD operations with the database

## Features

### 1. Add Role Functionality
- **Button**: "Add Role" button in the top right
- **Fields**: 
  - Role Name (required)
  - Description (optional)
- **Validation**: Prevents duplicate role names
- **Database**: Inserts into `Roles` table

### 2. Add Staff Functionality
- **Button**: "Add Staff" button in the top right
- **Fields**:
  - First Name (required)
  - Last Name (required)
  - Email (required, unique)
  - Phone Number (required, unique)
  - Gender (required)
  - Role (required, fetched from database)
  - Status (Active/Inactive)
- **Validation**: 
  - All fields required
  - Email format validation
  - Duplicate email/phone prevention
  - Role existence verification
- **Database**: Inserts into `Staffs` table

### 3. Staff Listing
- **Grid Layout**: Responsive card-based display
- **Staff Information**: Name, role, ID, status, contact details
- **Status Indicators**: Active (green), Inactive (red), On Break (yellow)
- **Actions**: View Profile, Edit buttons

### 4. Search and Filtering
- **Search**: Search staff by name
- **Role Filter**: Filter by specific roles
- **Status Filter**: Filter by Active/Inactive status

## Database Tables

### Roles Table
```sql
CREATE TABLE Roles (
    role_id INT PRIMARY KEY AUTO_INCREMENT,
    role_name VARCHAR(50) UNIQUE NOT NULL,
    description TEXT
);
```

### Staffs Table
```sql
CREATE TABLE Staffs (
    staff_id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    role_id INT NOT NULL,
    is_active ENUM('Active', 'Inactive') DEFAULT 'Active',
    FOREIGN KEY (role_id) REFERENCES Roles(role_id)
);
```

## Setup Instructions

### 1. Run Default Roles Script
First, run the default roles insertion script to populate the database with common hotel roles:

```bash
# Navigate to admin directory
cd admin

# Run the script (via browser or command line)
http://localhost/homsdemo/admin/insert_default_roles.php
```

This will add 10 default roles:
- Hotel Manager
- Front Desk Staff
- Housekeeping Staff
- Maintenance Technician
- Security Guard
- Chef
- Waiter/Waitress
- Concierge
- Night Auditor
- Receptionist

### 2. Run Sample Staff Script (Optional)
To see the system in action with sample data, run:

```bash
http://localhost/homsdemo/admin/insert_sample_staff.php
```

This will add 8 sample staff members with different roles.

### 3. Access Staff Management
Navigate to the staff management page:
```
http://localhost/homsdemo/admin/staff.php
```

## Usage

### Adding a New Role
1. Click "Add Role" button
2. Fill in Role Name (required)
3. Add Description (optional)
4. Click "Add Role"
5. Role will be added to database and available for staff assignment

### Adding a New Staff Member
1. Click "Add Staff" button
2. Fill in all required fields:
   - First Name
   - Last Name
   - Email (must be unique)
   - Phone Number (must be unique)
   - Gender
   - Role (select from dropdown)
   - Status
3. Click "Add Staff Member"
4. Staff member will be added to database and appear in the listing

### Managing Staff
- **View**: Click "View Profile" to see staff details
- **Edit**: Click "Edit" to modify staff information
- **Filter**: Use search and filter options to find specific staff
- **Status**: Staff status is displayed with color-coded indicators

## File Structure

```
admin/
├── staff.php                 # Main staff management page
├── add_role.php             # Backend for adding roles
├── add_staff.php            # Backend for adding staff
├── insert_default_roles.php # Script to insert default roles
├── insert_sample_staff.php  # Script to insert sample staff
└── STAFF_SYSTEM_README.md   # This documentation
```

## Technical Details

### Frontend
- **Bootstrap 5**: Modern, responsive UI framework
- **Bootstrap Icons**: Professional icon set
- **Custom CSS**: Consistent with hotel admin theme
- **JavaScript**: Form handling and dynamic functionality

### Backend
- **PHP**: Server-side processing
- **MySQL**: Database operations
- **Prepared Statements**: SQL injection prevention
- **JSON API**: AJAX communication

### Security Features
- Input validation and sanitization
- SQL injection prevention
- Duplicate data prevention
- Proper error handling

## Troubleshooting

### Common Issues

1. **"Role not found" error when adding staff**
   - Ensure roles exist in the database
   - Run the default roles script first

2. **"Email already exists" error**
   - Each staff member must have a unique email
   - Check existing staff for duplicate emails

3. **"Phone number already exists" error**
   - Each staff member must have a unique phone number
   - Check existing staff for duplicate phone numbers

4. **Staff not appearing in listing**
   - Check database connection
   - Verify staff was added successfully
   - Check for JavaScript errors in browser console

### Database Issues
- Ensure MySQL service is running
- Check database connection credentials in `config/configdatabse.php`
- Verify tables exist and have correct structure

## Future Enhancements

Potential improvements for the staff management system:
- Staff profile editing
- Staff deletion (with confirmation)
- Bulk staff import/export
- Staff scheduling system
- Performance tracking
- Document management
- Advanced reporting

## Support

For technical support or questions about the staff management system, please refer to the main project documentation or contact the development team.
