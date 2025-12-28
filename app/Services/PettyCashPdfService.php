<?php

namespace App\Services;

use App\Models\PettyCashVoucher;
use Carbon\Carbon;

/**
 * Petty Cash PDF Generator Service
 * Generates advanced PDF documents for petty cash vouchers
 * Extends BasePdfService for reusable functionality
 */
class PettyCashPdfService extends BasePdfService
{
    public function __construct()
    {
        $this->documentType = 'PETTY CASH VOUCHER';
    }

    /**
     * Generate the complete HTML for petty cash PDF using Blade template
     */
    public function generateHtml($data, $logoSrc = null): string
    {
        // Type check and cast
        if (!$data instanceof PettyCashVoucher) {
            throw new \InvalidArgumentException('Expected PettyCashVoucher instance');
        }
        
        $pettyCash = $data;
        
        // Load relationships if not already loaded
        if (!$pettyCash->relationLoaded('lines')) {
            $pettyCash->load(['lines', 'creator', 'accountant', 'hod', 'ceo', 'paidBy']);
        }
        
        // Use Blade template for rendering
        return view('modules.finance.pdf.petty-cash', [
            'pettyCash' => $pettyCash
        ])->render();
    }
}
