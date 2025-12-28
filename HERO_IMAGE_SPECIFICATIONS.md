# Hero Carousel Image Specifications

## Overview
The hero section carousel uses 6 slides, each requiring a high-quality image. This document specifies the exact requirements for each image.

## Image Requirements

### General Specifications (All Images)
- **Recommended Size:** 1200x800px (3:2 aspect ratio) OR 1920x1080px (16:9 aspect ratio)
- **Format:** PNG, JPG, or WebP
- **File Size:** Maximum 500KB (optimized for web)
- **Background:** Transparent or gradient overlay preferred
- **Quality:** High resolution, professional photography or illustrations
- **Color Scheme:** Should complement the gradient overlay (see individual slides)

### Image Storage Location
All hero images should be placed in:
```
public/assets/img/hero/
```

### Individual Slide Specifications

#### Slide 1: Main Overview
- **File Name:** `ofisilink-hero-1.jpg` (or .png, .webp)
- **Theme:** Office management, dashboard, modern workspace
- **Content:** Professional office environment, people working with technology
- **Gradient Overlay:** Red gradient (#940000 to #a80000)
- **Fallback:** Uses existing `man-with-laptop-light.png` if image not found

#### Slide 2: File Management
- **File Name:** `file-management-hero.jpg`
- **Theme:** File organization, document management, digital/physical files
- **Content:** Organized files, filing cabinets, digital storage
- **Gradient Overlay:** Blue gradient (#1e3c72 to #2a5298)
- **Fallback:** Icon (bx-folder) if image not found

#### Slide 3: HR Management
- **File Name:** `hr-management-hero.jpg`
- **Theme:** Human resources, team collaboration, employee management
- **Content:** Team working together, HR processes, employee engagement
- **Gradient Overlay:** Purple gradient (#667eea to #764ba2)
- **Fallback:** Icon (bx-briefcase) if image not found

#### Slide 4: Task Management
- **File Name:** `task-management-hero.jpg`
- **Theme:** Project management, task tracking, productivity
- **Content:** Task boards, project planning, team collaboration
- **Gradient Overlay:** Pink gradient (#f093fb to #f5576c)
- **Fallback:** Icon (bx-clipboard) if image not found

#### Slide 5: Analytics & Reporting
- **File Name:** `analytics-hero.jpg`
- **Theme:** Data visualization, charts, business intelligence
- **Content:** Dashboards, charts, graphs, data analysis
- **Gradient Overlay:** Cyan gradient (#4facfe to #00f2fe)
- **Fallback:** Icon (bx-bar-chart) if image not found

#### Slide 6: Security & Compliance
- **File Name:** `security-hero.jpg`
- **Theme:** Security, data protection, compliance
- **Content:** Security shields, data protection, secure systems
- **Gradient Overlay:** Green gradient (#43e97b to #38f9d7)
- **Fallback:** Icon (bx-shield-check) if image not found

## Image Optimization Tips

1. **Compression:** Use tools like TinyPNG, ImageOptim, or Squoosh to compress images
2. **Format Selection:**
   - Use WebP for best compression (with JPG fallback)
   - Use PNG for images with transparency
   - Use JPG for photographs
3. **Responsive Images:** Consider providing multiple sizes for different screen resolutions
4. **Alt Text:** All images have descriptive alt text for accessibility

## Current Implementation

The carousel is configured to:
- Display images at max-height: 600px
- Use object-fit: cover for proper scaling
- Apply border-radius: 15px for rounded corners
- Include box-shadow for depth
- Fallback to icons if images are not found

## Testing Checklist

- [ ] All 6 images are placed in `public/assets/img/hero/`
- [ ] Images are optimized (under 500KB each)
- [ ] Images display correctly on desktop (1920px width)
- [ ] Images display correctly on tablet (768px width)
- [ ] Images display correctly on mobile (375px width)
- [ ] Fallback icons display if images are missing
- [ ] Images load quickly (under 2 seconds)
- [ ] Images maintain aspect ratio
- [ ] Images work with gradient overlays

