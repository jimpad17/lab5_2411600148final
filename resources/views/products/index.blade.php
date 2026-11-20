@extends('layouts.app')

@section('title', 'Inventory')

@section('heading')
    Gym <span class="text-warning fw-bold">Inventory</span>
@endsection

@section('content')
    <!-- Filters: the server-side version of the Lab 4 filter toolbar -->
    <div class="card mb-4 shadow-sm bg-white p-2">
        <div class="card-body">
            <form method="GET" action="{{ route('products.index') }}" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Category</label>
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category }}" {{ ($filters['category'] ?? '') === $category ? 'selected' : '' }}>{{ $category }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Stock Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="in" {{ ($filters['status'] ?? '') === 'in' ? 'selected' : '' }}>In Stock</option>
                        <option value="low" {{ ($filters['status'] ?? '') === 'low' ? 'selected' : '' }}>Low Stock</option>
                        <option value="out" {{ ($filters['status'] ?? '') === 'out' ? 'selected' : '' }}>Out of Stock</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Min Qty</label>
                    <input type="number" name="min_quantity" class="form-control" min="0" value="{{ $filters['min_quantity'] ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Max Qty</label>
                    <input type="number" name="max_quantity" class="form-control" min="0" value="{{ $filters['max_quantity'] ?? '' }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted mb-1">Search (name, SKU or supplier)</label>
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Search inventory..."
                               value="{{ $filters['search'] ?? '' }}">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
                        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Clear</a>
                    </div>
                </div>
                <div class="col-12 d-flex flex-wrap gap-2">
                    <a href="{{ route('products.create') }}" class="btn btn-success btn-sm px-3">
                        <i class="bi bi-plus-circle me-1"></i> Add Product
                    </a>
                    <a href="{{ route('products.export', request()->only(['category', 'status', 'min_quantity', 'max_quantity', 'search'])) }}"
                       class="btn btn-primary btn-sm px-3">
                        <i class="bi bi-download me-1"></i> Export CSV
                    </a>
                    <a href="{{ route('reports.index') }}" class="btn btn-outline-warning btn-sm px-3">
                        <i class="bi bi-bar-chart-line me-1"></i> Inventory Report
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white pt-3 pb-2 d-flex justify-content-between align-items-center border-bottom-0">
            <h5 class="mb-0 fw-bold"><i class="bi bi-box-seam text-warning me-2"></i> Product List</h5>
            <small class="text-muted">Showing {{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }} of {{ $products->total() }} products</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">SKU</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Qty</th>
                            <th>Reorder</th>
                            <th>Unit Price</th>
                            <th>Status</th>
                            <th>Supplier</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            <tr>
                                <td class="ps-4"><code class="sku-tag">{{ $product->sku }}</code></td>
                                <td><strong>{{ $product->name }}</strong></td>
                                <td><span class="badge badge-category">{{ $product->category }}</span></td>
                                <td>{{ $product->quantity }}</td>
                                <td>{{ $product->reorder_level }}</td>
                                <td>₱{{ number_format($product->unit_price, 2) }}</td>
                                <td>
                                    <span class="badge badge-{{ $product->quantity == 0 ? 'out' : ($product->isLowStock() ? 'low' : 'in') }}">
                                        {{ $product->stock_status }}
                                    </span>
                                </td>
                                <td><small>{{ $product->supplier ?? '—' }}</small></td>
                                <td class="text-end pe-4 text-nowrap">
                                    <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form method="POST" action="{{ route('products.destroy', $product) }}" class="d-inline"
                                          onsubmit="return confirm('Delete {{ addslashes($product->name) }} from the inventory?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">No products match your filter criteria.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-body border-top d-flex justify-content-between align-items-center">
            <small class="text-muted">JimTrack Fitness · Inventory Management System</small>
            {{ $products->links() }}
        </div>
    </div>
@endsection
