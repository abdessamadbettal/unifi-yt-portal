# Logging System Documentation

## Overview

The UniFi portal now includes comprehensive logging for production monitoring. All events are logged to daily log files for easy troubleshooting and auditing.

## Log Location

Logs are stored in: `/var/www/wifi.oumlocacar.com/guest/s/logs/`

Log files are named by date: `portal_YYYY-MM-DD.log`

Example: `portal_2026-01-17.log`

## What is Logged

### 1. **Session Management**
- New session starts
- Session parameters (MAC address, AP MAC)
- User agent and request details

### 2. **Database Operations**
- Connection success/failure
- Query executions
- User registration
- Returning user detection

### 3. **UniFi Controller**
- Controller login attempts
- Guest authorization requests
- API responses
- Connection errors

### 4. **User Flow**
- New vs returning user detection
- Form submissions
- Page redirects
- Authorization status

### 5. **Errors**
- Database connection failures
- Query errors
- UniFi API failures
- Configuration issues

## Log Levels

- **INFO**: General information about system operation
- **SUCCESS**: Successful operations (auth, DB insert, etc.)
- **WARNING**: Potential issues that don't stop execution
- **ERROR**: Failures that need attention
- **DEBUG**: Detailed debugging information

## Log Format

Each log entry includes:
```
[YYYY-MM-DD HH:MM:SS] [LEVEL] [IP: x.x.x.x] [Session: xxxxxxxx] Message | Context: {json}
```

Example:
```
[2026-01-17 14:30:45] [SUCCESS] [IP: 192.168.1.100] [Session: a1b2c3d4] Guest authorized successfully | Context: {"mac":"aa:bb:cc:dd:ee:ff","duration":"120"}
```

## Viewing Logs

### View today's log:
```bash
tail -f /var/www/wifi.oumlocacar.com/guest/s/logs/portal_$(date +%Y-%m-%d).log
```

### View last 50 lines:
```bash
tail -n 50 /var/www/wifi.oumlocacar.com/guest/s/logs/portal_$(date +%Y-%m-%d).log
```

### Search for errors:
```bash
grep ERROR /var/www/wifi.oumlocacar.com/guest/s/logs/portal_*.log
```

### Search for specific MAC address:
```bash
grep "aa:bb:cc:dd:ee:ff" /var/www/wifi.oumlocacar.com/guest/s/logs/portal_*.log
```

### View all authorization attempts:
```bash
grep "authorize_guest" /var/www/wifi.oumlocacar.com/guest/s/logs/portal_*.log
```

### Count successful authorizations today:
```bash
grep -c "Guest authorized successfully" /var/www/wifi.oumlocacar.com/guest/s/logs/portal_$(date +%Y-%m-%d).log
```

## Log Rotation

Logs are automatically separated by date. To manage old logs, you can set up a cron job:

```bash
# Delete logs older than 30 days
find /var/www/wifi.oumlocacar.com/guest/s/logs/ -name "portal_*.log" -mtime +30 -delete
```

Add to crontab (run daily at 2 AM):
```bash
crontab -e
```
Add:
```
0 2 * * * find /var/www/wifi.oumlocacar.com/guest/s/logs/ -name "portal_*.log" -mtime +30 -delete
```

## Setting Up Log Permissions

After deploying to your droplet:

```bash
cd /var/www/wifi.oumlocacar.com/guest/s
mkdir -p logs
chmod 755 logs
chown www-data:www-data logs
```

## Monitoring Common Issues

### Check if database connection is failing:
```bash
grep "Database connection failed" logs/portal_*.log
```

### Check UniFi controller issues:
```bash
grep "UniFi controller login failed\|UniFi connection exception" logs/portal_*.log
```

### Check new user registrations:
```bash
grep "New user registered successfully" logs/portal_*.log
```

### Check returning users:
```bash
grep "Returning user detected" logs/portal_*.log
```

## Real-time Monitoring Dashboard

Create a simple monitoring script:

```bash
#!/bin/bash
# Save as monitor.sh

LOG_FILE="/var/www/wifi.oumlocacar.com/guest/s/logs/portal_$(date +%Y-%m-%d).log"

echo "=== UniFi Portal Status ==="
echo "Today: $(date)"
echo ""
echo "Total Events: $(wc -l < $LOG_FILE)"
echo "Errors: $(grep -c ERROR $LOG_FILE)"
echo "Successful Auths: $(grep -c "Guest authorized successfully" $LOG_FILE)"
echo "New Users: $(grep -c "New user registered" $LOG_FILE)"
echo "Returning Users: $(grep -c "Returning user detected" $LOG_FILE)"
echo ""
echo "=== Last 10 Events ==="
tail -n 10 $LOG_FILE
```

Run: `bash monitor.sh`

## Troubleshooting

If logs are not being created:
1. Check directory exists: `ls -la logs/`
2. Check permissions: `ls -la logs/`
3. Check PHP error log: `tail /var/log/apache2/error.log`
4. Ensure logger.php is loaded: `grep "require 'logger.php'" default/header.php`

## Security Note

Log files may contain sensitive information (IP addresses, MAC addresses, emails). Ensure proper file permissions:
- Logs directory: 755 (drwxr-xr-x)
- Log files: 644 (-rw-r--r--)
- Owner: www-data:www-data
