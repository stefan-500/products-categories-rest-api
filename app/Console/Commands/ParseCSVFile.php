<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Department;
use App\Models\Manufacturer;
use App\Models\Product;
use Illuminate\Console\Command;

class ParseCSVFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:products-csv';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import products data from a CSV file to the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Dodati naziv CSV fajla iz root foldera projekta (nazivfajla.csv)
        $file = fopen("", "r");
        $assocArray = [];

        if ($file !== false) {
            if (($headerData = fgetcsv($file)) !== false) { // header podaci 
                $keys = $headerData;
            }

            while (($row = fgetcsv($file)) !== false) { // red ostalih podataka
                $assocArray[] = array_combine($keys, $row); // kreiranje asocijativnog niza
            }
            fclose($file);

            // Mapiranje podataka u Eloquent modele
            foreach ($assocArray as $data) {

                $manufacturer = Manufacturer::firstOrCreate([
                    'name' => $data['manufacturer_name']
                ]);

                $department = Department::firstOrCreate([
                    'name' => $data['deparment_name']
                ]);

                $category = Category::firstOrCreate([
                    'name' => $data['category_name']
                ]);
                $category->departments()->sync([$department->id]); // pivot tabela category_department

                $regularPrice = $data['regular_price'] * 100; // cijene su tipa integer u bazi podataka
                $salePrice = $data['sale_price'] * 100;
                $product = Product::firstOrCreate(
                    [
                        'product_number' => $data['product_number'],
                        'upc' => $data['upc'],
                        'sku' => $data['sku'],
                        'regular_price' => $regularPrice,
                        'sale_price' => $salePrice,
                        'description' => $data['description'],
                        'manufacturer_id' => $manufacturer->id
                    ]
                );
                $product->categories()->sync([$category->id]); // pivot tabela product_category
            }
        }
    }
}
