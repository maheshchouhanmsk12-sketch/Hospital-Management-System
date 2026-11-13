#!/bin/bash
set -e

# Render assigns dynamic PORT environment variable (default: 10000)
PORT="${PORT:-10000}"
echo "-----------------------------------------------------"
echo "Starting Apache on Port: ${PORT}"
echo "-----------------------------------------------------"

# Update Apache listening ports
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# REVERSE PROXY FIX: Ensure Apache never appends internal port (e.g. :10000) to external redirects
cat << 'EOF' > /etc/apache2/conf-available/reverse-proxy-fixes.conf
ServerName localhost
UseCanonicalName Off
UseCanonicalPhysicalPort Off

# Prevent internal port leaking in headers
<IfModule mod_headers.c>
    Header unset X-Forwarded-Port
</IfModule>
EOF

a2enconf reverse-proxy-fixes > /dev/null 2>&1 || true

# Verify AllowOverride All
sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

echo "Apache configuration updated successfully. Launching apache2-foreground..."
exec apache2-foreground
