<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = [
            ['loc' => route('home'), 'priority' => '1.0'],
            ['loc' => route('catalog.index'), 'priority' => '0.9'],
            ['loc' => route('contact'), 'priority' => '0.5'],
        ];

        Product::active()->select('slug', 'updated_at')->each(function (Product $product) use (&$urls) {
            $urls[] = [
                'loc' => route('catalog.show', $product->slug),
                'lastmod' => $product->updated_at->toAtomString(),
                'priority' => '0.8',
            ];
        });

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }
}