# Download Required Assets

This directory should contain the following libraries downloaded from their official sources:

## Chart.js 4.4.0
Download from: https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js
Save as: `chart.js/chart.umd.min.js`

## SweetAlert2 v11
Download from: https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js
Save as: `sweetalert2/sweetalert2.min.js`

Download CSS from: https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css
Save as: `sweetalert2/sweetalert2.min.css`

## Instructions

1. Create the directories if they don't exist:
   - `chart.js/`
   - `sweetalert2/`

2. Download the files using curl or wget:
   ```bash
   # Chart.js
   curl -o chart.js/chart.umd.min.js https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js
   
   # SweetAlert2 JS
   curl -o sweetalert2/sweetalert2.min.js https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js
   
   # SweetAlert2 CSS
   curl -o sweetalert2/sweetalert2.min.css https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css
   ```

3. Verify the files exist before deploying.









