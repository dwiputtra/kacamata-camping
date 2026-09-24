<?php

namespace App\Http\Controllers;

class RobotsController extends Controller
{
    public function index()
    {
        $lines = [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /keranjang',
            'Disallow: /checkout',
            'Disallow: /pesanan',
            'Disallow: /login',
            'Disallow: /register',
            '',
            'Sitemap: ' . route('sitemap'),
        ];

        return response(implode("\n", $lines), 200)
            ->header('Content-Type', 'text/plain');
    }
}