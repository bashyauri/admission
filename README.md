# Admission Management & Student MIS System

A Laravel-based Admission Management and Student Management Information System (MIS) built for handling student applications, admissions, course registrations, fee payments, and result processing for academic institutions in Nigeria.

## Overview

This system manages the complete student lifecycle from admission to graduation, with NUC (National Universities Commission) compliant result processing, course versioning for data integrity, and comprehensive role-based access control.

## Tech Stack

- **Backend**: Laravel 12.x (PHP ^8.3)
- **Frontend**: Livewire 3.4 + Tailwind CSS
- **Database**: MySQL
- **Authentication**: Laravel Sanctum 4.0
- **PDF Generation**: Laravel DomPDF 3.0
- **Excel Export**: Maatwebsite Excel 3.1
- **QR Code**: Simple QR Code 4.2
- **Email**: MailerSend Laravel Driver 2.7
- **Monitoring**: Laravel Pulse 1.2

## Features

### Current Features
- User registration and authentication
- Role-based access control (Admin, Applicant, Student, HOD, CIT, Coordinator, IdCard Officer, Lecturer, Exam Officer)
- Programme management (Undergraduate, Postgraduate, HND, ND, NCE, PD)
- Department and school management
- Course management and department course allocation
- Student application workflow with status tracking
- O-level result management
- Certificate upload management
- Post-UTME credential management
- Course registration system
- Fee payment processing with transaction tracking
- Academic detail management with matric number generation

### Planned Extensions (Documented)
- NUC-compliant result processing system
- Course versioning for historical data integrity
- Automatic carry-over course registration
- Department-level unit validation
- Transcript generation and management
- Graduation eligibility checking
- Certificate generation and printing
- Assessment configuration with lecturer permissions
- Result approval workflow (Lecturer → HOD → Exam Officer)
- Hybrid result entry (web form + CSV upload)
- Analytics and reporting dashboards

## Documentation

For detailed documentation, see:

- **[SYSTEM_DOCUMENTATION.md](SYSTEM_DOCUMENTATION.md)** - Complete system architecture, database schema, roles, routes, Livewire components
- **[RESULT_PROCESSING_EXTENSION_PLAN.md](RESULT_PROCESSING_EXTENSION_PLAN.md)** - Detailed plan for extending to full Student MIS with result processing
- **[COURSE_VERSIONING_DATA_INTEGRITY.md](COURSE_VERSIONING_DATA_INTEGRITY.md)** - Course versioning system for historical data integrity
- **[DATABASE_OPTIMIZATION_PRODUCTION_SAFE.md](DATABASE_OPTIMIZATION_PRODUCTION_SAFE.md)** - Production-safe database optimization guide
- **[DATABASE_BACKUP_STRATEGY.md](DATABASE_BACKUP_STRATEGY.md)** - Comprehensive backup and recovery strategy
- **[PRODUCTION_PACKAGE_DEPLOYMENT.md](PRODUCTION_PACKAGE_DEPLOYMENT.md)** - Safe package deployment for production (no Git/staging)

## Installation

### Prerequisites
- PHP ^8.3
- Composer
- MySQL
- Node.js & NPM (for frontend assets)

### Steps

1. Clone the repository
```bash
git clone <repository-url>
cd admission
```

2. Install dependencies
```bash
composer install
npm install
```

3. Configure environment
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` with your database credentials and configuration.

4. Run migrations
```bash
php artisan migrate
```

5. Create storage link
```bash
php artisan storage:link
```

6. Build frontend assets
```bash
npm run build
```

7. Start development server
```bash
php artisan serve
```

## User Roles

The system supports the following roles with specific privileges:

- **Admin**: Full system access, user management, configuration
- **Applicant**: Application submission, document upload, payment
- **Student**: Course registration, result viewing, fee payment
- **HOD**: Department management, result approval, course allocation
- **CIT**: System administration, technical support
- **Coordinator**: Programme coordination, student oversight
- **IdCard Officer**: ID card generation and management
- **Lecturer**: Result entry, course management (planned)
- **Exam Officer**: Faculty-level result approval (planned)

## Database Schema

Key tables include:
- `users` - User accounts with UUID primary keys
- `programmes` - Academic programmes
- `departments` - Department management
- `courses` - Course catalog
- `department_courses` - Course allocation to departments
- `registered_courses` - Student course registrations
- `academic_details` - Student academic information
- `transactions` - Fee payment transactions
- `olevels`, `olevel_exams`, `olevel_subject_grades` - O-level results
- `certificate_uploads` - Document management
- `department_max_units` - Department-level unit constraints

See [SYSTEM_DOCUMENTATION.md](SYSTEM_DOCUMENTATION.md) for complete schema documentation.

## Production Deployment

This system is currently in production. For safe deployment:

1. **Backup Strategy**: Use Spatie Laravel Backup package (see [DATABASE_BACKUP_STRATEGY.md](DATABASE_BACKUP_STRATEGY.md))
2. **Database Optimization**: Follow production-safe guidelines (see [DATABASE_OPTIMIZATION_PRODUCTION_SAFE.md](DATABASE_OPTIMIZATION_PRODUCTION_SAFE.md))
3. **Package Installation**: Use direct deployment method (no Git/staging) - see [PRODUCTION_PACKAGE_DEPLOYMENT.md](PRODUCTION_PACKAGE_DEPLOYMENT.md)

## Development

### Running Tests
```bash
php artisan test
```

### Code Style
Follow Laravel conventions and existing code patterns in the project.

### Adding New Features
1. Review existing documentation
2. Follow architectural patterns
3. Update relevant documentation
4. Test thoroughly before deployment

## Support

For detailed technical documentation, refer to the markdown files listed above. Each document provides comprehensive information for developers and AI coding agents to understand and work with the system.

## License

MIT License
