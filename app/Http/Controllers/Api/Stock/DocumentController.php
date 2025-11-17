<?php
namespace App\Http\Controllers\Api\Stock;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Services\Compliance\TaxCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocumentController extends Controller
{
    protected $taxCalculator;

    public function __construct(TaxCalculator $taxCalculator)
    {
        $this->taxCalculator = $taxCalculator;
    }

    public function index(Request $request)
    {
        $query = Document::with('customer', 'supplier', 'warehouse', 'user');

        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->customer_id) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->date_from) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $documents = $query->latest('date')->paginate($request->per_page ?? 15);

        return response()->json($documents);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:quote,delivery_note,invoice,credit_note,purchase_order,purchase_invoice',
            'number' => 'required|string|unique:documents,number',
            'date' => 'required|date',
            'due_date' => 'nullable|date',
            'customer_id' => 'nullable|exists:customers,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'discount_rate' => 'nullable|numeric|min:0|max:100',
            'note' => 'nullable|string',
            'terms' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.quantity' => 'required|numeric|min:0',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.tva_rate' => 'required|in:19,13,7,0',
            'lines.*.discount_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        return DB::transaction(function () use ($validated) {
            // Calculate totals
            $lines = [];
            foreach ($validated['lines'] as $line) {
                $lines[] = [
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'tva_rate' => $line['tva_rate'],
                    'discount_rate' => $line['discount_rate'] ?? 0,
                ];
            }

            $totals = $this->taxCalculator->calculateDocument(
                $lines,
                $validated['discount_rate'] ?? 0,
                in_array($validated['type'], ['invoice', 'credit_note'])
            );

            // Create document
            $document = Document::create([
                'type' => $validated['type'],
                'number' => $validated['number'],
                'date' => $validated['date'],
                'due_date' => $validated['due_date'] ?? null,
                'customer_id' => $validated['customer_id'] ?? null,
                'supplier_id' => $validated['supplier_id'] ?? null,
                'warehouse_id' => $validated['warehouse_id'] ?? null,
                'discount_rate' => $validated['discount_rate'] ?? 0,
                'note' => $validated['note'] ?? null,
                'terms' => $validated['terms'] ?? null,
                'subtotal' => $totals['subtotal'],
                'discount_amount' => $totals['discount_amount'],
                'total_ht' => $totals['total_ht'],
                'total_tva' => $totals['total_tva'],
                'timbre_fiscal' => $totals['timbre_fiscal'],
                'total_ttc' => $totals['total_ttc'],
                'status' => 'draft',
                'user_id' => auth()->id(),
            ]);

            // Create lines
            foreach ($validated['lines'] as $index => $lineData) {
                $lineCalcs = $this->calculateLine($lineData);
                
                $document->lines()->create([
                    'product_id' => $lineData['product_id'],
                    'quantity' => $lineData['quantity'],
                    'unit_price' => $lineData['unit_price'],
                    'discount_rate' => $lineData['discount_rate'] ?? 0,
                    'discount_amount' => $lineCalcs['discount_amount'],
                    'tva_rate' => $lineData['tva_rate'],
                    'tva_amount' => $lineCalcs['tva_amount'],
                    'total_ht' => $lineCalcs['total_ht'],
                    'total_ttc' => $lineCalcs['total_ttc'],
                    'line_order' => $index,
                ]);
            }

            return response()->json($document->load('lines.product', 'customer', 'supplier'), 201);
        });
    }

    public function show(Document $document)
    {
        return response()->json($document->load('lines.product', 'customer', 'supplier', 'payments'));
    }

    public function destroy(Document $document)
    {
        if ($document->status !== 'draft') {
            return response()->json([
                'message' => 'Seuls les documents en brouillon peuvent être supprimés'
            ], 422);
        }

        $document->delete();

        return response()->json(['message' => 'Document supprimé avec succès']);
    }

    private function calculateLine(array $line)
    {
        $amount = $line['quantity'] * $line['unit_price'];
        $discountAmount = $amount * (($line['discount_rate'] ?? 0) / 100);
        $totalHT = $amount - $discountAmount;
        $tvaAmount = $this->taxCalculator->calculateTVA($totalHT, $line['tva_rate']);
        $totalTTC = $totalHT + $tvaAmount;

        return [
            'discount_amount' => $discountAmount,
            'total_ht' => $totalHT,
            'tva_amount' => $tvaAmount,
            'total_ttc' => $totalTTC,
        ];
    }
}
