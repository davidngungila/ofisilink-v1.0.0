<script type="text/php">
    if (isset($pdf)) {
        $orgSettings = \App\Models\OrganizationSetting::getSettings();
        $timezone = $orgSettings->timezone ?? config('app.timezone', 'Africa/Dar_es_Salaam');
        // Ensure timezone is a string, not an array
        if (is_array($timezone)) {
            $timezone = config('app.timezone', 'Africa/Dar_es_Salaam');
        } else {
            $timezone = (string)$timezone;
        }
        $generatedAt = \Carbon\Carbon::now()->setTimezone($timezone)->format('d M Y, H:i:s');
        
        $size = 8;
        $font = $fontMetrics->getFont("Helvetica");
        $pageHeight = $pdf->get_height();
        $pageWidth = $pdf->get_width();
        
        // Draw horizontal line in color #940000 (50px from bottom)
        $lineY = $pageHeight - 50;
        $lineX1 = 30; // Left margin
        $lineX2 = $pageWidth - 30; // Right margin
        
        // Draw line using rectangle (more reliable than text)
        $pdf->rectangle($lineX1, $lineY, $lineX2 - $lineX1, 0.5, array(0.58, 0, 0), array(0.58, 0, 0));
        
        // Footer text with page numbers - bottom line (32px from bottom)
        $generatedText = "Generated: " . $generatedAt;
        $poweredText = "OfisiLink - Powered by EmCa Technologies";
        $separator = " | ";
        
        // Page counter text - this will be replaced by DomPDF with actual page numbers
        $pageNumText = "Page {PAGE_NUM} of {PAGE_COUNT}";
        
        // Calculate widths for proper centering
        // Use a sample text to estimate width
        $samplePageText = "Page 999 of 999";
        $pageNumWidth = $fontMetrics->get_text_width($samplePageText, $font, $size);
        $generatedWidth = $fontMetrics->get_text_width($generatedText, $font, $size);
        $poweredWidth = $fontMetrics->get_text_width($poweredText, $font, $size);
        $separatorWidth = $fontMetrics->get_text_width($separator, $font, $size);
        
        // Calculate total width
        $totalWidth = $pageNumWidth + $separatorWidth + $generatedWidth + $separatorWidth + $poweredWidth;
        
        // Center the footer
        $footerX = ($pageWidth - $totalWidth) / 2;
        $footerY = $pageHeight - 32;
        
        // Render page number counter (DomPDF will replace {PAGE_NUM} and {PAGE_COUNT})
        $pdf->page_text($footerX, $footerY, $pageNumText, $font, $size, array(0.2, 0.2, 0.2));
        
        // Render separator and generated text
        $generatedX = $footerX + $pageNumWidth + $separatorWidth;
        $pdf->page_text($generatedX, $footerY, $separator . $generatedText, $font, $size, array(0.4, 0.4, 0.4));
        
        // Render separator and powered by text in primary color (#940000)
        $poweredX = $generatedX + $generatedWidth + $separatorWidth;
        $pdf->page_text($poweredX, $footerY, $separator, $font, $size, array(0.4, 0.4, 0.4));
        $pdf->page_text($poweredX + $separatorWidth, $footerY, $poweredText, $font, $size, array(0.58, 0, 0));
    }
</script>

