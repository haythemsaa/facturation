<?php

namespace App\Services\Compliance;

/**
 * Service de calcul de paie conforme aux normes tunisiennes
 * CNSS, IRPP, et autres cotisations sociales
 */
class PayrollCalculator
{
    /**
     * Taux CNSS - Cotisations salariales
     */
    public const CNSS_EMPLOYEE_RATE = 0.0918; // 9.18%

    /**
     * Taux CNSS - Cotisations patronales
     */
    public const CNSS_EMPLOYER_RATE = 0.1657; // 16.57%

    /**
     * Contribution Sociale de Solidarité
     */
    public const CSS_RATE = 0.01; // 1%

    /**
     * Taxe de Formation Professionnelle
     */
    public const TFP_RATE = 0.01; // 1%

    /**
     * FOPROLOS
     */
    public const FOPROLOS_RATE = 0.01; // 1%

    /**
     * Barème IRPP 2025 (impôt sur le revenu des personnes physiques)
     * Tranches de revenu annuel en TND
     */
    public const IRPP_BRACKETS = [
        ['min' => 0, 'max' => 5000, 'rate' => 0],
        ['min' => 5000.001, 'max' => 20000, 'rate' => 0.26],
        ['min' => 20000.001, 'max' => 30000, 'rate' => 0.28],
        ['min' => 30000.001, 'max' => 50000, 'rate' => 0.32],
        ['min' => 50000.001, 'max' => PHP_FLOAT_MAX, 'rate' => 0.35],
    ];

    /**
     * Déductions IRPP
     */
    public const IRPP_DEDUCTION_FAMILY_HEAD = 300; // Chef de famille
    public const IRPP_DEDUCTION_PER_CHILD = 100; // Par enfant à charge (max 4)
    public const MAX_CHILDREN_DEDUCTION = 4;

    /**
     * Calculer les cotisations CNSS salariales
     *
     * @param float $grossSalary Salaire brut
     * @return float Cotisation CNSS salarié
     */
    public function calculateCNSSEmployee(float $grossSalary): float
    {
        return round($grossSalary * self::CNSS_EMPLOYEE_RATE, 3);
    }

    /**
     * Calculer les cotisations CNSS patronales
     *
     * @param float $grossSalary Salaire brut
     * @return float Cotisation CNSS employeur
     */
    public function calculateCNSSEmployer(float $grossSalary): float
    {
        return round($grossSalary * self::CNSS_EMPLOYER_RATE, 3);
    }

    /**
     * Calculer la CSS (Contribution Sociale de Solidarité)
     *
     * @param float $grossSalary Salaire brut
     * @return float Montant CSS
     */
    public function calculateCSS(float $grossSalary): float
    {
        return round($grossSalary * self::CSS_RATE, 3);
    }

    /**
     * Calculer la TFP (Taxe de Formation Professionnelle)
     *
     * @param float $grossSalary Salaire brut
     * @return float Montant TFP
     */
    public function calculateTFP(float $grossSalary): float
    {
        return round($grossSalary * self::TFP_RATE, 3);
    }

    /**
     * Calculer le FOPROLOS
     *
     * @param float $grossSalary Salaire brut
     * @return float Montant FOPROLOS
     */
    public function calculateFOPROLOS(float $grossSalary): float
    {
        return round($grossSalary * self::FOPROLOS_RATE, 3);
    }

    /**
     * Calculer l'IRPP (Impôt sur le Revenu des Personnes Physiques)
     *
     * @param float $grossSalary Salaire brut mensuel
     * @param bool $isFamilyHead Chef de famille
     * @param int $childrenCount Nombre d'enfants à charge
     * @return array Détails du calcul IRPP
     */
    public function calculateIRPP(float $grossSalary, bool $isFamilyHead = false, int $childrenCount = 0): array
    {
        // Revenu annuel
        $annualIncome = $grossSalary * 12;

        // Déductions
        $deductions = 0;
        if ($isFamilyHead) {
            $deductions += self::IRPP_DEDUCTION_FAMILY_HEAD;
        }
        $childrenCount = min($childrenCount, self::MAX_CHILDREN_DEDUCTION);
        $deductions += $childrenCount * self::IRPP_DEDUCTION_PER_CHILD;

        // Revenu imposable annuel
        $taxableIncome = max(0, $annualIncome - $deductions);

        // Calcul de l'impôt par tranches
        $totalTax = 0;
        $taxByBracket = [];

        foreach (self::IRPP_BRACKETS as $bracket) {
            if ($taxableIncome > $bracket['min']) {
                $taxableInBracket = min($taxableIncome, $bracket['max']) - $bracket['min'];
                $taxInBracket = $taxableInBracket * $bracket['rate'];
                $totalTax += $taxInBracket;

                $taxByBracket[] = [
                    'bracket' => $bracket['min'] . ' - ' . $bracket['max'],
                    'rate' => $bracket['rate'] * 100 . '%',
                    'taxable' => round($taxableInBracket, 3),
                    'tax' => round($taxInBracket, 3),
                ];
            }
        }

        // IRPP mensuel (retenue à la source)
        $monthlyIRPP = $totalTax / 12;

        return [
            'annual_income' => round($annualIncome, 3),
            'deductions' => round($deductions, 3),
            'taxable_income' => round($taxableIncome, 3),
            'annual_tax' => round($totalTax, 3),
            'monthly_tax' => round($monthlyIRPP, 3),
            'tax_by_bracket' => $taxByBracket,
        ];
    }

