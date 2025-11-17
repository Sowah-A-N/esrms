==================================================================
ESRMS v2 - End-of-Semester Results Management System
==================================================================

✔ Base URL Fix Applied
The system is configured for http://localhost/esrms/
To move to another folder, change the BASE_URL constant in:
    config/config.php

Example:
    define('BASE_URL', '/results_portal/');

✔ .htaccess Configuration
The .htaccess file includes:
    RewriteEngine On
    RewriteBase /esrms/

Make sure Apache rewrite_module is enabled (WAMP: Apache → Modules → rewrite_module).

✔ Folder Placement
Place this folder in:
    C:\wamp64\www\

Access it via:
    http://localhost/esrms/

✔ Security Reminder
- Delete setup helpers (e.g., set_admin_password.php) after setup.
- Ensure uploads/files/ is writable by Apache (chmod 755).
- Use secure password hashes via PHP password_hash().
