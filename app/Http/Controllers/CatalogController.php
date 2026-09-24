<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $search = Str::limit(trim((string) $request->query('q', '')), 100, '');
        $categorySlug = Str::limit((string) $request->query('category', ''), 100, '');
        $sort = (string) $request->query('sort', 'latest');

        if (! in_array($sort, ['latest', 'price_asc', 'price_desc', 'name'], true)) {
            $sort = 'latest';
        }

        $query = Product::active()->with('category');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($categorySlug !== '') {
            $query->whereHas('category', fn ($q) => $q->where('slug', $categorySlug));
        }

        match ($sort) {
            'price_asc' => $query->orderBy('price_per_day'),
            'price_desc' => $query->orderByDesc('price_per_day'),
            'name' => $query->orderBy('name'),
            default => $query->latest(),
        };

        return view('catalog.index', [
            'products' => $query->paginate(12)->withQueryString(),
            'categories' => Category::orderBy('name')->get(),
            'filters' => [
                'q' => $search,
                'category' => $categorySlug,
                'sort' => $sort,
            ],
        ]);
    }

    public function show(string $slug): View
    {
        $product = Product::active()
            ->with(['category', 'images'])
            ->where('slug', $slug)
            ->firstOrFail();

        $related = Product::active()
            ->with('category')
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->id)
            ->latest()
            ->take(4)
            ->get();

        return view('catalog.show', compact('product', 'related'));
    }
}