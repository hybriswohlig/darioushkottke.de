# N&E Innovations Compliance Portal

A modern, AG1-inspired environmental documentation portal with dynamic content management, built for InfinityFree hosting.

## Features

- **🔒 Private Portal**: Entire website is password-protected - visitors must log in to access any content
- **AG1-Inspired Design**: Modern, minimal aesthetic with smooth animations
- **Dynamic Content Management**: Full CRUD system for documents via admin panel
- **Category-Based Organization**: Separate pages for LCA Reports, Certifications, Impact Studies, Technical Docs, and Compliance
- **Search & Filter**: Powerful search functionality across all documents
- **Two-Level Security**: Separate authentication for visitors and admin panel
- **Mobile Responsive**: Optimized for desktop with basic mobile support
- **MySQL Database**: Structured data storage for scalability

## Technology Stack

- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Hosting**: InfinityFree compatible

## Prerequisites

- InfinityFree account (or any PHP + MySQL hosting)
- FTP client (FileZilla recommended)
- Text editor for configuration

## Installation Guide

### Step 1: Set Up MySQL Database on InfinityFree

1. Log in to your InfinityFree control panel at https://dash.infinityfree.com/
2. Go to **MySQL Databases** section
3. Click **Create Database**
4. Note down your database credentials:
   - Database Name (e.g., `epiz_12345678_compliance`)
   - Database Username (e.g., `epiz_12345678`)
   - Database Password
   - Database Hostname (usually `sqlXXX.infinityfree.com`)

### Step 2: Upload Files via FTP

1. Connect to your InfinityFree hosting via FTP:
   - Host: Your domain or `ftpupload.net`
   - Username: Your FTP username
   - Password: Your FTP password
   - Port: 21

