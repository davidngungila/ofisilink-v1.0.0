# OfisiLink Domain Structure

## Overview
OfisiLink uses a multi-domain architecture to separate the landing page from the application systems.

## Domain Configuration

### 1. Main Domain - Landing Page
**Domain:** `ofisilink.com`
**Purpose:** Marketing and information landing page
**Content:**
- Hero section with slider
- Feature showcases
- Workflow demonstrations
- Pricing information
- Testimonials
- FAQ section
- Contact information

**Access:** Public (no authentication required)

---

### 2. Live Subdomain - Production System
**Domain:** `live.ofisilink.com`
**Purpose:** Production environment for live users
**Redirect:** Automatically redirects to `/login` page
**Access:** Requires authentication

**Features:**
- Full OfisiLink application
- All modules and features
- Production data
- Real-time operations

---

### 3. Demo Subdomain - Demo System
**Domain:** `demo.ofisilink.com`
**Purpose:** Demonstration environment for potential clients
**Redirect:** Automatically redirects to `/login` page
**Access:** Requires authentication (demo credentials)

**Features:**
- Full OfisiLink application
- Sample data for demonstration
- All features available for testing
- Safe environment for exploration

---

## Routing Logic

The routing is handled in `routes/web.php`:

```php
// Landing page - only on root domain
Route::get('/', function () {
    $subdomain = getSubdomain();
    
    // If on subdomain, redirect to login page
    if ($subdomain === 'live') {
        return redirect()->route('login');
    }
    
    if ($subdomain === 'demo') {
        return redirect()->route('login');
    }
    
    // Show landing page only on root domain (ofisilink.com)
    return view('landing');
})->name('landing');
```

## DNS Configuration Required

For this setup to work, you need to configure DNS records:

1. **A Record for Main Domain:**
   - `ofisilink.com` → Server IP

2. **A Record for Live Subdomain:**
   - `live.ofisilink.com` → Server IP

3. **A Record for Demo Subdomain:**
   - `demo.ofisilink.com` → Server IP

## Web Server Configuration

Ensure your web server (Apache/Nginx) is configured to:
- Accept requests for all three domains
- Route them to the same Laravel application
- The application will handle the routing logic based on the subdomain

## Benefits

1. **Separation of Concerns:** Landing page separate from application
2. **Better SEO:** Main domain focused on marketing content
3. **Clear Access Points:** Users know exactly where to go
4. **Professional Structure:** Industry-standard domain setup
5. **Easy Management:** Each environment has its own domain

