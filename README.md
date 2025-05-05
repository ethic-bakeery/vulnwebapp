# SecureCorp Portal - Vulnerable Web Application (Path Traversal)

## Description

The **SecureCorp Portal** is a PHP-based demo web application **intentionally vulnerable** to **Path Traversal** attacks. It allows attackers to manipulate file paths and potentially access sensitive server files. This application is for **educational purposes** only.

### Vulnerabilities

- **Path Traversal**: Attackers can manipulate file paths to access sensitive files outside of the intended directory (e.g., `/etc/passwd`).
- **Hardcoded Credentials**: Login uses static credentials (`admin/password123`).
- **No Rate Limiting**: Vulnerable to brute force login attempts.
- **Plaintext Password**: No password hashing.
- **Unprotected Logs**: Logs sensitive info to `/var/log/php-server.log`.

### Usage

1. Clone the repo:

   ```bash
   git clone https://github.com/your-username/securecorp-portal.git
   cd securecorp-portal
   ```

2. Run the server (requires sudo for logging):

   ```bash
   sudo php -S 192.168.10.6:8000 router.php
   ```

3. Visit the portal at:

   ```
   http://192.168.10.6:8000
   ```

4. Log in with:

   - Username: admin
   - Password: password123

5. View logs:

   ```bash
   tail -f /var/log/php-server.log
   ```

### Security Recommendations

- Fix Path Traversal by sanitizing file paths.
- Hash passwords using `password_hash()`.
- Implement rate limiting for login attempts.
- Secure log access and sensitive files.

