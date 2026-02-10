# Security Setup Guide
## N&E Innovations Compliance Portal

This guide covers all password protection and security measures for your website.

---

## Current Security Features

Your compliance portal has **two levels of password protection**:

### 1. Admin Panel Login (PHP Session-Based)
- **Location**: `/admin/` directory
- **Type**: PHP login with database authentication
- **Default Credentials**:
  - Username: `admin`
  - Password: `admin123`
- **Features**:
  - Secure password hashing (bcrypt)
  - Session management
  - Activity logging
  - Automatic timeout after 1 hour of inactivity

### 2. Optional HTTP Basic Authentication
- **Location**: Can be added to `/admin/` directory
- **Type**: Apache .htpasswd protection
- **Purpose**: Additional security layer BEFORE PHP login
- **Status**: Currently disabled (needs manual setup)

---

## ⚠️ CRITICAL: Change Default Admin Password

**IMMEDIATELY after installation, change the default admin password!**

### Method 1: Using the Password Generator (Recommended)

1. Navigate to: `https://your-domain.infinityfree.com/admin/change-password.php`

2. You'll see instructions. Download the file, open it in a text editor

3. Find this line:
   ```php
   // $new_password = 'your_new_password';
   ```

4. Uncomment and set your new password:
   ```php
   $new_password = 'MyNewSecurePassword123!';
   ```

5. Save and re-upload the file

6. Visit the page again - it will show your password hash

7. Log in to **phpMyAdmin**:
   - Go to InfinityFree Control Panel
   - Click **phpMyAdmin**
   - Select your database
   - Navigate to `admin_users` table
   - Click **Edit** on the admin user
   - Replace the `password_hash` field with the new hash
   - Click **Go** to save

8. **DELETE** `change-password.php` from your server immediately!

9. Test the new password by logging out and back in

### Method 2: Using PHP Command Line

If you have PHP command line access:

```bash
php -r "echo password_hash('YourNewPassword', PASSWORD_DEFAULT);"
```

Copy the output hash and update it in the database via phpMyAdmin.

---

## Adding HTTP Basic Authentication (Double Password Protection)

For extra security, add Apache .htpasswd protection as a second layer.

### Step 1: Create Password File

**On Your Computer (Mac/Linux):**

1. Open Terminal

2. Run:
   ```bash
   htpasswd -c .htpasswd admin
   ```

3. Enter your password when prompted

4. This creates a `.htpasswd` file

**On Windows:**

Use an online .htpasswd generator:
- Visit: https://hostingcanada.org/htpasswd-generator/
- Enter username: `admin`
- Enter a strong password
- Copy the generated line

Create a file named `.htpasswd` with this content:
```
admin:$apr1$encrypted$hashhere
```

### Step 2: Upload .htpasswd File

1. Upload `.htpasswd` to a secure location on your server
   - **Recommended**: Upload OUTSIDE of `htdocs` (if possible)
   - **Alternative**: Upload to `htdocs` (but protect it with .htaccess)

2. Note the **full server path**. Examples:
   - Outside htdocs: `/home/username/.htpasswd`
   - Inside htdocs: `/home/username/htdocs/.htpasswd`

### Step 3: Enable .htaccess Protection

1. Open `/admin/.htaccess` file

2. Find these commented lines:
   ```apache
   # AuthType Basic
   # AuthName "N&E Innovations Admin Area"
   # AuthUserFile /full/path/to/.htpasswd
   # Require valid-user
   ```

3. Uncomment them:
   ```apache
   AuthType Basic
   AuthName "N&E Innovations Admin Area"
   AuthUserFile /home/yourusername/htdocs/.htpasswd
   Require valid-user
   ```

4. Replace `/home/yourusername/htdocs/.htpasswd` with your actual path

5. Save and upload

### Step 4: Test Double Authentication

1. Visit `/admin/` in your browser

2. You should see **two password prompts**:
   - **First**: HTTP Basic Auth (browser popup) - Use .htpasswd username/password
   - **Second**: PHP Login Page - Use admin panel username/password

3. Both must be correct to access the admin panel

---

## Finding Your Server Path (for .htpasswd)

### Method 1: Create a Test PHP File

1. Create `path.php` in your `htdocs` folder:
   ```php
   <?php
   echo "Full server path: " . __DIR__;
   ?>
   ```

2. Visit: `https://your-domain.infinityfree.com/path.php`

3. Note the path shown (e.g., `/home/epiz_12345678/htdocs`)

4. Your .htpasswd path would be: `/home/epiz_12345678/htdocs/.htpasswd`
   or outside: `/home/epiz_12345678/.htpasswd`

