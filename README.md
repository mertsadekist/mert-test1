# IST Real Estate Management System

## Overview
A comprehensive real estate management system for managing properties, developers, and projects.

## Features
- User authentication and role-based access control
- Property and project management
- Excel file import/export functionality
- PDF report generation
- Secure file upload handling
- Advanced search and filtering capabilities

## Requirements
- PHP >= 7.4
- MySQL/MariaDB
- Composer
- Web server (Apache/Nginx)

## Installation

1. Clone the repository:
```bash
git clone [repository-url]
cd ist-real-estate
```

2. Install dependencies:
```bash
composer install
```

3. Create and configure environment file:
```bash
cp .env.example .env
# Edit .env with your configuration
```

4. Set up the database:
- Create a new MySQL database
- Import the database schema from `database/schema.sql`

5. Configure web server:
- Point document root to the project's root directory
- Ensure `uploads`, `logs`, and `cache` directories are writable

6. Set up permissions:
```bash
chmod -R 755 .
chmod -R 777 uploads/ logs/ cache/ sessions/
```

## Configuration

### Environment Variables
- `DB_HOST`: Database host
- `DB_DATABASE`: Database name
- `DB_USER`: Database username
- `DB_PASS`: Database password
- `APP_DEBUG`: Enable/disable debug mode
- `APP_URL`: Application URL

### Security Settings
- `SESSION_LIFETIME`: Session timeout in seconds
- `MAX_LOGIN_ATTEMPTS`: Maximum failed login attempts
- `LOCKOUT_TIME`: Account lockout duration

### File Upload Settings
- `MAX_UPLOAD_SIZE`: Maximum file upload size
- `ALLOWED_FILE_TYPES`: Allowed file extensions

## Security Features
- CSRF protection
- XSS prevention
- SQL injection protection
- Secure session handling
- Input validation and sanitization
- File upload security
- Password hashing
- Rate limiting

## Directory Structure
```
├── assets/          # Static assets (CSS, JS, images)
├── includes/        # Core PHP classes
├── templates/       # HTML templates
├── uploads/         # Uploaded files
├── logs/            # Application logs
├── cache/           # Cache files
├── sessions/        # Session files
├── database/        # Database schema and migrations
└── vendor/          # Composer dependencies
```

## Development

### Coding Standards
This project follows PSR-12 coding standards. To check and fix code style:
```bash
composer cs-check  # Check code style
composer cs-fix    # Fix code style
```

### Static Analysis
Run PHPStan for static code analysis:
```bash
composer analyze
```

### Testing
Run PHPUnit tests:
```bash
composer test
```

## Maintenance

### Log Rotation
Logs are automatically rotated daily and kept for 30 days.

### Cache Management
To clear application cache:
```bash
rm -rf cache/*
```

### Session Management
Sessions are stored in the `sessions` directory and automatically cleaned up.

## Support
For support and bug reports, please create an issue in the project repository.

## License
This project is proprietary software. All rights reserved.
