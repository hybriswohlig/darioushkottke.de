<?php
/**
 * Password Change Utility
 * Use this to generate a new password hash for the admin user
 *
 * SECURITY: Delete this file after generating your password hash!
 */

// Uncomment the line below and replace 'your_new_password' with your desired password
// $new_password = 'your_new_password';

// Generate password hash
if (isset($new_password)) {
    $hash = password_hash($new_password, PASSWORD_DEFAULT);

    echo "<!DOCTYPE html>";
    echo "<html><head><title>Password Hash Generator</title>";
    echo "<style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f9fafb; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #16a34a; }
        .hash-box { background: #f3f4f6; padding: 15px; border-radius: 5px; font-family: monospace; word-break: break-all; margin: 20px 0; }
        .warning { background: #fee2e2; border-left: 4px solid #dc2626; padding: 15px; margin: 20px 0; }
        .success { background: #dcfce7; border-left: 4px solid #16a34a; padding: 15px; margin: 20px 0; }
        code { background: #f3f4f6; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
        ol { line-height: 1.8; }
    </style></head><body>";

    echo "<div class='container'>";
    echo "<h1>✅ Password Hash Generated</h1>";

    echo "<div class='success'>";
    echo "<strong>Success!</strong> Your new password hash has been generated.";
    echo "</div>";

    echo "<h2>Your Password Hash:</h2>";
    echo "<div class='hash-box'>" . htmlspecialchars($hash) . "</div>";

    echo "<h2>Next Steps:</h2>";
    echo "<ol>";
    echo "<li>Copy the hash above</li>";
    echo "<li>Log in to <strong>phpMyAdmin</strong> or connect to your MySQL database</li>";
    echo "<li>Select your database</li>";
    echo "<li>Navigate to the <code>admin_users</code> table</li>";
    echo "<li>Click <strong>Edit</strong> on the admin user</li>";
    echo "<li>Replace the <code>password_hash</code> field with the hash above</li>";
    echo "<li>Click <strong>Go</strong> to save</li>";
    echo "<li><strong>IMPORTANT:</strong> Delete this file (<code>change-password.php</code>) from your server immediately!</li>";
    echo "</ol>";

    echo "<div class='warning'>";
    echo "<strong>⚠️ Security Warning:</strong><br>";
    echo "Delete this file immediately after updating your password in the database. ";
    echo "This file should not remain on your server as it contains sensitive information.";
    echo "</div>";

    echo "</div></body></html>";
} else {
    echo "<!DOCTYPE html>";
    echo "<html><head><title>Password Hash Generator</title>";
    echo "<style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f9fafb; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #16a34a; }
        .info { background: #dbeafe; border-left: 4px solid #2563eb; padding: 15px; margin: 20px 0; }
        code { background: #f3f4f6; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
        ol { line-height: 1.8; }
        pre { background: #1f2937; color: #f9fafb; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style></head><body>";

    echo "<div class='container'>";
    echo "<h1>🔐 Admin Password Generator</h1>";

    echo "<div class='info'>";
    echo "<strong>ℹ️ Instructions:</strong> Follow the steps below to generate a new admin password hash.";
    echo "</div>";

    echo "<h2>How to Use:</h2>";
    echo "<ol>";
    echo "<li>Open this file (<code>change-password.php</code>) in a text editor</li>";
    echo "<li>Find this line:<br><pre>// \$new_password = 'your_new_password';</pre></li>";
    echo "<li>Uncomment it and replace <code>your_new_password</code> with your desired password:<br><pre>\$new_password = 'MySecurePassword123!';</pre></li>";
    echo "<li>Save the file and upload it back to your server</li>";
    echo "<li>Visit this page again in your browser</li>";
    echo "<li>Follow the instructions to update your password in the database</li>";
    echo "<li><strong>Delete this file</strong> immediately after use!</li>";
    echo "</ol>";

    echo "<div class='info'>";
    echo "<strong>🔒 Password Tips:</strong><br>";
    echo "• Use at least 12 characters<br>";
    echo "• Mix uppercase, lowercase, numbers, and symbols<br>";
    echo "• Avoid common words or personal information<br>";
    echo "• Don't reuse passwords from other sites";
    echo "</div>";

    echo "</div></body></html>";
}
?>
