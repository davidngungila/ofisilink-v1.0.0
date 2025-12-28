# ZKBio Time.Net - Running Requirements

## Quick Answer

**ZKBio Time.Net MUST be running** for new attendance records to be downloaded from the device and stored in the database.

However, **Laravel can read existing records** from the database even if ZKBio Time.Net is closed.

---

## Detailed Explanation

### How It Works

```
┌──────────────┐         ┌──────────────────┐         ┌──────────────┐
│ UF200-S      │  ────>  │  ZKBio Time.Net  │  ────>  │  Database    │
│ Device       │         │  (MUST RUN)      │         │  (SQLite/    │
│              │         │                  │         │   MySQL)     │
└──────────────┘         └──────────────────┘         └──────────────┘
     │                           │                            │
     │                           │                            │
     │ 1. Employee scans         │ 2. Downloads records       │ 3. Stores records
     │    fingerprint            │    every 5 minutes         │    in database
     │                           │                            │
     └───────────────────────────┴────────────────────────────┴──────────────┐
                                                                              │
                                                                              │
┌──────────────┐                                                             │
│  Laravel     │  <──────────────────────────────────────────────────────────┘
│  Application │        4. Reads database directly (ZKBio can be closed)
└──────────────┘
```

---

## When ZKBio Time.Net MUST Be Running

### ✅ For Downloading New Records from Device

**ZKBio Time.Net MUST be running** because:
- It's the software that connects to the UF200-S device
- It downloads attendance records from the device every 5 minutes
- Without it, **no new records** will be added to the database

**What happens if ZKBio is closed:**
- ❌ No new records downloaded from device
- ❌ Database won't get updated with new attendance
- ❌ Laravel will only sync old/existing records

---

## When ZKBio Time.Net Does NOT Need to Be Running

### ✅ For Laravel to Read Existing Records

**Laravel reads directly from the database**, so:
- ✅ Can read existing records even if ZKBio is closed
- ✅ Can sync historical data
- ✅ Database file (SQLite) or database server (MySQL) must be accessible

**What Laravel needs:**
- ✅ Database file path accessible (for SQLite)
- ✅ Database server running (for MySQL/MSSQL)
- ❌ ZKBio Time.Net does NOT need to be running for reading

---

## Recommended Setup

### Option 1: Always Running (Recommended)

**Best Practice**: Keep ZKBio Time.Net running 24/7

**Why:**
- ✅ Continuous attendance recording
- ✅ Automatic data download every 5 minutes
- ✅ No missed attendance records
- ✅ Real-time sync with Laravel

**How to keep it running:**
1. Set ZKBio Time.Net to start with Windows
2. Enable auto-download in ZKBio settings
3. Keep PC/server running 24/7

---

### Option 2: Scheduled Running

**If you can't keep it running 24/7:**

1. **Run ZKBio Time.Net during business hours**
2. **Enable auto-download** when it's running
3. **Laravel will sync** all records when ZKBio downloads them

**Limitation:**
- ⚠️ Only records attendance during business hours
- ⚠️ Misses attendance if ZKBio is closed

---

## Database Access Scenarios

### Scenario 1: SQLite Database (Same PC)

**ZKBio Time.Net running:**
- ✅ Downloads new records from device
- ✅ Updates `C:\ZKTeco\ZKBioTime\attendance.db`
- ✅ Laravel can read database

**ZKBio Time.Net closed:**
- ❌ No new records downloaded
- ✅ Laravel can still read existing records from database file
- ✅ Database file remains accessible

---

### Scenario 2: MySQL Database (Network)

**ZKBio Time.Net running:**
- ✅ Downloads new records from device
- ✅ Updates MySQL database
- ✅ Laravel can read database

**ZKBio Time.Net closed:**
- ❌ No new records downloaded
- ✅ Laravel can still read existing records from MySQL
- ✅ MySQL server must be running (but ZKBio doesn't need to be)

---

## Troubleshooting

### Issue: No New Records Syncing

**Check:**
1. ✅ Is ZKBio Time.Net running?
2. ✅ Is auto-download enabled in ZKBio?
3. ✅ Is device connected in ZKBio?
4. ✅ Are employees scanning fingerprints?

**Solution:**
- Start ZKBio Time.Net
- Enable auto-download (Settings → Device → Auto Download)
- Wait 5 minutes for download cycle
- Run sync: `php artisan attendance:sync-zkbiotime --all`

---

### Issue: Can't Read Database

**Check:**
1. ✅ Database file exists (for SQLite)
2. ✅ Database server running (for MySQL/MSSQL)
3. ✅ Network path accessible (for network SQLite)
4. ✅ Permissions correct

**Solution:**
- Verify database path in device settings
- Test database connection manually
- Check file/folder permissions
- Verify network connectivity

---

## Summary Table

| Scenario | ZKBio Running? | Database Access | New Records | Laravel Sync |
|----------|----------------|-----------------|-------------|--------------|
| **Normal Operation** | ✅ Yes | ✅ Yes | ✅ Yes | ✅ Yes |
| **ZKBio Closed** | ❌ No | ✅ Yes (existing) | ❌ No | ⚠️ Only old records |
| **Database Offline** | ✅ Yes | ❌ No | ✅ Yes (stored locally) | ❌ No |
| **Both Offline** | ❌ No | ❌ No | ❌ No | ❌ No |

---

## Best Practice Recommendation

**Keep ZKBio Time.Net running 24/7** on a dedicated Windows PC/server:

1. ✅ Set to start with Windows
2. ✅ Enable auto-download (every 5 minutes)
3. ✅ Keep PC/server always on
4. ✅ Monitor ZKBio logs for errors
5. ✅ Laravel will automatically sync every 5 minutes

This ensures:
- ✅ No missed attendance records
- ✅ Real-time data sync
- ✅ Reliable attendance tracking
- ✅ Automatic operation

---

## Quick Checklist

- [ ] ZKBio Time.Net installed and configured
- [ ] Device connected in ZKBio
- [ ] Auto-download enabled (every 5 minutes)
- [ ] ZKBio Time.Net set to start with Windows
- [ ] Database accessible from Laravel server
- [ ] Laravel scheduler running
- [ ] Employee IDs match between systems

---

**Last Updated**: 2025
**Version**: 1.0