5. **DELETE** `path.php` after getting the path

### Method 2: Check InfinityFree Control Panel

- InfinityFree usually displays your home directory path
- Look for "Home Directory" or "Document Root" information

---

## Password Best Practices

### Strong Password Requirements:

✅ **DO:**
- Use at least 12 characters
- Mix uppercase and lowercase letters
- Include numbers (0-9)
- Use special characters (!@#$%^&*)
- Use unique passwords for each system
- Consider using a password manager

❌ **DON'T:**
- Use personal information (names, birthdays)
- Use common words or patterns
- Reuse passwords from other sites
- Share passwords via email or chat
- Use default passwords

### Example Strong Passwords:
- `Tr0pic@l$unSet2026!`
- `Qu!ckB0wnF0x#Jumps`
- `L1m3$toNe#River99`

---

## Additional Security Measures

### 1. Enable HTTPS (SSL)

1. Go to InfinityFree Control Panel
2. Navigate to **SSL Certificates**
3. Enable **Free SSL** (Let's Encrypt)
4. Wait for activation (can take a few minutes)
5. Your site will be accessible via `https://`

The `.htaccess` file automatically redirects HTTP to HTTPS once SSL is enabled.

### 2. Regular Security Maintenance

**Weekly:**
- Check activity logs in admin dashboard
- Verify all document links are working

**Monthly:**
- Review user accounts (if you add more admins)
- Check for any suspicious activity in logs
- Backup your database

**Quarterly:**
- Update admin password
- Review and update security settings
- Test all authentication systems

### 3. Database Security

**Protect phpMyAdmin Access:**
- Never share phpMyAdmin credentials
- Use strong MySQL password
- Only access from trusted networks

**Regular Backups:**
1. Log in to phpMyAdmin
2. Select your database
3. Click **Export**
4. Choose **Quick** method
5. Click **Go** to download
6. Store backups securely offline

### 4. File Permissions

Recommended permissions for InfinityFree:
- Directories: `755`
- PHP files: `644`
- .htaccess files: `644`
- .htpasswd file: `644`

### 5. Hide Sensitive Information

**In config.php:**
- Set `display_errors = 0` in production
- Use strong `SECURE_KEY`
- Never commit config.php to public repositories

**Protect sensitive files:**
The `.htaccess` file already blocks access to:
- `.htaccess`
- `config.php`
- `db.php`
- `.env`
- `.git`

---

## Security Checklist

After installation, complete this checklist:

- [ ] Change default admin password from `admin123`
- [ ] Update `SECURE_KEY` in `includes/config.php`
- [ ] Enable HTTPS/SSL in InfinityFree
- [ ] (Optional) Set up HTTP Basic Auth for /admin/
- [ ] Delete `change-password.php` after use
- [ ] Test login with new credentials
- [ ] Set up regular database backups
- [ ] Review activity logs weekly
- [ ] Add your admin email to config
- [ ] Test password reset procedure
- [ ] Document your passwords securely (use password manager)

---

## Troubleshooting

### "Access Denied" when accessing /admin/

**Cause**: HTTP Basic Auth is enabled but credentials are wrong

**Solution**:
1. Check your .htpasswd username/password
2. Try disabling HTTP Auth temporarily (comment out lines in `/admin/.htaccess`)
3. Use PHP login alone until HTTP Auth is properly configured

### "Session Expired" Too Frequently

**Cause**: Session timeout set too short

**Solution**:
1. Edit `includes/config.php`
2. Change `ADMIN_SESSION_TIMEOUT` value:
   ```php
   define('ADMIN_SESSION_TIMEOUT', 7200); // 2 hours instead of 1
   ```

### Forgot Admin Password

**Solution**:
1. Use `change-password.php` to generate new hash
2. Update directly in database via phpMyAdmin
3. No email reset needed - direct database access

---

## Getting Help

If you encounter security issues:

1. **Check this guide** - Most issues are covered here
2. **InfinityFree Forum**: https://forum.infinityfree.com/
3. **Email Support**: business@vi-kang.com

---

## Important Reminders

🔴 **NEVER:**
- Share admin credentials publicly
- Commit passwords to Git repositories
- Use HTTP (always use HTTPS)
- Leave default passwords unchanged
- Keep password generator files on server

🟢 **ALWAYS:**
- Use strong, unique passwords
- Enable HTTPS/SSL
- Keep regular backups
- Monitor activity logs
- Delete sensitive utility files after use

---

**Last Updated**: February 2026
**Portal Version**: 1.0