2. Upload all project files to the `htdocs` folder (or your domain's root directory)

3. Ensure the following structure is maintained:
   ```
   htdocs/
   ├── admin/
   ├── api/
   ├── assets/
   │   ├── css/
   │   └── js/
   ├── database/
   ├── includes/
   ├── pages/
   ├── new-index.php
   └── README.md
   ```

### Step 3: Configure Database Connection

1. Open `includes/config.php` in a text editor
2. Update the database credentials:

```php
define('DB_HOST', 'sqlXXX.infinityfree.com');  // Your MySQL hostname
define('DB_NAME', 'epiz_12345678_compliance'); // Your database name
define('DB_USER', 'epiz_12345678');            // Your database username
define('DB_PASS', 'your_password_here');       // Your database password
```

3. Update the site URL:

```php
define('SITE_URL', 'https://your-domain.infinityfree.com');
```

4. Change the security key to a random string:

```php
define('SECURE_KEY', 'CHANGE_THIS_TO_A_RANDOM_STRING');
```

5. Save and re-upload the file via FTP

### Step 4: Import Database Schema

1. In InfinityFree control panel, go to **phpMyAdmin**
2. Select your database from the left sidebar
3. Click the **Import** tab
4. Choose file: Select `database/schema.sql`
5. Click **Go** to import

6. (Optional) Import sample data:
   - Click **Import** again
   - Choose file: Select `database/sample_data.sql`
   - Click **Go** to import

### Step 5: Set Up URL Rewriting (Important!)

1. Create a `.htaccess` file in your root directory (if it doesn't exist)
2. Add the following code:

```apache
# Enable Rewrite Engine
RewriteEngine On

# Redirect to HTTPS (recommended)
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Remove .php extension
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME}.php -f
RewriteRule ^(.*)$ $1.php [L]

# Redirect old index.html to new-index.php
RewriteRule ^index\.html$ new-index.php [L]
RewriteRule ^$ new-index.php [L]

# Error pages
ErrorDocument 404 /404.php
ErrorDocument 500 /500.php
```

3. Upload the `.htaccess` file to your root directory

### Step 6: Rename Files

1. Rename `new-index.php` to `index.php` (this will be your homepage)
2. Delete or move the old `index.html` to the `archive` folder

### Step 7: Test Visitor Login

1. Visit your website: `https://your-domain.infinityfree.com`

2. **You'll see a login page** - This is the visitor authentication system

3. **Default Visitor Password:** `CompliancePortal2026`
   - **🔴 CHANGE THIS IMMEDIATELY** after testing!
   - See [VISITOR_PASSWORD_SETUP.md](VISITOR_PASSWORD_SETUP.md) for instructions

4. After logging in:
   - You should see the homepage with categories
   - Click on any category to view documents
   - Test the search functionality
   - Session lasts 4 hours

5. **Important:** The entire website is now private - ALL visitors need this password!

📖 **Visitor Password Documentation**: See [VISITOR_PASSWORD_SETUP.md](VISITOR_PASSWORD_SETUP.md) for:
   - How to change the visitor password
   - Managing visitor access
   - Session settings
   - Sharing access securely

### Step 8: Access Admin Panel & Security Setup

1. Go to `https://your-domain.infinityfree.com/admin/`
2. Default credentials:
   - Username: `admin`
   - Password: `admin123`

3. **🔴 CRITICAL**: Change the admin password immediately!

   **Easy Method - Use Password Change Tool:**
   - Visit: `https://your-domain.infinityfree.com/admin/change-password.php`
   - Follow the on-screen instructions
   - Delete the file after use!

   **Manual Method:**
   - Generate hash via phpMyAdmin or PHP command line
   - Update the `admin_users` table
   - See [SECURITY_SETUP.md](SECURITY_SETUP.md) for detailed instructions

4. **Optional - Add Double Password Protection:**
   - Set up HTTP Basic Authentication (.htpasswd) for extra security
   - See [SECURITY_SETUP.md](SECURITY_SETUP.md) for complete guide

📖 **Full Security Documentation**: See [SECURITY_SETUP.md](SECURITY_SETUP.md) for:
   - Step-by-step password change instructions
   - How to enable HTTP Basic Auth (double password protection)
   - Security best practices
   - Troubleshooting guide

## Admin Panel Features

### Dashboard (`/admin/`)
- View statistics (total documents, published, views)
- See recent documents and activity
- Quick navigation to all sections

### Document Management (`/admin/documents.php`)
- Add new documents
- Edit existing documents
- Delete documents
- Manage metadata (key-value pairs)
- Set document status (Published, Under Review, etc.)
- Mark documents as featured

## Document Management Guide

### Adding a Document

1. Log in to the admin panel
2. Click **Documents** in the sidebar
3. Click **Add New Document**
4. Fill in the form:
   - **Category**: Select the appropriate category
   - **Title**: Document name
   - **Description**: Brief description
   - **File URL**: Link to Google Drive, Dropbox, or external file
   - **Status**: Published, Under Review, In Progress, etc.
   - **Version**: Optional version number
   - **Date Published**: Publication date
   - **Featured**: Check to highlight the document
5. Click **Save Document**

### Using External File Links

Since InfinityFree has storage limitations, it's recommended to host large files externally:

**Google Drive:**
1. Upload file to Google Drive
2. Right-click → Get link → Change to "Anyone with the link"
3. Copy the link
4. Use this format in File URL: `https://drive.google.com/file/d/FILE_ID/view`

**Dropbox:**
1. Upload file to Dropbox
2. Get shareable link
3. Replace `www.dropbox.com` with `dl.dropboxusercontent.com`
4. Use this link in File URL

## Customization

### Changing Colors

Edit `assets/css/style.css` and update the CSS variables:

```css
:root {
    --primary-green: #16a34a;      /* Main green color */
    --primary-green-dark: #15803d; /* Darker green */
    --primary-green-light: #22c55e; /* Lighter green */
    /* ... more colors */
}
```

### Updating Content

- **Homepage**: Edit `index.php`
- **About Page**: Edit `pages/about.php`
- **Footer**: Update in each page file or create a shared footer component

### Adding Categories

1. Log in to phpMyAdmin
2. Select your database
3. Go to the `categories` table
4. Click **Insert**
5. Fill in the category details:
   - name
   - slug (URL-friendly name, e.g., `new-category`)
   - description
   - icon_svg (SVG code for icon)
   - display_order (sort order)
6. The category will automatically appear in navigation

## Troubleshooting

### Database Connection Error

**Problem**: "Database connection error" message

**Solution**:
- Verify database credentials in `includes/config.php`
- Check that your MySQL service is active in InfinityFree
- Ensure the database was imported correctly

### 404 Errors on Pages

**Problem**: Category pages return 404

**Solution**:
- Verify `.htaccess` file is uploaded and configured
- Check that URL rewriting is enabled on your host
- Ensure PHP files have proper permissions (644)

### Admin Login Not Working

**Problem**: Can't log in to admin panel

**Solution**:
- Verify the `admin_users` table was imported
- Check that session cookies are enabled
- Clear browser cookies and try again
- Verify the password hash in the database

### Files Not Uploading

**Problem**: Can't upload files via FTP

**Solution**:
- Check your FTP credentials
- Ensure you're uploading to the correct directory (`htdocs`)
- Verify file permissions (folders: 755, files: 644)
- Check InfinityFree storage limits

## Security Best Practices

1. **Change Default Admin Password**: Update immediately after installation
2. **Update Security Key**: Change `SECURE_KEY` in `config.php` to a random string
3. **Use HTTPS**: Enable SSL/HTTPS in InfinityFree settings
4. **Regular Backups**: Export your database regularly via phpMyAdmin
5. **Keep PHP Updated**: InfinityFree automatically updates PHP
6. **Monitor Activity**: Check the activity log in admin dashboard

## Maintenance

### Database Backup

1. Go to phpMyAdmin
2. Select your database
3. Click **Export**
4. Choose **Quick** export method
5. Click **Go** to download SQL file

### Regular Updates

- Check document links regularly (ensure Google Drive links are still accessible)
- Review and update document statuses
- Clean up old activity logs periodically

## Support

For issues or questions:
- Email: business@vi-kang.com
- InfinityFree Forum: https://forum.infinityfree.com/

## License

Proprietary - N&E Innovations Pte Ltd

---

**Created for N&E Innovations by Claude Code**
Last Updated: February 2026