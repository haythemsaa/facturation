<?php

namespace App\Services\Compliance;

/**
 * Service de calcul des taxes tunisiennes
 * Conforme aux normes fiscales tunisiennes 2025
 */
class TaxCalculator
{
    /**
     * Taux de TVA tunisiens
     */
    public const TVA_RATES = [
        '19' => 0.19,  // Taux normal
        '13' => 0.13,  // Taux réduit
        '7' => 0.07,   // Taux super réduit
        '0' => 0.00,   // Exonéré
    ];

    /**
     * Timbre fiscal: 1% du montant TTC, plafonné à 1 TND
     */
    public const TIMBRE_FISCAL_RATE = 0.01;
    public const TIMBRE_FISCAL_MAX = 1.000;

    /**
     * Calculer le montant de TVA
     *
     * @param float $amountHT Montant hors taxe
     * @param string $tvaRate Taux de TVA ('19', '13', '7', '0')
     * @return float Montant de TVA
     */
    public function calculateTVA(float $amountHT, string $tvaRate): float
    {
        if (!isset(self::TVA_RATES[$tvaRate])) {
            throw new \InvalidArgumentException("Taux de TVA invalide: {$tvaRate}");
        }

        return round($amountHT * self::TVA_RATES[$tvaRate], 3);
    }

    /**
     * Calculer le timbre fiscal (1% du montant TTC, max 1 TND)
     *
     * @param float $amountTTC Montant toutes taxes comprises
     * @return float Montant du timbre fiscal
     */
    public function calculateTimbreFiscal(float $amountTTC): float
    {
        $timbre = $amountTTC * self::TIMBRE_FISCAL_RATE;
        return min(round($timbre, 3), self::TIMBRE_FISCAL_MAX);
    }

    /**
     * Calculer les montants d'un document commercial
     *
     * @param array $lines Lignes du document avec quantité, prix unitaire, taux TVA
     * @param float $discountRate Taux de remise global (%)
     * @param bool $includeTimbreFiscal Inclure le timbre fiscal
     * @return array Détails des calculs
     */
    public function calculateDocument(array $lines, float $discountRate = 0, bool $includeTimbreFiscal = true): array
    {
        $subtotal = 0;
        $totalTVA = 0;
        $tvaByRate = [];

        foreach ($lines as $line) {
            $quantity = $line['quantity'] ?? 1;
            $unitPrice = $line['unit_price'] ?? 0;
            $lineTvaRate = $line['tva_rate'] ?? '19';
            $lineDiscountRate = $line['discount_rate'] ?? 0;

            // Calcul ligne
            $lineAmount = $quantity * $unitPrice;
            $lineDiscountAmount = $lineAmount * ($lineDiscountRate / 100);
            $lineAmountHT = $lineAmount - $lineDiscountAmount;

            $lineTVA = $this->calculateTVA($lineAmountHT, $lineTvaRate);

            $subtotal += $lineAmountHT;
            $totalTVA += $lineTVA;

            // Regroupement par taux
            if (!isset($tvaByRate[$lineTvaRate])) {
                $tvaByRate[$lineTvaRate] = ['base' => 0, 'tva' => 0];
            }
            $tvaByRate[$lineTvaRate]['base'] += $lineAmountHT;
            $tvaByRate[$lineTvaRate]['tva'] += $lineTVA;
        }

        // Remise globale
        $discountAmount = $subtotal * ($discountRate / 100);
        $totalHT = $subtotal - $discountAmount;

        // Recalcul TVA si remise globale
        if ($discountAmount > 0) {
            $totalTVA = 0;
            foreach ($tvaByRate as $rate => &$data) {
                $data['base'] = $data['base'] * (1 - $discountRate / 100);
                $data['tva'] = $this->calculateTVA($data['base'], $rate);
                $totalTVA += $data['tva'];
            }
        }

        // Total TTC avant timbre
        $totalTTC = $totalHT + $totalTVA;

        // Timbre fiscal
        $timbreFiscal = $includeTimbreFiscal ? $this->calculateTimbreFiscal($totalTTC) : 0;

        // Total final
        $totalFinal = $totalTTC + $timbreFiscal;

        return [
            'subtotal' => round($subtotal, 3),
            'discount_amount' => round($discountAmount, 3),
            'total_ht' => round($totalHT, 3),
            'total_tva' => round($totalTVA, 3),
            'tva_by_rate' => $tvaByRate,
            'timbre_fiscal' => round($timbreFiscal, 3),
            'total_ttc' => round($totalFinal, 3),
        ];
    }

    /**
     * Vérifier la conformité d'une facture tunisienne
     *
     * @param array $invoice Données de la facture
     * @return array Résultat de la validation
     */
    public function validateInvoice(array $invoice): array
    {
        $errors = [];

        // Numéro séquentiel obligatoire
        if (empty($invoice['number'])) {
            $errors[] = "Numéro de facture obligatoire";
        }

        // Date obligatoire
        if (empty($invoice['date'])) {
            $errors[] = "Date de facture obligatoire";
        }

        // Client obligatoire
        if (empty($invoice['customer_id'])) {
            $errors[] = "Client obligatoire";
        }

        // Vérifier les montants
        if (isset($invoice['total_ttc']) && $invoice['total_ttc'] < 0) {
            $errors[] = "Montant TTC ne peut pas être négatif";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}
