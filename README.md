# N&E Innovations Compliance Portal

A private environmental documentation portal with dynamic content management, individual user authentication, and PDF document management with watermarked downloads.

## Features

- **Private Portal**: Individual user accounts with email-based login, expiry dates, and forced password changes
- **Admin Panel**: Full CRUD for documents, user management, and activity tracking
- **PDF Upload & Viewer**: Upload PDFs, view them in an embedded viewer, and download with identity watermarks
- **Category-Based Organization**: LCA Reports, Certifications, Impact Studies, Technical Docs, Compliance
- **Search & Filter**: Full-text search across all documents with tag-based filtering
- **Document Types**: External links (Google Drive), uploaded PDFs, and local HTML reports
- **Activity Logging**: Tracks user page views, document views, and PDF downloads with IP logging

## Technology Stack

- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **PDF Processing**: FPDI + TCPDF (via Composer)
- **Hosting**: AWS Lightsail (Bitnami LAMP stack)

## Server Requirements

- AWS Lightsail instance with Bitnami LAMP blueprint
- PHP 7.4+ with PHP-FPM (included in Bitnami)
- MySQL/MariaDB (included in Bitnami)
- Apache with mod_rewrite enabled (included in Bitnami)
- Composer (for PDF library dependencies)
- Git (for deployment)

## Project Structure

```
/opt/bitnami/apache2/htdocs/
├── admin/                    # Admin panel (documents, users, activity)
├── api/                      # REST API endpoints
│   ├── documents.php         # Document CRUD
│   ├── upload-document.php   # PDF file upload
│   ├── serve-pdf.php         # Auth-gated PDF streaming
│   ├── download-pdf.php      # Watermarked PDF download
│   ├── track-view.php        # View counting
│   └── user-activity.php     # Activity logging
├── assets/
│   ├── css/style.css         # Main stylesheet
│   └── js/main.js            # Client-side interactions
├── database/
│   ├── schema.sql            # Full database schema
│   ├── sample_data.sql       # Sample documents
│   ├── migration_user_auth.sql
│   └── migration_pdf_upload.sql
├── includes/
│   ├── config.php            # Database & site configuration
│   ├── db.php                # PDO database connection
│   ├── functions.php         # Shared utility functions
│   └── user-auth.php         # User authentication middleware
├── pages/
│   ├── category.php          # Category document listing
│   ├── search.php            # Document search
│   └── about.php             # About page
├── uploads/documents/        # Uploaded PDF storage (protected by .htaccess)
├── index.php                 # Homepage
├── login.php                 # User login
├── view-document.php         # Embedded PDF viewer
├── .htaccess                 # Apache rewrite rules & security headers
├── .user.ini                 # PHP-FPM settings (upload limits, memory)
└── composer.json             # PHP dependencies (FPDI, TCPDF)
```

## Installation Guide (AWS Lightsail)

### Step 1: Create Lightsail Instance

1. Go to AWS Lightsail console
2. Create instance with **LAMP (PHP 7)** blueprint
3. Choose instance plan (minimum 512MB RAM, 1GB recommended)
4. Launch and note the public IP address

### Step 2: SSH into the Instance

```bash
ssh -i your-key.pem bitnami@YOUR_IP_ADDRESS
```

### Step 3: Install Git and Composer

```bash
sudo apt-get update
sudo apt-get install git -y

# Install Composer
cd /tmp
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### Step 4: Clone and Deploy

```bash
cd /opt/bitnami/apache2/htdocs
# Remove default Bitnami page
sudo rm -rf *

# Clone repository
sudo git clone YOUR_REPO_URL .
sudo chown -R bitnami:daemon .
sudo chmod -R 755 .
sudo chmod -R 644 *.php *.html *.css *.js .htaccess .user.ini

# Install PHP dependencies
composer install

# Create required directories
mkdir -p uploads/documents logs
chmod 775 uploads/documents logs
```

### Step 5: Create MySQL Database

```bash
# Connect to MySQL (Bitnami default)
mysql -u root -p
# Password is in: /home/bitnami/bitnami_credentials
```

```sql
CREATE DATABASE compliance_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'portal_user'@'localhost' IDENTIFIED BY 'YOUR_SECURE_PASSWORD';
GRANT ALL PRIVILEGES ON compliance_portal.* TO 'portal_user'@'localhost';
FLUSH PRIVILEGES;
USE compliance_portal;
SOURCE /opt/bitnami/apache2/htdocs/database/schema.sql;
-- Optional: SOURCE /opt/bitnami/apache2/htdocs/database/sample_data.sql;
```

### Step 6: Configure the Application

Edit `includes/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'compliance_portal');
define('DB_USER', 'portal_user');
define('DB_PASS', 'YOUR_SECURE_PASSWORD');
define('SITE_URL', 'http://YOUR_IP_ADDRESS');
define('SECURE_KEY', 'CHANGE_TO_RANDOM_STRING');
```

### Step 7: Verify

1. Visit `http://YOUR_IP_ADDRESS` - should see the login page
2. Visit `http://YOUR_IP_ADDRESS/admin/` - admin login (default: admin / admin123)
3. **Change the admin password immediately** via the admin panel

## Admin Panel

### Document Management (`/admin/documents`)
- Add documents with three types: **External Link**, **PDF Upload**, or **HTML Report**
- Set status, tags, version, metadata, and featured flag
- PDF files are stored securely in `uploads/documents/` (not directly accessible)

### User Management (`/admin/users`)
- Create individual user accounts with email/password
- Set access expiry dates
- Force password changes on first login
- View user activity logs

## PDF Document Features

### Upload
Admin uploads a PDF (max 20MB) through the document form. Files are validated (MIME type, magic bytes, extension) and stored with sanitized filenames.

### Viewing
Users see PDFs in an embedded viewer (`/view-document`) with site branding and navigation. PDFs are served through an authenticated PHP endpoint - direct file access is blocked.

### Watermarked Download
When a user downloads a PDF, the system generates a copy with a footer watermark on every page containing:
- User's full name
- Company
- Email
- Download date/time

This enables tracking if documents are shared externally. All downloads are logged in the `document_downloads` audit table.

## Deployment Updates

To deploy changes from the repository:

```bash
cd /opt/bitnami/apache2/htdocs
sudo git pull origin main
composer install  # Only needed if composer.json changed
```

## SSL Setup (Recommended)

Use the Bitnami HTTPS configuration tool:

```bash
sudo /opt/bitnami/bncert-tool
```

Follow the prompts to set up Let's Encrypt SSL. After SSL is configured, uncomment the HTTPS redirect in `.htaccess`.

## Support

For issues or questions: business@vi-kang.com

## License

Proprietary - N&E Innovations Pte Ltd

---

Last Updated: February 2026
