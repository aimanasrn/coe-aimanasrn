<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // New products start with placeholder prices and no stock.
        // Update their actual prices and stock before selling.
        $defaultPrice = '0.00';
        $defaultStock = 0;

        $products = [
            // Writing Instruments
            [
                'name' => 'Blue Ballpoint Pen',
                'sku' => 'ST-PEN-BP-BLU-001',
                'category' => 'Writing Instruments',
                'description' => 'Blue-ink ballpoint pen for everyday writing, taking notes, and completing school or office work.',
            ],
            [
                'name' => 'Black Gel Pen',
                'sku' => 'ST-PEN-GEL-BLK-001',
                'category' => 'Writing Instruments',
                'description' => 'Black-ink gel pen for clear writing in notebooks, journals, and documents.',
            ],
            [
                'name' => 'HB Pencil',
                'sku' => 'ST-PENCIL-HB-001',
                'category' => 'Writing Instruments',
                'description' => 'HB graphite pencil for everyday writing, sketching, and school assignments.',
            ],
            [
                'name' => 'Mechanical Pencil',
                'sku' => 'ST-PENCIL-MECH-001',
                'category' => 'Writing Instruments',
                'description' => 'Refillable mechanical pencil for writing and drawing without the need for sharpening.',
            ],
            [
                'name' => 'Highlighter',
                'sku' => 'ST-HIGHLIGHTER-001',
                'category' => 'Writing Instruments',
                'description' => 'Highlight important information in textbooks, study notes, and documents for easy reference.',
            ],
            [
                'name' => 'Permanent Marker',
                'sku' => 'ST-MARKER-PERM-001',
                'category' => 'Writing Instruments',
                'description' => 'Permanent marker for labelling and marking suitable surfaces at school, home, or the office.',
            ],

            // Notebooks & Paper
            [
                'name' => 'A4 Notebook',
                'sku' => 'ST-NOTEBOOK-A4-001',
                'category' => 'Notebooks & Paper',
                'description' => 'A4 notebook with plenty of space for lesson notes, meeting records, and everyday writing.',
            ],
            [
                'name' => 'Exercise Book',
                'sku' => 'ST-EXERCISE-BOOK-001',
                'category' => 'Notebooks & Paper',
                'description' => 'Exercise book for classroom activities, homework, revision, and writing practice.',
            ],
            [
                'name' => 'A4 Printing Paper',
                'sku' => 'ST-PAPER-A4-001',
                'category' => 'Notebooks & Paper',
                'description' => 'A4 paper for everyday document printing, photocopying, and written work.',
            ],
            [
                'name' => 'Sticky Notes',
                'sku' => 'ST-STICKY-NOTES-001',
                'category' => 'Notebooks & Paper',
                'description' => 'Adhesive notes for reminders, page markers, quick messages, and organising tasks.',
            ],
            [
                'name' => 'Graph Paper',
                'sku' => 'ST-PAPER-GRAPH-001',
                'category' => 'Notebooks & Paper',
                'description' => 'Grid-lined paper for plotting graphs, working through mathematics exercises, and drawing diagrams.',
            ],

            // Erasers & Sharpeners
            [
                'name' => 'Eraser',
                'sku' => 'ST-ERASER-001',
                'category' => 'Erasers & Sharpeners',
                'description' => 'Eraser for removing graphite pencil marks and correcting written work or sketches.',
            ],
            [
                'name' => 'Pencil Sharpener',
                'sku' => 'ST-SHARPENER-001',
                'category' => 'Erasers & Sharpeners',
                'description' => 'Pencil sharpener for maintaining a pointed tip for clear writing and precise drawing.',
            ],

            // Correction Supplies
            [
                'name' => 'Correction Tape',
                'sku' => 'ST-CORRECTION-TAPE-001',
                'category' => 'Correction Supplies',
                'description' => 'Cover writing mistakes with correction tape that allows you to write over the corrected area immediately.',
            ],
            [
                'name' => 'Correction Fluid',
                'sku' => 'ST-CORRECTION-FLUID-001',
                'category' => 'Correction Supplies',
                'description' => 'Cover writing and typing errors with correction fluid. Allow it to dry before writing over the area.',
            ],

            // Geometry & Measuring Tools
            [
                'name' => '30 cm Ruler',
                'sku' => 'ST-RULER-30CM-001',
                'category' => 'Geometry & Measuring Tools',
                'description' => 'A 30 cm ruler for measuring lengths, drawing straight lines, and completing school projects.',
            ],
            [
                'name' => 'Geometry Set',
                'sku' => 'ST-GEOMETRY-SET-001',
                'category' => 'Geometry & Measuring Tools',
                'description' => 'A set of geometry tools for measuring angles, drawing shapes, and completing mathematics exercises.',
            ],
            [
                'name' => 'Protractor',
                'sku' => 'ST-PROTRACTOR-001',
                'category' => 'Geometry & Measuring Tools',
                'description' => 'Protractor for measuring and drawing angles in geometry lessons and technical work.',
            ],

            // Files & Folders
            [
                'name' => 'Ring Binder',
                'sku' => 'ST-BINDER-RING-001',
                'category' => 'Files & Folders',
                'description' => 'Ring binder for securing punched papers and organising notes, reports, and reference materials.',
            ],
            [
                'name' => 'Document Folder',
                'sku' => 'ST-FOLDER-DOC-001',
                'category' => 'Files & Folders',
                'description' => 'Keep loose papers together and organise assignments, reports, and important documents.',
            ],
            [
                'name' => 'Clear Document Holder',
                'sku' => 'ST-HOLDER-CLEAR-001',
                'category' => 'Files & Folders',
                'description' => 'Transparent document holder that keeps papers together while allowing you to identify the contents easily.',
            ],

            // Office Accessories
            [
                'name' => 'Stapler',
                'sku' => 'ST-STAPLER-001',
                'category' => 'Office Accessories',
                'description' => 'Stapler for fastening sheets of paper together and keeping assignments or documents organised.',
            ],
            [
                'name' => 'Staples',
                'sku' => 'ST-STAPLES-001',
                'category' => 'Office Accessories',
                'description' => 'Staples for fastening paperwork together. Choose the correct size for your stapler.',
            ],
            [
                'name' => 'Paper Clips',
                'sku' => 'ST-PAPER-CLIPS-001',
                'category' => 'Office Accessories',
                'description' => 'Hold loose sheets together temporarily without punching holes or stapling them.',
            ],
            [
                'name' => 'Binder Clips',
                'sku' => 'ST-BINDER-CLIPS-001',
                'category' => 'Office Accessories',
                'description' => 'Removable clips for holding stacks of paper together and organising documents.',
            ],

            // Cutting & Adhesives
            [
                'name' => 'Scissors',
                'sku' => 'ST-SCISSORS-001',
                'category' => 'Cutting & Adhesives',
                'description' => 'Scissors for cutting paper and suitable craft materials for school, office, and home projects.',
            ],
            [
                'name' => 'Glue Stick',
                'sku' => 'ST-GLUE-STICK-001',
                'category' => 'Cutting & Adhesives',
                'description' => 'Apply adhesive to paper and lightweight card for scrapbooks, classroom activities, and craft projects.',
            ],
            [
                'name' => 'Transparent Tape',
                'sku' => 'ST-TAPE-CLEAR-001',
                'category' => 'Cutting & Adhesives',
                'description' => 'Clear adhesive tape for joining paper, wrapping gifts, and everyday stationery tasks.',
            ],

            // Art & Colouring Supplies
            [
                'name' => 'Coloured Pencils',
                'sku' => 'ST-PENCIL-COLOUR-001',
                'category' => 'Art & Colouring Supplies',
                'description' => 'Coloured pencils for colouring, illustrating, and adding detail to artwork and school projects.',
            ],
            [
                'name' => 'Crayons',
                'sku' => 'ST-CRAYONS-001',
                'category' => 'Art & Colouring Supplies',
                'description' => 'Crayons for colouring pictures, making drawings, and exploring creative activities.',
            ],
            [
                'name' => 'Watercolour Set',
                'sku' => 'ST-WATERCOLOUR-SET-001',
                'category' => 'Art & Colouring Supplies',
                'description' => 'Watercolour paints for creating washes, blending colours, and exploring painting techniques on suitable paper.',
            ],

            // Storage & Organisers
            [
                'name' => 'Pencil Case',
                'sku' => 'ST-PENCIL-CASE-001',
                'category' => 'Storage & Organisers',
                'description' => 'Keep pens, pencils, erasers, and other small stationery items together for easy storage and carrying.',
            ],
        ];

        $categoryNames = array_values(array_unique(array_column($products, 'category')));
        $categoryIds = Category::whereIn('name', $categoryNames)->pluck('id', 'name');
        $missingCategories = array_diff($categoryNames, $categoryIds->keys()->all());

        if ($missingCategories !== []) {
            throw new RuntimeException(
                'Missing categories: '.implode(', ', $missingCategories)
                .'. Run php artisan db:seed --class=CategorySeeder first.'
            );
        }

        DB::transaction(function () use ($products, $categoryIds, $defaultPrice, $defaultStock): void {
            foreach ($products as $data) {
                // Match existing products by SKU. Defaults apply only to new products.
                $product = Product::firstOrNew(
                    ['sku' => $data['sku']],
                    ['price' => $defaultPrice, 'stock_quantity' => $defaultStock],
                );

                $product->fill([
                    'category_id' => $categoryIds[$data['category']],
                    'name' => $data['name'],
                    'description' => $data['description'],
                ]);

                $product->save();
            }
        });
    }
}
