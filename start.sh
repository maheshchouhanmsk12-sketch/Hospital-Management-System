#!/bin/bash
set -e

PORT="${PORT:-10000}"
echo "====================================================="
echo " Starting Apache Server on dynamic PORT: ${PORT}"
echo "====================================================="

# Cleanly configure ports.conf
echo "Listen ${PORT}" > /etc/apache2/ports.conf

# Cleanly configure 000-default.conf VirtualHost
cat << EOF > /etc/apache2/sites-available/000-default.conf
<VirtualHost *:${PORT}>
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html
    DirectoryIndex index.php login.php index.html

    <Directory /var/www/html>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/error.log
    CustomLog \${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF

# Reverse Proxy config to prevent port leaking in redirects
cat << 'EOF' > /etc/apache2/conf-available/reverse-proxy.conf
ServerName localhost
UseCanonicalName Off
UseCanonicalPhysicalPort Off
EOF

a2enconf reverse-proxy > /dev/null 2>&1 || true

# Ensure global apache2.conf has AllowOverride All
sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

echo "Apache configuration ready. Executing apache2-foreground..."
exec apache2-foreground
