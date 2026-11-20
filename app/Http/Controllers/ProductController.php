<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Shared search / filter logic used by the index page and the CSV export.
     */
    private function filteredProducts(Request $request)
    {
        $query = Product::query();

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('status')) {
            match ($request->input('status')) {
                'low' => $query->lowStock(),
                'out' => $query->outOfStock(),
                'in' => $query->whereColumn('quantity', '>', 'reorder_level'),
                default => null,
            };
        }

        if ($request->filled('min_quantity')) {
            $query->where('quantity', '>=', (int) $request->input('min_quantity'));
        }

        if ($request->filled('max_quantity')) {
            $query->where('quantity', '<=', (int) $request->input('max_quantity'));
        }

        if ($request->filled('search')) {
            $term = '%'.strip_tags((string) $request->input('search')).'%';

            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('sku', 'like', $term)
                    ->orWhere('supplier', 'like', $term);
            });
        }

        return $query->orderBy('id');
    }

    private function rules(?Product $product = null): array
    {
        $uniqueSku = 'unique:products,sku';
        if ($product) {
            $uniqueSku .= ','.$product->id;
        }

        return [
            'name' => ['required', 'string', 'max:150'],
            'sku' => ['required', 'string', 'max:50', $uniqueSku],
            'description' => ['nullable', 'string', 'max:1000'],
            'category' => ['required', 'string', Rule::in(Product::categories())],
            'quantity' => ['required', 'integer', 'min:0', 'max:1000000'],
            'reorder_level' => ['required', 'integer', 'min:0', 'max:1000000'],
            'unit_price' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'supplier' => ['nullable', 'string', 'max:150'],
        ];
    }

    private function messages(): array
    {
        return [
            'name.required' => 'Product name is required.',
            'sku.required' => 'SKU is required.',
            'sku.unique' => 'This SKU is already used by another product.',
            'category.required' => 'Please choose a category.',
            'quantity.min' => 'Quantity cannot be negative.',
            'reorder_level.min' => 'Reorder level cannot be negative.',
            'unit_price.min' => 'Unit price must be at least 0.01.',
        ];
    }

    /**
     * Basic data sanitization before saving to MySQL.
     */
    private function sanitized(array $data): array
    {
        $data['name'] = strip_tags(trim($data['name']));
        $data['sku'] = strtoupper(strip_tags(trim($data['sku'])));
        $data['description'] = isset($data['description']) ? strip_tags(trim($data['description'])) : null;
        $data['supplier'] = isset($data['supplier']) ? strip_tags(trim($data['supplier'])) : null;

        return $data;
    }

    public function index(Request $request)
    {
        $products = $this->filteredProducts($request)->paginate(10)->withQueryString();
        $categories = Product::categories();
        $filters = $request->only(['category', 'status', 'min_quantity', 'max_quantity', 'search']);

        return view('products.index', compact('products', 'categories', 'filters'));
    }

    public function create()
    {
        return view('products.create', [
            'product' => new Product(),
            'categories' => Product::categories(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->sanitized($request->validate($this->rules(), $this->messages()));

        $product = Product::create($data);

        InventoryTransaction::create([
            'product_id' => $product->id,
            'type' => InventoryTransaction::TYPE_IN,
            'quantity' => (int) $product->quantity,
            'reference' => 'INITIAL-STOCK',
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('products.index')
            ->with('success', "Product '{$product->name}' was added to the inventory.");
    }

    public function show(Product $product)
    {
        $product->load('transactions.user');

        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        return view('products.edit', [
            'product' => $product,
            'categories' => Product::categories(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->sanitized($request->validate($this->rules($product), $this->messages()));
        $oldQuantity = (int) $product->quantity;

        $product->update($data);

        if ((int) $product->quantity !== $oldQuantity) {
            InventoryTransaction::create([
                'product_id' => $product->id,
                'type' => InventoryTransaction::TYPE_ADJUSTMENT,
                'quantity' => abs((int) $product->quantity - $oldQuantity),
                'reference' => 'MANUAL-ADJUSTMENT',
                'user_id' => auth()->id(),
            ]);
        }

        return redirect()->route('products.index')
            ->with('success', "Product '{$product->name}' was updated successfully.");
    }

    public function destroy(Product $product)
    {
        $name = $product->name;
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', "Product '{$name}' was deleted from the inventory.");
    }

    /**
     * Inventory report download - the server-side version of Lab 4's CSV export.
     */
    public function exportCsv(Request $request)
    {
        $products = $this->filteredProducts($request)->get();
        $filename = 'jimtrack-inventory-'.now()->format('Y-m-d').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ];

        $callback = function () use ($products) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['SKU', 'Product Name', 'Category', 'Description', 'Quantity', 'Reorder Level', 'Unit Price', 'Stock Status', 'Supplier', 'Stock Value']);

            foreach ($products as $product) {
                fputcsv($handle, [
                    $product->sku,
                    $product->name,
                    $product->category,
                    $product->description,
                    $product->quantity,
                    $product->reorder_level,
                    $product->unit_price,
                    $product->stock_status,
                    $product->supplier,
                    number_format($product->stock_value, 2, '.', ''),
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
