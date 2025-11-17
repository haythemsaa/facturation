<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Contact;
use App\Models\Document;
use App\Models\Employee;
use App\Models\Opportunity;
use App\Models\Product;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ExcelService
{
    /**
     * Export sales report to Excel.
     */
    public function exportSalesReport(string $startDate, string $endDate)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rapport Ventes');

        // Header styling
        $this->styleHeader($sheet, ['A1', 'B1', 'C1', 'D1', 'E1', 'F1', 'G1']);

        // Headers
        $sheet->setCellValue('A1', 'Date');
        $sheet->setCellValue('B1', 'N° Facture');
        $sheet->setCellValue('C1', 'Client');
        $sheet->setCellValue('D1', 'Total HT');
        $sheet->setCellValue('E1', 'TVA');
        $sheet->setCellValue('F1', 'Total TTC');
        $sheet->setCellValue('G1', 'Statut Paiement');

        // Data
        $invoices = Document::invoices()
            ->validated()
            ->dateBetween($startDate, $endDate)
            ->with('customer')
            ->get();

        $row = 2;
        $totalHT = 0;
        $totalTVA = 0;
        $totalTTC = 0;

        foreach ($invoices as $invoice) {
            $sheet->setCellValue('A' . $row, $invoice->date?->format('d/m/Y'));
            $sheet->setCellValue('B' . $row, $invoice->number);
            $sheet->setCellValue('C' . $row, $invoice->customer?->name ?? 'N/A');
            $sheet->setCellValue('D' . $row, (float) $invoice->total_ht);
            $sheet->setCellValue('E' . $row, (float) $invoice->total_tva);
            $sheet->setCellValue('F' . $row, (float) $invoice->total_ttc);
            $sheet->setCellValue('G' . $row, $this->getPaymentStatusLabel($invoice->payment_status));

            $totalHT += $invoice->total_ht;
            $totalTVA += $invoice->total_tva;
            $totalTTC += $invoice->total_ttc;

            $row++;
        }

        // Totals row
        $sheet->setCellValue('C' . $row, 'TOTAL');
        $sheet->setCellValue('D' . $row, $totalHT);
        $sheet->setCellValue('E' . $row, $totalTVA);
        $sheet->setCellValue('F' . $row, $totalTTC);

        $this->styleTotalRow($sheet, $row, ['C', 'D', 'E', 'F']);

        // Auto-size columns
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Number formatting
        $sheet->getStyle('D2:F' . $row)->getNumberFormat()
            ->setFormatCode('#,##0.000');

        return $this->downloadSpreadsheet($spreadsheet, "Rapport_Ventes_{$startDate}_{$endDate}.xlsx");
    }

    /**
     * Export stock report to Excel.
     */
    public function exportStockReport()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rapport Stock');

        // Headers
        $this->styleHeader($sheet, ['A1', 'B1', 'C1', 'D1', 'E1', 'F1', 'G1']);

        $sheet->setCellValue('A1', 'Code');
        $sheet->setCellValue('B1', 'Produit');
        $sheet->setCellValue('C1', 'Quantité');
        $sheet->setCellValue('D1', 'Prix Achat');
        $sheet->setCellValue('E1', 'Prix Vente');
        $sheet->setCellValue('F1', 'Valeur Stock');
        $sheet->setCellValue('G1', 'Statut');

        // Data
        $products = Product::with('stocks')->active()->get();

        $row = 2;
        $totalValue = 0;

        foreach ($products as $product) {
            $quantity = $product->stocks->sum('quantity');
            $stockValue = $quantity * $product->purchase_price;
            $status = $quantity <= $product->stock_alert_threshold ? 'STOCK FAIBLE' : 'OK';

            $sheet->setCellValue('A' . $row, $product->code);
            $sheet->setCellValue('B' . $row, $product->name);
            $sheet->setCellValue('C' . $row, $quantity);
            $sheet->setCellValue('D' . $row, (float) $product->purchase_price);
            $sheet->setCellValue('E' . $row, (float) $product->selling_price);
            $sheet->setCellValue('F' . $row, $stockValue);
            $sheet->setCellValue('G' . $row, $status);

            // Highlight low stock
            if ($quantity <= $product->stock_alert_threshold) {
                $sheet->getStyle('G' . $row)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFE5E5'],
                    ],
                    'font' => [
                        'color' => ['rgb' => 'FF0000'],
                        'bold' => true,
                    ],
                ]);
            }

            $totalValue += $stockValue;
            $row++;
        }

        // Total
        $sheet->setCellValue('E' . $row, 'TOTAL STOCK:');
        $sheet->setCellValue('F' . $row, $totalValue);
        $this->styleTotalRow($sheet, $row, ['E', 'F']);

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->getStyle('D2:F' . $row)->getNumberFormat()
            ->setFormatCode('#,##0.000');

        return $this->downloadSpreadsheet($spreadsheet, "Rapport_Stock_" . date('Ymd') . ".xlsx");
    }

    /**
     * Export contacts to Excel.
     */
    public function exportContacts(array $filters = [])
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Contacts');

        $this->styleHeader($sheet, ['A1', 'B1', 'C1', 'D1', 'E1', 'F1', 'G1']);

        $sheet->setCellValue('A1', 'Type');
        $sheet->setCellValue('B1', 'Nom');
        $sheet->setCellValue('C1', 'Email');
        $sheet->setCellValue('D1', 'Téléphone');
        $sheet->setCellValue('E1', 'Entreprise');
        $sheet->setCellValue('F1', 'Source');
        $sheet->setCellValue('G1', 'Score');

        $query = Contact::query();

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (isset($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }
        if (isset($filters['source'])) {
            $query->where('source', $filters['source']);
        }

        $contacts = $query->get();

        $row = 2;
        foreach ($contacts as $contact) {
            $sheet->setCellValue('A' . $row, strtoupper($contact->type));
            $sheet->setCellValue('B' . $row, $contact->first_name . ' ' . $contact->last_name);
            $sheet->setCellValue('C' . $row, $contact->email);
            $sheet->setCellValue('D' . $row, $contact->mobile ?? $contact->phone);
            $sheet->setCellValue('E' . $row, $contact->company);
            $sheet->setCellValue('F' . $row, $contact->source);
            $sheet->setCellValue('G' . $row, $contact->score);

            $row++;
        }

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $this->downloadSpreadsheet($spreadsheet, "Contacts_" . date('Ymd') . ".xlsx");
    }

    /**
     * Export opportunities to Excel.
     */
    public function exportOpportunities(array $filters = [])
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Opportunités');

        $this->styleHeader($sheet, ['A1', 'B1', 'C1', 'D1', 'E1', 'F1']);

        $sheet->setCellValue('A1', 'Titre');
        $sheet->setCellValue('B1', 'Valeur');
        $sheet->setCellValue('C1', 'Probabilité %');
        $sheet->setCellValue('D1', 'Statut');
        $sheet->setCellValue('E1', 'Date Clôture Prévue');
        $sheet->setCellValue('F1', 'Contact');

        $query = Opportunity::with('contact');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['pipeline_id'])) {
            $query->where('pipeline_id', $filters['pipeline_id']);
        }

        $opportunities = $query->get();

        $row = 2;
        foreach ($opportunities as $opp) {
            $sheet->setCellValue('A' . $row, $opp->title);
            $sheet->setCellValue('B' . $row, (float) $opp->value);
            $sheet->setCellValue('C' . $row, $opp->probability);
            $sheet->setCellValue('D' . $row, strtoupper($opp->status));
            $sheet->setCellValue('E' . $row, $opp->expected_close_date?->format('d/m/Y'));
            $sheet->setCellValue('F' . $row, $opp->contact ? $opp->contact->first_name . ' ' . $opp->contact->last_name : '');

            $row++;
        }

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $this->downloadSpreadsheet($spreadsheet, "Opportunites_" . date('Ymd') . ".xlsx");
    }

    /**
     * Export employees to Excel.
     */
    public function exportEmployees()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Employés');

        $this->styleHeader($sheet, ['A1', 'B1', 'C1', 'D1', 'E1', 'F1']);

        $sheet->setCellValue('A1', 'Matricule');
        $sheet->setCellValue('B1', 'Nom Complet');
        $sheet->setCellValue('C1', 'CIN');
        $sheet->setCellValue('D1', 'CNSS');
        $sheet->setCellValue('E1', 'Date Embauche');
        $sheet->setCellValue('F1', 'Statut');

        $employees = Employee::all();

        $row = 2;
        foreach ($employees as $employee) {
            $sheet->setCellValue('A' . $row, $employee->employee_number);
            $sheet->setCellValue('B' . $row, $employee->first_name . ' ' . $employee->last_name);
            $sheet->setCellValue('C' . $row, $employee->cin);
            $sheet->setCellValue('D' . $row, $employee->cnss_number);
            $sheet->setCellValue('E' . $row, $employee->hire_date?->format('d/m/Y'));
            $sheet->setCellValue('F' . $row, strtoupper($employee->status));

            $row++;
        }

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $this->downloadSpreadsheet($spreadsheet, "Employes_" . date('Ymd') . ".xlsx");
    }

    /**
     * Export audit logs to Excel.
     */
    public function exportAuditLogs(?string $startDate, ?string $endDate)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Audit Logs');

        $this->styleHeader($sheet, ['A1', 'B1', 'C1', 'D1', 'E1']);

        $sheet->setCellValue('A1', 'Date');
        $sheet->setCellValue('B1', 'Utilisateur');
        $sheet->setCellValue('C1', 'Action');
        $sheet->setCellValue('D1', 'Model');
        $sheet->setCellValue('E1', 'Description');

        $query = AuditLog::with('user');

        if ($startDate && $endDate) {
            $query->between($startDate, $endDate);
        }

        $logs = $query->orderByDesc('created_at')->limit(5000)->get();

        $row = 2;
        foreach ($logs as $log) {
            $sheet->setCellValue('A' . $row, $log->created_at->format('d/m/Y H:i'));
            $sheet->setCellValue('B' . $row, $log->user?->name ?? 'System');
            $sheet->setCellValue('C' . $row, strtoupper($log->event));
            $sheet->setCellValue('D' . $row, class_basename($log->auditable_type));
            $sheet->setCellValue('E' . $row, $log->description);

            $row++;
        }

        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $this->downloadSpreadsheet($spreadsheet, "Audit_Logs_" . date('Ymd') . ".xlsx");
    }

    /**
     * Style header row.
     */
    protected function styleHeader($sheet, array $cells): void
    {
        foreach ($cells as $cell) {
            $sheet->getStyle($cell)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ]);
        }
    }

    /**
     * Style total row.
     */
    protected function styleTotalRow($sheet, int $row, array $columns): void
    {
        foreach ($columns as $col) {
            $sheet->getStyle($col . $row)->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F2F2F2'],
                ],
            ]);
        }
    }

    /**
     * Download spreadsheet.
     */
    protected function downloadSpreadsheet(Spreadsheet $spreadsheet, string $filename)
    {
        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    /**
     * Get payment status label in French.
     */
    protected function getPaymentStatusLabel(string $status): string
    {
        return match($status) {
            'paid' => 'Payé',
            'pending' => 'En attente',
            'partial' => 'Partiel',
            'overdue' => 'En retard',
            default => $status,
        };
    }
}
