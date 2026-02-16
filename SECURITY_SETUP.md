# Security Setup Guide
## N&E Innovations Compliance Portal (AWS Lightsail)

---

## Authentication System

The portal has **two separate authentication layers**:

### 1. User Portal Login
- **Location**: All pages (enforced by `includes/user-auth.php`)
- **Type**: Individual email/password accounts stored in `users` table
- **Features**:
  - Bcrypt password hashing
  - 4-hour session timeout
  - Account expiry dates
  - Forced password change on first login
  - Activity logging per user

### 2. Admin Panel Login
- **Location**: `/admin/` directory
- **Type**: Separate username/password in `admin_users` table
- **Default Credentials**: admin / admin123
- **Features**:
  - 1-hour session timeout
  - Admin activity logging

---

## Critical: Change Default Admin Password

**Immediately after installation, change the default admin password.**

### Method 1: Via MySQL Command Line

```bash
# SSH into your Lightsail instance
ssh -i your-key.pem bitnami@YOUR_IP

# Generate a new password hash
php -r "echo password_hash('YourNewSecurePassword', PASSWORD_DEFAULT) . PHP_EOL;"

# Update in database
mysql -u root -p
```

```sql
USE compliance_portal;
UPDATE admin_users SET password_hash = 'PASTE_HASH_HERE' WHERE username = 'admin';
```

### Method 2: Using the Password Generator

1. Edit `admin/change-password.php` on the server
2. Uncomment and set your password
3. Visit the page in browser to get the hash
4. Update the hash in the database
5. **Delete the file immediately after use**

---

## SSL/HTTPS Setup

Use the Bitnami HTTPS tool (Let's Encrypt):

```bash
sudo /opt/bitnami/bncert-tool
```

After SSL is configured, uncomment the HTTPS redirect in `.htaccess`:

```apache
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

---

## PHP-FPM Configuration

Since Bitnami LAMP uses PHP-FPM (not mod_php), PHP settings are configured in `.user.ini` in the document root, **not** via `php_value` in `.htaccess`.

Current settings in `.user.ini`:
```ini
upload_max_filesize = 25M
post_max_size = 25M
max_execution_time = 300
memory_limit = 256M
```

To modify: edit `.user.ini` and wait up to 5 minutes for PHP-FPM to pick up changes (or restart PHP-FPM).

---

## File Permissions

Recommended for Bitnami LAMP:

```bash
# General files
sudo chown -R bitnami:daemon /opt/bitnami/apache2/htdocs
sudo chmod -R 755 /opt/bitnami/apache2/htdocs

# PHP/HTML/JS files
sudo find /opt/bitnami/apache2/htdocs -name "*.php" -exec chmod 644 {} \;

# Writable directories (uploads, logs)
sudo chmod 775 /opt/bitnami/apache2/htdocs/uploads/documents
sudo chmod 775 /opt/bitnami/apache2/htdocs/logs
```

---

## Protected Files

The `.htaccess` blocks direct HTTP access to:
- `.htaccess` itself
- `.user.ini` (PHP settings)
- `config.php` (database credentials)
- `db.php` (database connection)
- `.env` files
- `.git` directory

Uploaded PDFs in `uploads/documents/` are blocked by a separate `.htaccess` in that directory. They can only be accessed through the authenticated PHP endpoints.

---

## Security Checklist

After installation:

- [ ] Change default admin password from `admin123`
- [ ] Update `SECURE_KEY` in `includes/config.php` to a random string
- [ ] Set up SSL with `bncert-tool`
- [ ] Uncomment HTTPS redirect in `.htaccess`
- [ ] Delete `admin/change-password.php` if used
- [ ] Verify `uploads/documents/` returns 403 when accessed directly
- [ ] Create user accounts via admin panel (don't share admin credentials)
- [ ] Set up regular database backups
- [ ] Review activity logs weekly

---

## Database Backup

```bash
# SSH into instance
mysqldump -u root -p compliance_portal > backup_$(date +%Y%m%d).sql

# Download to local machine
scp -i your-key.pem bitnami@YOUR_IP:~/backup_*.sql ./
```

---

## Firewall (Lightsail Networking)

In the Lightsail console, configure the instance networking to only allow:
- **Port 22** (SSH) - restrict to your IP if possible
- **Port 80** (HTTP)
- **Port 443** (HTTPS)

Block all other inbound ports.

---

## Important Reminders

**NEVER:**
- Share admin credentials publicly
- Commit `config.php` with real credentials to a public repository
- Leave default passwords unchanged
- Keep `change-password.php` on the server after use

**ALWAYS:**
- Use strong, unique passwords
- Enable HTTPS/SSL
- Keep regular database backups
- Monitor activity logs via admin panel
- Set account expiry dates for temporary users

---

Last Updated: February 2026
