# Visitor Password Setup Guide
## Private Website Access Control

Your compliance portal now requires ALL visitors to enter a password before they can view any content. This makes your entire website private.

---

## How It Works

**Two-Level Security System:**

1. **Visitor Login** (All Pages)
   - Protects ALL website content
   - Single shared password for authorized visitors
   - Session lasts 4 hours
   - Required before viewing ANY page

2. **Admin Login** (Admin Panel Only)
   - Separate login for admin panel
   - Manage documents, categories, etc.
   - More detailed in [SECURITY_SETUP.md](SECURITY_SETUP.md)

---

## 🔑 Default Visitor Password

**Current Password:** `CompliancePortal2026`

**⚠️ CHANGE THIS IMMEDIATELY!**

---

## Changing the Visitor Password

### Step 1: Edit the Password File

1. Open the file: `includes/visitor-auth.php`

2. Find this line (around line 13):
   ```php
   define('VISITOR_PASSWORD', 'CompliancePortal2026');
   ```

3. Change the password to your desired password:
   ```php
   define('VISITOR_PASSWORD', 'YourNewPassword123!');
   ```

4. Save the file

5. Re-upload via FTP to your server

6. Test immediately by logging out and back in

### Password Recommendations:

✅ **Good Passwords:**
- `GreenTech@2026!`
- `Sustain#Innovate99`
- `EnviroDoc$Portal`

❌ **Avoid:**
- Common words: `password123`, `welcome`
- Company name alone: `NEInnovations`
- Easy to guess: `12345678`

---

## Visitor Login Page

**Location:** `/visitor-login.php`

### Features:
- Clean, professional design
- Automatic redirect to intended page after login
- Session timeout warning
- 4-hour session duration
- Secure password handling

### User Experience:
1. Visitor tries to access any page on your site
2. Automatically redirected to login page
3. Enter visitor password
4. Redirected to the page they wanted to visit
5. Can browse freely for 4 hours
6. After 4 hours of inactivity, must log in again

---

## Logging Out

Visitors can log out at any time using the **Logout button** in the navigation menu.

**Logout URL:** `/visitor-logout.php`

When they log out:
- Session is destroyed
- Redirected to login page
- Must enter password again to re-enter

---

## Session Management

### Session Duration
- **Active Session:** 4 hours
- **After 4 hours:** Automatic logout with timeout message

### What Triggers Timeout:
- 4 hours of **inactivity** (no page views)
- Closing browser (depending on settings)
- Manual logout

### Adjusting Session Time

To change the 4-hour timeout:

1. Open `includes/visitor-auth.php`

2. Find this line (around line 42):
   ```php
   if ($inactiveTime > 14400) { // 4 hours
   ```

3. Change the number (in seconds):
   - 2 hours = `7200`
   - 4 hours = `14400` (default)
   - 8 hours = `28800`
   - 24 hours = `86400`

4. Save and upload

---

## Sharing Access with Others

### Distributing the Password

When sharing the website with authorized users:

1. **Send them:**
   - Website URL: `https://your-domain.infinityfree.com`
   - Visitor password: `YourPassword`
   - Contact for admin access if needed

2. **Via secure channels:**
   - ✅ Encrypted email
   - ✅ Password manager (shared vault)
   - ✅ Secure messaging (Signal, WhatsApp)
   - ❌ Plain email
   - ❌ SMS
   - ❌ Slack/Teams (unless encrypted)

3. **Instructions to send:**
   ```
   Access the N&E Innovations Compliance Portal:

   1. Visit: https://your-domain.infinityfree.com
   2. Enter the portal password when prompted
   3. Your session will last 4 hours
   4. Use the Logout button when finished

   Password: [Send separately via secure channel]

   For questions: business@vi-kang.com
   ```

---

## Managing Multiple Passwords (Advanced)

If you need **different passwords for different groups**, you can modify the system:

### Option 1: Multiple Fixed Passwords

