<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class TanzaniaStatutoryCalculator
{
    // Tanzania statutory rates (as of 2024)
    const NSSF_RATE = 0.10; // 10% (5% employee + 5% employer)
    const NHIF_RATE = 0.03; // 3%
    const HESLB_RATE = 0.05; // 5% for student loan holders
    const WCF_RATE = 0.01; // 1% (Workers Compensation Fund)
    const SDL_RATE = 0.035; // 3.5% (Skills Development Levy)

    public function calculateNetSalary($basicSalary, $overtimeAmount = 0, $bonusAmount = 0, $allowanceAmount = 0, $employeeId = null, $additionalDeductions = 0)
    {
        $grossSalary = $basicSalary + $overtimeAmount + $bonusAmount + $allowanceAmount;

        // Calculate statutory deductions
        $nssf = $this->calculateNSSF($grossSalary);
        $nhif = $this->calculateNHIF($grossSalary);
        $heslb = $this->calculateHESLB($grossSalary, $employeeId);
        $paye = $this->calculatePAYE($grossSalary);
        $wcf = $this->calculateWCF($grossSalary);
        $sdl = $this->calculateSDL($grossSalary);

        // Calculate other deductions
        $otherDeductions = $additionalDeductions;

        // Calculate total deductions
        $totalDeductions = $nssf['employee'] + $nhif + $heslb + $paye + $wcf + $sdl + $otherDeductions;

        // Calculate net salary
        $netSalary = $grossSalary - $totalDeductions;

        // Calculate employer contributions
        $employerContributions = $grossSalary + $nssf['employer'] + $wcf + $sdl;

        return [
            'gross_salary' => $grossSalary,
            'nssf' => $nssf,
            'nhif' => $nhif,
            'heslb' => $heslb,
            'paye' => $paye,
            'wcf' => $wcf,
            'sdl' => $sdl,
            'other_deductions' => $otherDeductions,
            'total_deductions' => $totalDeductions,
            'net_salary' => $netSalary,
            'employer_contributions' => $employerContributions,
        ];
    }

    public function calculateNSSF($grossSalary)
    {
        // NSSF calculation with ceiling
        $nssfCeiling = 2000000; // TZS 2,000,000 ceiling
        $contributableSalary = min($grossSalary, $nssfCeiling);
        
        $employeeContribution = $contributableSalary * (self::NSSF_RATE / 2); // 5% employee
        $employerContribution = $contributableSalary * (self::NSSF_RATE / 2); // 5% employer

        return [
            'employee' => round($employeeContribution),
            'employer' => round($employerContribution)
        ];
    }

    public function calculateNHIF($grossSalary)
    {
        // NHIF calculation with ceiling
        $nhifCeiling = 1000000; // TZS 1,000,000 ceiling
        $contributableSalary = min($grossSalary, $nhifCeiling);
        
        return round($contributableSalary * self::NHIF_RATE);
    }

    public function calculateHESLB($grossSalary, $employeeId = null)
    {
        if (!$employeeId) {
            return 0;
        }

        // Check if employee has student loan
        $hasStudentLoan = DB::table('employees')
            ->where('user_id', $employeeId)
            ->value('has_student_loan');

        if (!$hasStudentLoan) {
            return 0;
        }

        // HESLB calculation with ceiling
        $heslbCeiling = 5000000; // TZS 5,000,000 ceiling
        $contributableSalary = min($grossSalary, $heslbCeiling);
        
        return round($contributableSalary * self::HESLB_RATE);
    }

    public function calculatePAYE($grossSalary)
    {
        // Tanzania PAYE tax brackets (2024)
        $taxBrackets = [
            ['min' => 0, 'max' => 270000, 'rate' => 0],
            ['min' => 270000, 'max' => 520000, 'rate' => 0.08],
            ['min' => 520000, 'max' => 760000, 'rate' => 0.20],
            ['min' => 760000, 'max' => 1000000, 'rate' => 0.25],
            ['min' => 1000000, 'max' => PHP_FLOAT_MAX, 'rate' => 0.30],
        ];

        $totalTax = 0;
        $remainingSalary = $grossSalary;

        foreach ($taxBrackets as $bracket) {
            if ($remainingSalary <= 0) break;

            $taxableAmount = min($remainingSalary, $bracket['max'] - $bracket['min']);
            if ($taxableAmount > 0) {
                $totalTax += $taxableAmount * $bracket['rate'];
                $remainingSalary -= $taxableAmount;
            }
        }

        return round($totalTax);
    }

    public function calculateWCF($grossSalary)
    {
        // Workers Compensation Fund - 1% of gross salary
        return round($grossSalary * self::WCF_RATE);
    }

    public function calculateSDL($grossSalary)
    {
        // Skills Development Levy - 3.5% of gross salary
        return round($grossSalary * self::SDL_RATE);
    }

    public function getDetailedBreakdown($basicSalary, $overtimeAmount = 0, $bonusAmount = 0, $allowanceAmount = 0, $employeeId = null, $additionalDeductions = 0)
    {
        return $this->calculateNetSalary($basicSalary, $overtimeAmount, $bonusAmount, $allowanceAmount, $employeeId, $additionalDeductions);
    }

    public function getStatutoryRates()
    {
        return [
            'nssf_rate' => self::NSSF_RATE,
            'nhif_rate' => self::NHIF_RATE,
            'heslb_rate' => self::HESLB_RATE,
            'wcf_rate' => self::WCF_RATE,
            'sdl_rate' => self::SDL_RATE,
            'paye_brackets' => [
                ['min' => 0, 'max' => 270000, 'rate' => 0],
                ['min' => 270000, 'max' => 520000, 'rate' => 0.08],
                ['min' => 520000, 'max' => 760000, 'rate' => 0.20],
                ['min' => 760000, 'max' => 1000000, 'rate' => 0.25],
                ['min' => 1000000, 'max' => PHP_FLOAT_MAX, 'rate' => 0.30],
            ]
        ];
    }
}
