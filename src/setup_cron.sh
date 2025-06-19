#!/bin/bash

# Get the current directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CRON_FILE="$SCRIPT_DIR/cron.php"

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo "PHP is not installed or not in PATH"
    exit 1
fi

# Check if cron.php exists
if [ ! -f "$CRON_FILE" ]; then
    echo "cron.php not found in $SCRIPT_DIR"
    exit 1
fi

# Create the cron job entry
CRON_ENTRY="0 * * * * /usr/bin/php $CRON_FILE >> $SCRIPT_DIR/cron.log 2>&1"

# Check if cron job already exists
if crontab -l 2>/dev/null | grep -q "$CRON_FILE"; then
    echo "Cron job already exists for $CRON_FILE"
else
    # Add the cron job
    (crontab -l 2>/dev/null; echo "$CRON_ENTRY") | crontab -
    echo "Cron job added successfully!"
    echo "The task reminder will run every hour"
    echo "Logs will be written to: $SCRIPT_DIR/cron.log"
fi

# Display current crontab
echo ""
echo "Current crontab entries:"
crontab -l 2>/dev/null || echo "No crontab entries found"