Edit `includes/visitor-auth.php`:

```php
// Multiple allowed passwords
$allowedPasswords = [
    'ClientPassword2026',      // For clients
    'PartnerAccess2026',       // For partners
    'AuditorView2026'          // For auditors
];

function verifyVisitorPassword($password) {
    global $allowedPasswords;
    return in_array($password, $allowedPasswords);
}
```

### Option 2: User-Based System (Requires Database)

For individual user accounts with unique passwords:
1. Create a `visitors` table in database
2. Add username/password fields
3. Modify login to check database
4. This requires more advanced PHP development

---

## Protecting the Admin Panel

The admin panel has **separate authentication**:

- **Visitor password** → Access website content
- **Admin credentials** → Manage website via admin panel

**Admin panel is double-protected:**
1. Must log in as visitor first
2. Then log in to admin panel with admin credentials

See [SECURITY_SETUP.md](SECURITY_SETUP.md) for admin panel security.

---

## Troubleshooting

### "Invalid Password" Error

**Causes:**
- Wrong password entered
- Password was changed on server
- Typing error (check caps lock)

**Solutions:**
1. Verify password in `includes/visitor-auth.php`
2. Check for typos or extra spaces
3. Re-upload the file if recently changed

### Session Keeps Expiring Too Soon

**Cause:** Session timeout set too short

**Solution:**
1. Edit `includes/visitor-auth.php`
2. Increase timeout value (see "Adjusting Session Time" above)
3. Check server settings (some hosts limit session time)

### Login Page Redirect Loop

**Cause:** File paths incorrect or visitor-auth.php not found

**Solutions:**
1. Verify `includes/visitor-auth.php` exists
2. Check file permissions (644)
3. Ensure correct path in require statements
4. Clear browser cookies and try again

### Can't Access Admin Panel

**Issue:** Stuck on visitor login

**Solution:**
1. Log in as visitor first
2. Navigate to `/admin/`
3. You'll see a separate admin login
4. These are two different systems

---

## Disabling Visitor Password Protection

If you want to make the site public again:

### Method 1: Comment Out Protection (Recommended)

In each protected file, comment out the visitor-auth line:

```php
<?php
// Protect this page - require visitor authentication
// require_once __DIR__ . '/includes/visitor-auth.php';  // DISABLED
?>
```

### Method 2: Remove Protection Completely

Delete these lines from all pages:
- `new-index.php`
- `pages/category.php`
- `pages/search.php`
- `pages/about.php`

**Note:** Admin panel will still be protected separately.

---

## Security Best Practices

### DO:
- ✅ Change default password immediately
- ✅ Use strong, unique passwords
- ✅ Share passwords via secure channels only
- ✅ Monitor who has access
- ✅ Change password regularly (quarterly)
- ✅ Use HTTPS (SSL/TLS) - enable in InfinityFree

### DON'T:
- ❌ Share password publicly
- ❌ Use common/weak passwords
- ❌ Write password in plain text in emails
- ❌ Leave default password unchanged
- ❌ Share same password as admin panel
- ❌ Give password to unauthorized users

---

## Quick Reference

| Item | Value |
|------|-------|
| **Login Page** | `/visitor-login.php` |
| **Logout Page** | `/visitor-logout.php` |
| **Password File** | `includes/visitor-auth.php` |
| **Default Password** | `CompliancePortal2026` |
| **Session Duration** | 4 hours |
| **Password Location** | Line 13 in `visitor-auth.php` |

---

## Need Help?

**For access issues:**
- Check this guide first
- Verify password in source file
- Test in incognito/private window
- Clear browser cookies

**For technical support:**
- Email: business@vi-kang.com
- InfinityFree Forum: https://forum.infinityfree.com/

---

**Last Updated:** February 2026
**Security Level:** Private Portal (Password Protected)
**Related Documentation:** [SECURITY_SETUP.md](SECURITY_SETUP.md)
