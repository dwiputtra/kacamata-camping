<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Models\Testimonial;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('home', [
            'banner' => Banner::active()->first(),
            'categories' => Category::withCount(['products' => fn ($query) => $query->active()])
                ->orderBy('name')
                ->get(),
            'latestProducts' => Product::active()->with('category')->latest()->take(8)->get(),
            'testimonials' => Testimonial::published()->latest()->take(6)->get(),
        ]);
    }
}