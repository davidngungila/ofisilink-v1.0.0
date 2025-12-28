<?php

namespace App\Services;

use Carbon\Carbon;

/**
 * Base PDF Service
 * Provides reusable methods and structure for all PDF generation across the system
 */
abstract class BasePdfService
{
    protected $mainColor = '#940000';
    protected $secondaryColor = '#a80000';
    protected $companyName = 'OfisiLink Office Management System';
    protected $companyAddress = 'P.O. Box, Tanzania';
    protected $companyPhone = '+255 XXX XXX XXX';
    protected $companyEmail = 'info@ofisilink.com';
    protected $documentType = 'Document';
    protected $watermarkText = 'CONFIDENTIAL';

    /**
     * Set company information
     */
    public function setCompanyInfo($name, $address = null, $phone = null, $email = null)
    {
        $this->companyName = $name;
        if ($address) $this->companyAddress = $address;
        if ($phone) $this->companyPhone = $phone;
        if ($email) $this->companyEmail = $email;
        return $this;
    }

    /**
     * Set document type
     */
    public function setDocumentType($type)
    {
        $this->documentType = $type;
        return $this;
    }

    /**
     * Set colors
     */
    public function setColors($mainColor, $secondaryColor = null)
    {
        $this->mainColor = $mainColor;
        if ($secondaryColor) $this->secondaryColor = $secondaryColor;
        return $this;
    }

