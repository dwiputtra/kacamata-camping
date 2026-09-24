<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CampingDataSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Tenda' => [
                ['name' => 'Tenda Dome 4 Orang', 'price' => 50000, 'stock' => 8, 'specs' => ['Kapasitas' => '4 orang', 'Bahan' => 'Polyester waterproof', 'Berat' => '3.2 kg'], 'desc' => 'Tenda dome ringan dan mudah dipasang, cocok untuk pendakian akhir pekan.'],
                ['name' => 'Tenda Dome 6 Orang', 'price' => 75000, 'stock' => 5, 'specs' => ['Kapasitas' => '6 orang', 'Bahan' => 'Polyester waterproof', 'Berat' => '4.5 kg'], 'desc' => 'Tenda besar untuk rombongan, dilengkapi ruang tambahan untuk menyimpan barang.'],
            ],
            'Carrier' => [
                ['name' => 'Carrier 60 Liter', 'price' => 35000, 'stock' => 10, 'specs' => ['Kapasitas' => '60 liter', 'Bahan' => 'Cordura', 'Rain cover' => 'Termasuk'], 'desc' => 'Tas carrier dengan banyak kompartemen dan penyangga punggung yang nyaman.'],
                ['name' => 'Carrier 80 Liter', 'price' => 45000, 'stock' => 6, 'specs' => ['Kapasitas' => '80 liter', 'Bahan' => 'Cordura', 'Rain cover' => 'Termasuk'], 'desc' => 'Cocok untuk pendakian panjang dengan perlengkapan lengkap.'],
            ],
            'Sleeping Bag' => [
                ['name' => 'Sleeping Bag Standar', 'price' => 20000, 'stock' => 15, 'specs' => ['Suhu nyaman' => '15-20°C', 'Bahan' => 'Polar fleece'], 'desc' => 'Sleeping bag ringan untuk cuaca tropis pegunungan.'],
                ['name' => 'Sleeping Bag Musim Dingin', 'price' => 30000, 'stock' => 8, 'specs' => ['Suhu nyaman' => '5-10°C', 'Bahan' => 'Polar fleece tebal'], 'desc' => 'Untuk pendakian di ketinggian dengan suhu lebih dingin.'],
            ],
            'Kompor' => [
                ['name' => 'Kompor Portable + Gas', 'price' => 15000, 'stock' => 12, 'specs' => ['Jenis' => 'Kompor angin portable', 'Bahan bakar' => 'Gas kaleng'], 'desc' => 'Praktis dibawa dan cepat menyala, cocok untuk memasak di camp.'],
            ],
            'Matras' => [
                ['name' => 'Matras Camping', 'price' => 10000, 'stock' => 20, 'specs' => ['Bahan' => 'Foam anti air', 'Ukuran' => '180 x 50 cm'], 'desc' => 'Alas tidur empuk dan tahan lembap.'],
            ],
            'Lampu Camping' => [
                ['name' => 'Lampu LED Camping', 'price' => 8000, 'stock' => 15, 'specs' => ['Daya' => 'Baterai AA', 'Mode' => '3 tingkat kecerahan'], 'desc' => 'Lampu gantung yang terang dan hemat baterai.'],
            ],
            'Trekking Pole' => [
                ['name' => 'Trekking Pole Sepasang', 'price' => 12000, 'stock' => 10, 'specs' => ['Bahan' => 'Aluminium', 'Panjang' => 'Bisa disesuaikan 65-135 cm'], 'desc' => 'Membantu keseimbangan dan mengurangi beban pada lutut saat mendaki.'],
            ],
        ];

        foreach ($categories as $categoryName => $products) {
            $category = Category::firstOrCreate(
                ['slug' => Str::slug($categoryName)],
                ['name' => $categoryName]
            );

            foreach ($products as $product) {
                Product::firstOrCreate(
                    ['slug' => Str::slug($product['name'])],
                    [
                        'category_id' => $category->id,
                        'name' => $product['name'],
                        'price_per_day' => $product['price'],
                        'stock' => $product['stock'],
                        'description' => $product['desc'],
                        'specifications' => $product['specs'],
                        'is_active' => true,
                    ]
                );
            }
        }

        $testimonials = [
            ['name' => 'Budi Santoso', 'title' => 'Pendaki Gunung Rinjani', 'rating' => 5, 'content' => 'Alatnya lengkap dan bersih. Proses sewa juga cepat, tinggal konfirmasi lewat WhatsApp.'],
            ['name' => 'Sari Dewi', 'title' => 'Mahasiswa Pecinta Alam', 'rating' => 5, 'content' => 'Harga sewa terjangkau untuk anak kos seperti saya. Tendanya juga masih bagus kondisinya.'],
            ['name' => 'Made Wirawan', 'title' => 'Traveler', 'rating' => 4, 'content' => 'Pelayanan ramah, alat sesuai deskripsi. Semoga koleksi produknya makin banyak.'],
            ['name' => 'Ayu Lestari', 'title' => 'Komunitas Camping Bali', 'rating' => 5, 'content' => 'Sudah beberapa kali sewa di sini untuk acara komunitas. Selalu puas dengan kualitasnya.'],
        ];

        foreach ($testimonials as $testimonial) {
            Testimonial::firstOrCreate(
                ['name' => $testimonial['name'], 'content' => $testimonial['content']],
                [
                    'title' => $testimonial['title'],
                    'rating' => $testimonial['rating'],
                    'is_published' => true,
                ]
            );
        }
    }
}