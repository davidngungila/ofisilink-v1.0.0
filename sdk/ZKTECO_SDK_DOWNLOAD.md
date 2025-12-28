# ZKTeco SDK Download Guide

## Official SDK Downloads

Based on the [ZKTeco SDK page](https://www.zkteco.com/en/SDK), the following SDKs are available:

### Available SDKs:

1. **ZKBioModuleSDK** (Recommended for Device Communication)
   - File: `ZKBioModuleSDK_20210113.zip`
   - Size: 15.54MB
   - Upload Date: 2022-04-20
   - **Use Case:** Device communication, user management, attendance sync
   - **Best for:** PHP/Laravel integration with ZKTeco devices

2. **ZKFinger SDK Windows**
   - File: `ZKFinger SDK Windows.rar`
   - Size: 34.18MB
   - Upload Date: 2022-07-25
   - **Use Case:** Fingerprint template processing, enrollment
   - **Best for:** Windows applications needing fingerprint processing

3. **ZKFinger SDK Linux**
   - File: `ZKFinger SDK Linux.zip`
   - Size: 10.36MB
   - Upload Date: 2022-07-25
   - **Use Case:** Fingerprint template processing on Linux
   - **Best for:** Linux server applications

4. **ZKFingerSDK Android**
   - File: `ZKFingerSDK_Andoroid.rar`
   - Size: 32.67MB
   - Upload Date: 2022-07-25
   - **Use Case:** Android mobile applications
   - **Best for:** Mobile app development

5. **Visible Light Recognition SDK**
   - File: `Visible Light Recognition SDK Introduction.pdf`
   - Size: 7.07MB
   - Upload Date: 2019-04-26
   - **Use Case:** Documentation for visible light recognition
   - **Best for:** Reference documentation

---

## Download Instructions

### Option 1: Direct Download from ZKTeco Website

1. **Visit:** https://www.zkteco.com/en/SDK
2. **Navigate to:** Support → Download Center → SDK
3. **Download:** `ZKBioModuleSDK_20210113.zip` (for device communication)
4. **Extract** to `ofisi/sdk/zkbiomodulesdk/` directory

### Option 2: Manual Download Steps

1. Go to: https://www.zkteco.com/en/SDK
2. Click on "ZKBioModuleSDK_20210113" download link
3. Save the file to: `ofisi/sdk/`
4. Extract the ZIP file
5. Review the documentation included

---

## What's Included in ZKBioModuleSDK

The ZKBioModuleSDK typically includes:

- **Device Communication Library** (DLL/SO files)
- **API Documentation** (PDF/HTML)
- **Sample Code** (C/C++/C# examples)
- **Protocol Documentation**
- **Integration Guides**

---

## For PHP/Laravel Integration

**Note:** Our current implementation uses **direct TCP/IP socket communication** which doesn't require the SDK. However, the SDK can be useful for:

1. **Reference Documentation:** Understanding the protocol better
2. **Sample Code:** Seeing how other languages implement it
3. **Advanced Features:** If you need features not in our current implementation

### Current Implementation Status

✅ **Already Implemented (No SDK Required):**
- Direct TCP/IP connection
- Device authentication
- User management (register, get users)
- Attendance sync
- Device information retrieval
- Multiple connection methods (4 different approaches)

❌ **Would Require SDK (If Needed):**
- Fingerprint template processing
- Advanced biometric operations
- Native library integration

---

## Download Links

### ZKBioModuleSDK (Recommended)
- **Direct Link:** https://www.zkteco.com/en/SDK
- **File:** ZKBioModuleSDK_20210113.zip
- **Size:** 15.54MB

### ZKFinger SDK Windows (If needed for fingerprint processing)
- **Direct Link:** https://www.zkteco.com/en/SDK
- **File:** ZKFinger SDK Windows.rar
- **Size:** 34.18MB

---

## After Download

1. **Extract SDK:**
   ```bash
   # Windows
   Extract to: ofisi/sdk/zkbiomodulesdk/
   
   # Or use 7-Zip/WinRAR
   ```

2. **Review Documentation:**
   - Check for API documentation
   - Review sample code
   - Understand protocol details

3. **Integration (If Needed):**
   - For PHP: May need to use FFI or create wrapper
   - For advanced features: Consider C extension
   - For reference: Use documentation to improve our implementation

---

## Current Status

**Our PHP Implementation:**
- ✅ Works without SDK (direct socket communication)
- ✅ Implements ZKTeco protocol directly
- ✅ No external dependencies required
- ✅ Cross-platform compatible

**SDK Would Be Useful For:**
- Advanced fingerprint template operations
- Native library performance
- Reference documentation
- Protocol verification

---

## Recommendation

**For your current use case (device connection and attendance sync):**

✅ **You DON'T need the SDK** - Our current implementation handles:
- Device connection
- User management
- Attendance sync
- All basic operations

**Download SDK only if:**
- You need advanced fingerprint processing
- You want to verify protocol implementation
- You need reference documentation
- You plan to use native libraries for performance

---

**Download Page:** https://www.zkteco.com/en/SDK

**Support Email:** service@zkteco.com

**Sales Email:** sales@zkteco.com









