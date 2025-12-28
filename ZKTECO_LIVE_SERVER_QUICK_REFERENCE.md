# ZKTeco Live Server - Quick Reference Card

## 🚀 Your Server Details

| Item | Value |
|------|-------|
| **Domain** | `ofisilink.com` |
| **Live URL** | `https://live.ofisilink.com/` |
| **Server IP** | `67.227.213.152` |
| **Home Directory** | `/home/ofisilink` |
| **Ping Endpoint** | `https://live.ofisilink.com/iclock/getrequest` |
| **Data Endpoint** | `https://live.ofisilink.com/iclock/cdata` |

---

## ⚡ Quick Setup (3 Steps)

### 1. Update .env File
```env
ZKTECO_PUSH_SDK_ENABLED=true
ZKTECO_SERVER_IP=live.ofisilink.com
ZKTECO_SERVER_PORT=443
```

### 2. Configure Device
- **Server IP**: `live.ofisilink.com`
- **Server Port**: `443`
- **Server Path**: `/iclock/getrequest`
- **Protocol**: HTTPS

### 3. Test Connection
```bash
curl "https://live.ofisilink.com/iclock/getrequest?SN=TEST123"
# Should return: OK
```

---

## 🔧 cPanel Tasks

1. **Enable PHP Sockets**: Software → MultiPHP INI Editor → Enable `extension=sockets`
2. **Install SSL**: SSL/TLS Status → Install Let's Encrypt
3. **Clear Cache**: Terminal → `php artisan config:clear`

---

## ✅ Test Checklist

- [ ] SSL certificate installed
- [ ] PHP sockets enabled
- [ ] .env updated
- [ ] Device configured
- [ ] Ping endpoint returns `OK`
- [ ] Device shows online

---

## 🐛 Quick Troubleshooting

| Issue | Solution |
|-------|----------|
| Device can't connect | Check HTTPS port 443, verify SSL |
| CSRF error | Clear cache: `php artisan config:clear` |
| No attendance sync | Check device Push SDK enabled, verify enroll_id |

---

## 📞 Endpoints

- **Ping**: `GET https://live.ofisilink.com/iclock/getrequest?SN=DEVICE_SERIAL`
- **Data**: `POST https://live.ofisilink.com/iclock/cdata?SN=DEVICE_SERIAL&table=ATTLOG&c=log`

---

**For detailed instructions, see**: `ZKTECO_LIVE_SERVER_CONFIGURATION.md`