    /**
     * Calculer un bulletin de paie complet
     *
     * @param array $data Données employé et paie
     * @return array Bulletin de paie détaillé
     */
    public function calculatePayslip(array $data): array
    {
        $baseSalary = $data['base_salary'] ?? 0;
        $earnings = $data['earnings'] ?? []; // Primes, indemnités
        $deductions = $data['deductions'] ?? []; // Autres déductions
        $isFamilyHead = $data['is_family_head'] ?? false;
        $childrenCount = $data['children_count'] ?? 0;
        $workedDays = $data['worked_days'] ?? 26; // Jours travaillés dans le mois
        $standardDays = 26; // Jours standard par mois

        // Calcul salaire brut
        $prorataSalary = ($baseSalary / $standardDays) * $workedDays;
        $totalEarnings = array_sum($earnings);
        $grossSalary = $prorataSalary + $totalEarnings;

        // Cotisations sociales
        $cnssEmployee = $this->calculateCNSSEmployee($grossSalary);
        $css = $this->calculateCSS($grossSalary);

        // IRPP
        $irppCalculation = $this->calculateIRPP($grossSalary, $isFamilyHead, $childrenCount);
        $irpp = $irppCalculation['monthly_tax'];

        // Total déductions
        $totalDeductions = $cnssEmployee + $css + $irpp + array_sum($deductions);

        // Salaire net
        $netSalary = $grossSalary - $totalDeductions;

        // Charges patronales
        $cnssEmployer = $this->calculateCNSSEmployer($grossSalary);
        $tfp = $this->calculateTFP($grossSalary);
        $foprolos = $this->calculateFOPROLOS($grossSalary);
        $totalEmployerCharges = $cnssEmployer + $tfp + $foprolos;

        // Coût total employeur
        $totalCost = $grossSalary + $totalEmployerCharges;

        return [
            'base_salary' => round($baseSalary, 3),
            'worked_days' => $workedDays,
            'prorata_salary' => round($prorataSalary, 3),
            'earnings' => $earnings,
            'total_earnings' => round($totalEarnings, 3),
            'gross_salary' => round($grossSalary, 3),
            'cnss_employee' => round($cnssEmployee, 3),
            'css' => round($css, 3),
            'irpp' => round($irpp, 3),
            'irpp_details' => $irppCalculation,
            'other_deductions' => $deductions,
            'total_deductions' => round($totalDeductions, 3),
            'net_salary' => round($netSalary, 3),
            'cnss_employer' => round($cnssEmployer, 3),
            'tfp' => round($tfp, 3),
            'foprolos' => round($foprolos, 3),
            'total_employer_charges' => round($totalEmployerCharges, 3),
            'total_cost' => round($totalCost, 3),
        ];
    }

    /**
     * Générer la déclaration CNSS mensuelle
     *
     * @param array $payslips Bulletins de paie du mois
     * @return array Déclaration CNSS
     */
    public function generateCNSSDeclaration(array $payslips): array
    {
        $totalSalaries = 0;
        $totalEmployeeContributions = 0;
        $totalEmployerContributions = 0;
        $employees = [];

        foreach ($payslips as $payslip) {
            $totalSalaries += $payslip['gross_salary'];
            $totalEmployeeContributions += $payslip['cnss_employee'];
            $totalEmployerContributions += $payslip['cnss_employer'];

            $employees[] = [
                'employee_id' => $payslip['employee_id'],
                'cnss_number' => $payslip['cnss_number'] ?? '',
                'name' => $payslip['employee_name'],
                'gross_salary' => round($payslip['gross_salary'], 3),
                'employee_contribution' => round($payslip['cnss_employee'], 3),
                'employer_contribution' => round($payslip['cnss_employer'], 3),
            ];
        }

        $totalAmount = $totalEmployeeContributions + $totalEmployerContributions;

        return [
            'total_salaries' => round($totalSalaries, 3),
            'total_employee_contributions' => round($totalEmployeeContributions, 3),
            'total_employer_contributions' => round($totalEmployerContributions, 3),
            'total_amount' => round($totalAmount, 3),
            'employees' => $employees,
            'employee_count' => count($employees),
        ];
    }
}
