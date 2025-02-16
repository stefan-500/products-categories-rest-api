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
    protected $signature = 'app:parse-csv-file';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse CSV file data into the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = fopen("", "r");
        $assoc_array = [];

        if ($file !== false) {

            if (($header_data = fgetcsv($file)) !== false) { // header podaci 
                $keys = $header_data;
            }

            while (($row = fgetcsv($file)) !== false) { // red ostalih podataka
                $assoc_array[] = array_combine($keys, $row); // kreiranje asocijativnog niza
            }
            fclose($file);

            // Mapiranje podataka u Eloquent modele
            foreach ($assoc_array as $data) {

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

                $product = Product::firstOrCreate(
                    [
                        'product_number' => $data['product_number'],
                        'upc' => $data['upc'],
                        'sku' => $data['sku'],
                        'regular_price' => $data['regular_price'],
                        'sale_price' => $data['sale_price'],
                        'description' => $data['description'],
                        'manufacturer_id' => $manufacturer->id
                    ]
                );
                $product->categories()->sync([$category->id]); // pivot tabela product_category
            }
        }
    }
}
