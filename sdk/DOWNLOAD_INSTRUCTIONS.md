# ZKTeco SDK Download Instructions

## 📥 Quick Download

### Option 1: Use the Helper Script
1. Double-click: `download_zkteco_sdk.bat`
2. Browser will open to ZKTeco SDK page
3. Follow on-screen instructions

### Option 2: Manual Download

1. **Visit:** https://www.zkteco.com/en/SDK
2. **Navigate to:** Support → Download Center → SDK
3. **Login/Register** if required
4. **Download:** `ZKBioModuleSDK_20210113.zip` (15.54MB)
5. **Save to:** `ofisi/sdk/` directory
6. **Extract** the ZIP file to `ofisi/sdk/zkbiomodulesdk/`

---

## 📦 Recommended SDK

### ZKBioModuleSDK (For Device Communication)

- **File:** `ZKBioModuleSDK_20210113.zip`
- **Size:** 15.54MB
- **Upload Date:** 2022-04-20
- **Best For:** PHP/Laravel device integration
- **Contains:**
  - Device communication libraries (DLL/SO)
  - API documentation (PDF/HTML)
  - Sample code (C/C++/C#)
  - Protocol documentation
  - Integration guides

**Direct Link:** https://www.zkteco.com/en/SDK

---

## 🔍 Other Available SDKs

### ZKFinger SDK Windows (If needed for fingerprint processing)
- **File:** `ZKFinger SDK Windows.rar`
- **Size:** 34.18MB
- **Use Case:** Advanced fingerprint template operations
- **Compatible:** Windows XP, Vista, Win7, Win8/8.1, Win10, Windows Server

### ZKFinger SDK Linux
- **File:** `ZKFinger SDK Linux.zip`
- **Size:** 10.36MB
- **Use Case:** Linux server fingerprint processing

### ZKFingerSDK Android
- **File:** `ZKFingerSDK_Andoroid.rar`
- **Size:** 32.67MB
- **Use Case:** Android mobile applications

---

## ⚠️ Important Note

**Our current PHP implementation works WITHOUT the SDK!**

✅ **Already Working (No SDK Required):**
- Direct TCP/IP socket communication
- Device connection (4 different methods)
- User management (register, get users)
- Attendance sync
- Device information retrieval
- All basic operations

**SDK is only needed if:**
- You want reference documentation
- You need advanced fingerprint processing
- You want to verify protocol implementation
- You plan to use native libraries for performance

---

## 📋 After Download

1. **Extract SDK:**
   ```
   Extract ZKBioModuleSDK_20210113.zip to:
   ofisi/sdk/zkbiomodulesdk/
   ```

2. **Review Contents:**
   - Check for API documentation (PDF/HTML)
   - Review sample code (C/C++/C# examples)
   - Understand protocol details
   - Check integration guides

3. **Integration (Optional):**
   - Use documentation to improve our PHP implementation
   - Reference for protocol verification
   - Advanced features if needed
   - For PHP: May need FFI or C extension wrapper

---

## 🔗 Direct Links & Support

- **SDK Download Page:** https://www.zkteco.com/en/SDK
- **Technical Support:** service@zkteco.com
- **Sales Support:** sales@zkteco.com

---

## ✅ Current Status

**Composer:** ✅ Installed and up to date  
**SDK Directory:** ✅ Created at `ofisi/sdk/`  
**Helper Script:** ✅ Created (`download_zkteco_sdk.bat`)  
**Implementation:** ✅ Working without SDK (direct socket communication)  
**Connection Methods:** ✅ 4 different methods implemented  
**All Features:** ✅ Working (connection, users, attendance)

---

## 🎯 Next Steps

1. **Run the helper script:**
   ```bash
   # Windows
   ofisi\sdk\download_zkteco_sdk.bat
   ```

2. **Or visit manually:**
   - Go to: https://www.zkteco.com/en/SDK
   - Download: ZKBioModuleSDK_20210113.zip
   - Extract to: `ofisi/sdk/zkbiomodulesdk/`

3. **Review documentation** (if you want to verify/improve implementation)

---

**Note:** The SDK is optional. Our current implementation handles all standard device operations without it.