    /**
     * Convert number to words (Tanzanian Shillings)
     */
    protected function convertNumberToWords($number)
    {
        $number = (int)$number;
        if ($number === 0) return 'Zero';

        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine'];
        $teens = ['Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

        $words = '';

        // Handle millions
        if ($number >= 1000000) {
            $millions = (int)($number / 1000000);
            $words .= $this->convertNumberToWords($millions) . ' Million ';
            $number %= 1000000;
        }

        // Handle thousands
        if ($number >= 1000) {
            $thousands = (int)($number / 1000);
            $words .= $this->convertNumberToWords($thousands) . ' Thousand ';
            $number %= 1000;
        }

        // Handle hundreds
        if ($number >= 100) {
            $words .= $ones[(int)($number / 100)] . ' Hundred ';
            $number %= 100;
        }

        // Handle tens and ones
        if ($number >= 20) {
            $words .= $tens[(int)($number / 10)] . ' ';
            $number %= 10;
        } elseif ($number >= 10) {
            $words .= $teens[$number - 10] . ' ';
            $number = 0;
        }

        if ($number > 0) {
            $words .= $ones[$number] . ' ';
        }

        return trim($words);
    }

    /**
     * Format currency (Tanzanian Shillings)
     */
    protected function formatCurrency($amount, $currency = 'TZS')
    {
        return $currency . ' ' . number_format((float)$amount, 2, '.', ',');
    }

    /**
     * Format date
     */
    protected function formatDate($date, $format = 'l, F d, Y')
    {
        if (!$date) return 'N/A';
        if (is_string($date)) {
            $date = Carbon::parse($date);
        }
        return $date->format($format);
    }

    /**
     * Format datetime
     */
    protected function formatDateTime($datetime, $format = 'F j, Y \a\t g:i A')
    {
        if (!$datetime) return 'N/A';
        if (is_string($datetime)) {
            $datetime = Carbon::parse($datetime);
        }
        return $datetime->format($format);
    }

    /**
     * Get status badge class
     */
    protected function getStatusBadgeClass($status)
    {
        $status = strtolower($status);
        if (str_contains($status, 'pending')) return 'pending';
        if (str_contains($status, 'approved') || str_contains($status, 'paid')) return 'approved';
        if (str_contains($status, 'retired') || str_contains($status, 'completed')) return 'completed';
        if (str_contains($status, 'rejected')) return 'rejected';
        return 'default';
    }

    /**
     * Get status text
     */
    protected function getStatusText($status)
    {
        return ucwords(str_replace(['_', '-'], ' ', $status));
    }

    /**
     * Escape HTML
     */
    protected function escape($text)
    {
        return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
    }

    /**
     * Get base CSS styles
     */
    protected function getBaseStyles()
    {
        return '
        @page { 
            margin: 15mm 20mm;
            size: A4 portrait;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body { 
            font-family: "DejaVu Sans", "Helvetica", Arial, sans-serif;
            color: #2c3e50;
            font-size: 10pt;
            line-height: 1.6;
            background: #ffffff;
        }
        
        /* Header Styles */
        .pdf-header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 4px double ' . $this->mainColor . ';
            position: relative;
        }
        
        .pdf-logo {
            max-width: 140px;
            height: auto;
            margin-bottom: 15px;
        }
        
        .pdf-company-name {
            color: ' . $this->mainColor . ';
            margin: 10px 0 8px 0;
            font-size: 24pt;
            font-weight: bold;
            letter-spacing: 1px;
        }
        
        .pdf-document-type {
            margin: 8px 0;
            font-size: 18pt;
            color: #34495e;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .pdf-company-info {
            margin-top: 10px;
            font-size: 9pt;
            color: #7f8c8d;
            line-height: 1.8;
        }
        
        /* Document Header Bar */
        .pdf-doc-header {
            background: linear-gradient(135deg, ' . $this->mainColor . ' 0%, ' . $this->secondaryColor . ' 100%);
            color: white;
            padding: 15px 20px;
            text-align: center;
            font-weight: bold;
            margin: 20px 0;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            font-size: 11pt;
        }
        
        /* Section Styles */
        .pdf-section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        
        .pdf-section-title {
            background: linear-gradient(90deg, ' . $this->mainColor . ' 0%, ' . $this->secondaryColor . ' 100%);
            color: white;
            padding: 10px 15px;
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 12px;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Table Styles */
        .pdf-table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0;
            font-size: 9.5pt;
        }
        
        .pdf-table th {
            background: linear-gradient(135deg, ' . $this->mainColor . ' 0%, ' . $this->secondaryColor . ' 100%);
            color: white;
            padding: 10px 12px;
            text-align: left;
            font-weight: bold;
            border: 1px solid ' . $this->mainColor . ';
        }
        
        .pdf-table td {
            padding: 10px 12px;
            border: 1px solid #ddd;
            vertical-align: top;
        }
        
        .pdf-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .pdf-table tbody tr:hover {
            background-color: #e8f4f8;
        }
        
        .pdf-table .text-center {
            text-align: center;
        }
        
        .pdf-table .text-right {
            text-align: right;
        }
        
        .pdf-table .amount {
            text-align: right;
            font-family: "Courier New", monospace;
            font-weight: 500;
        }
        
        /* Info Table */
        .pdf-info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        
        .pdf-info-table th {
            background-color: #f8f9fa;
            padding: 10px 15px;
            text-align: left;
            font-weight: bold;
            width: 30%;
            border: 1px solid #dee2e6;
            color: ' . $this->mainColor . ';
        }
        
        .pdf-info-table td {
            padding: 10px 15px;
            border: 1px solid #dee2e6;
            background-color: #ffffff;
        }
        
        /* Highlight Box */
        .pdf-highlight-box {
            background: linear-gradient(135deg, ' . $this->mainColor . ' 0%, ' . $this->secondaryColor . ' 100%);
            color: white;
            padding: 25px;
            text-align: center;
            border-radius: 8px;
            margin: 25px 0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .pdf-highlight-value {
            font-size: 28pt;
            font-weight: bold;
            margin: 15px 0;
            font-family: "Courier New", monospace;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        
        .pdf-highlight-label {
            font-size: 11pt;
            opacity: 0.95;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .pdf-highlight-subtext {
            font-style: italic;
            margin-top: 12px;
            font-size: 9pt;
            opacity: 0.9;
        }
        
        /* Status Badge */
        .pdf-status-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 9pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .pdf-status-badge.pending {
            background-color: #ffc107;
            color: #000;
        }
        
        .pdf-status-badge.approved {
            background-color: #28a745;
            color: white;
        }
        
        .pdf-status-badge.completed {
            background-color: #6c757d;
            color: white;
        }
        
        .pdf-status-badge.rejected {
            background-color: #dc3545;
            color: white;
        }
        
        /* Comment Box */
        .pdf-comment-box {
            background-color: #f8f9fa;
            padding: 12px 15px;
            border-left: 4px solid ' . $this->mainColor . ';
            margin: 12px 0;
            border-radius: 4px;
        }
        
        .pdf-comment-label {
            font-weight: bold;
            color: ' . $this->mainColor . ';
            margin-bottom: 6px;
            font-size: 9.5pt;
        }
        
        .pdf-comment-text {
            color: #495057;
            line-height: 1.6;
        }
        
        /* Summary Box */
        .pdf-summary-box {
            background-color: #f8f9fa;
            padding: 18px;
            border-radius: 6px;
            margin: 20px 0;
            border: 1px solid #dee2e6;
        }
        
        .pdf-summary-row {
            display: table;
            width: 100%;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 1px dashed #ced4da;
        }
        
        .pdf-summary-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .pdf-summary-label {
            display: table-cell;
            font-weight: 600;
            color: #495057;
            width: 60%;
        }
        
        .pdf-summary-value {
            display: table-cell;
            text-align: right;
            font-family: "Courier New", monospace;
            color: ' . $this->mainColor . ';
            font-weight: bold;
        }
        
        /* Workflow Timeline */
        .pdf-workflow-table {
            width: 100%;
            border-collapse: collapse;
            margin: 18px 0;
        }
        
        .pdf-workflow-table th {
            background: linear-gradient(135deg, ' . $this->mainColor . ' 0%, ' . $this->secondaryColor . ' 100%);
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: bold;
        }
        
        .pdf-workflow-table td {
            padding: 12px;
            border: 1px solid #dee2e6;
        }
        
        .pdf-workflow-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .pdf-status-icon {
            text-align: center;
            font-weight: bold;
            font-size: 14pt;
            width: 40px;
        }
        
        .pdf-status-icon.completed {
            color: #28a745;
        }
        
        .pdf-status-icon.pending {
            color: #ffc107;
        }
        
        .pdf-status-icon.pending::before {
            content: "⏳";
        }
        
        .pdf-status-icon.completed::before {
            content: "✓";
        }
        
        /* Signature Area */
        .pdf-signature-area {
            margin-top: 50px;
            page-break-inside: avoid;
        }
        
        .pdf-signature-box {
            display: inline-block;
            width: 45%;
            text-align: center;
            margin: 0 2%;
            vertical-align: top;
        }
        
        .pdf-signature-line {
            border-top: 2px solid #2c3e50;
            width: 200px;
            margin: 0 auto;
            padding-top: 8px;
        }
        
        .pdf-signature-label {
            margin-top: 8px;
            font-weight: bold;
            color: ' . $this->mainColor . ';
        }
        
        .pdf-signature-date {
            font-size: 8pt;
            margin-top: 5px;
            color: #6c757d;
        }
        
        /* Footer */
        .pdf-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e9ecef;
            text-align: center;
            font-size: 8pt;
            color: #6c757d;
            page-break-inside: avoid;
        }
        
        .pdf-footer-title {
            font-weight: bold;
            color: ' . $this->mainColor . ';
            margin-bottom: 8px;
        }
        
        /* Utility Classes */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .text-bold { font-weight: bold; }
        .text-italic { font-style: italic; }
        .mb-1 { margin-bottom: 8px; }
        .mb-2 { margin-bottom: 12px; }
        .mb-3 { margin-bottom: 18px; }
        .mt-1 { margin-top: 8px; }
        .mt-2 { margin-top: 12px; }
        .mt-3 { margin-top: 18px; }
        
        /* Watermark */
        .pdf-watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 72pt;
            color: rgba(148, 0, 0, 0.05);
            z-index: -1;
            font-weight: bold;
            pointer-events: none;
        }
        ';
    }

    /**
     * Generate header HTML
     */
    protected function generateHeader($logoSrc = null, $documentTitle = null)
    {
        $logoHtml = '';
        if ($logoSrc) {
            $logoHtml = '<img src="' . $logoSrc . '" alt="Company Logo" class="pdf-logo">';
        }

        $title = $documentTitle ?: $this->documentType;

        return '
        <div class="pdf-header">
            ' . $logoHtml . '
            <div class="pdf-company-name">' . $this->escape($this->companyName) . '</div>
            <div class="pdf-document-type">' . $this->escape($title) . '</div>
            <div class="pdf-company-info">
                <div>' . $this->escape($this->companyAddress) . '</div>
                <div>Phone: ' . $this->escape($this->companyPhone) . ' | Email: ' . $this->escape($this->companyEmail) . '</div>
            </div>
        </div>';
    }

    /**
     * Generate footer HTML
     */
    protected function generateFooter($additionalInfo = [])
    {
        $generationDate = Carbon::now()->format('F j, Y \a\t g:i A');
        
        $additionalHtml = '';
        foreach ($additionalInfo as $key => $value) {
            $additionalHtml .= '<p>' . $this->escape($key) . ': ' . $this->escape($value) . '</p>';
        }

        return '
        <div class="pdf-footer">
            <div class="pdf-footer-title">' . $this->watermarkText . ' DOCUMENT</div>
            <p>Generated on: ' . $generationDate . ' | ' . $this->escape($this->companyName) . '</p>
            <p>This is a computer-generated document and does not require a physical signature.</p>
            ' . $additionalHtml . '
        </div>';
    }

    /**
     * Abstract method - must be implemented by child classes
     */
    abstract public function generateHtml($data, $logoSrc = null): string;
}



