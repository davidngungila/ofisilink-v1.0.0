#!/bin/bash
# MySQL Access Fix Script - Run this on MySQL Server (192.168.100.109)
# This script will automatically grant MySQL access

echo "========================================"
echo "MySQL Access Fix Script"
echo "========================================"
echo ""
echo "This script will grant MySQL access for ZKBio Time.Net sync"
echo "Run this on the MySQL server: 192.168.100.109"
echo ""

# Connect to MySQL and run GRANT command
mysql -u root <<EOF
GRANT ALL PRIVILEGES ON ofisi.* TO 'root'@'%';
FLUSH PRIVILEGES;
EOF

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ MySQL access granted successfully!"
    echo ""
    echo "You can now run the sync command from Laravel:"
    echo "  php artisan attendance:sync-zkbiotime --device=\"UF 2000 HQ\""
else
    echo ""
    echo "❌ Failed to grant access. Please run manually:"
    echo "  mysql -u root"
    echo "  GRANT ALL PRIVILEGES ON ofisi.* TO 'root'@'%';"
    echo "  FLUSH PRIVILEGES;"
fi







