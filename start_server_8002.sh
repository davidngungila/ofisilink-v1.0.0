#!/bin/bash

echo "========================================"
echo "Starting Laravel Server on Port 8002"
echo "========================================"
echo ""
echo "Server will be available at:"
echo "  http://127.0.0.1:8002"
echo "  http://localhost:8002"
echo ""
echo "Attendance Module:"
echo "  http://127.0.0.1:8002/modules/hr/attendance"
echo ""
echo "Press Ctrl+C to stop the server"
echo "========================================"
echo ""

cd "$(dirname "$0")"
php artisan serve --host=127.0.0.1 --port=8002









