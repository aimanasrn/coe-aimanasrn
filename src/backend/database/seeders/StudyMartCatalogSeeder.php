<?php

namespace Database\Seeders;

use App\CatalogCache;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the full StudyMart catalog: every category and its StudyMart-brand products.
 *
 * Safe to run again. Categories are matched by name and products by name, so nothing is
 * duplicated. New products get automatic SKUs. Existing products keep their SKU, price,
 * stock, unit and images; demo prices and stock only replace untouched placeholders
 * (RM 0.00 with no stock).
 */
class StudyMartCatalogSeeder extends Seeder
{
    public const BRAND = 'StudyMart';

    public function run(): void
    {
        DB::transaction(function (): void {
            foreach ($this->catalog() as $categoryName => $category) {
                $categoryModel = Category::updateOrCreate(
                    ['name' => $categoryName],
                    ['description' => $category['description']],
                );

                foreach ($category['products'] as $data) {
                    $this->seedProduct($categoryModel, $data);
                }
            }
        });

        CatalogCache::invalidate();
    }

    /**
     * @param  array{name: string, price: string, stock: int, unit: string, description: string}  $data
     */
    private function seedProduct(Category $category, array $data): void
    {
        $product = Product::query()->where('name', $data['name'])->orderBy('id')->first();

        if ($product === null) {
            Product::createWithGeneratedSku([
                'category_id' => $category->id,
                'name' => $data['name'],
                'description' => $data['description'],
                'price' => $data['price'],
                'stock_quantity' => $data['stock'],
                'brand' => self::BRAND,
                'unit' => $data['unit'],
            ]);

            return;
        }

        $isPlaceholder = (float) $product->price === 0.0 && $product->stock_quantity === 0;

        $product->update([
            'category_id' => $category->id,
            'description' => $data['description'],
            'brand' => $product->brand ?: self::BRAND,
            ...($isPlaceholder ? ['price' => $data['price'], 'stock_quantity' => $data['stock']] : []),
        ]);
    }

    /**
     * @return array<string, array{description: string, products: list<array{name: string, price: string, stock: int, unit: string, description: string}>}>
     */
    private function catalog(): array
    {
        return [
            'Writing Instruments' => [
                'description' => 'Essential tools for writing, drawing, and highlighting, including pens, pencils, markers, and highlighters. Ideal for school, office, and everyday use.',
                'products' => [
                    ['name' => 'Blue Ballpoint Pen', 'price' => '1.50', 'stock' => 240, 'unit' => 'item', 'description' => 'Blue-ink ballpoint pen for everyday writing, taking notes, and completing school or office work.'],
                    ['name' => 'Black Gel Pen', 'price' => '2.90', 'stock' => 180, 'unit' => 'item', 'description' => 'Black-ink gel pen for clear writing in notebooks, journals, and documents.'],
                    ['name' => 'HB Pencil', 'price' => '0.80', 'stock' => 300, 'unit' => 'item', 'description' => 'HB graphite pencil for everyday writing, sketching, and school assignments.'],
                    ['name' => 'Mechanical Pencil', 'price' => '4.50', 'stock' => 4, 'unit' => 'item', 'description' => 'Refillable mechanical pencil for writing and drawing without the need for sharpening.'],
                    ['name' => 'Highlighter', 'price' => '2.50', 'stock' => 150, 'unit' => 'item', 'description' => 'Highlight important information in textbooks, study notes, and documents for easy reference.'],
                    ['name' => 'Permanent Marker', 'price' => '3.20', 'stock' => 90, 'unit' => 'item', 'description' => 'Permanent marker for labelling and marking suitable surfaces at school, home, or the office.'],
                ],
            ],
            'Notebooks & Paper' => [
                'description' => 'Notebooks, exercise books, sticky notes, and paper for taking notes, completing assignments, printing, and organising ideas. Perfect for school, office, and everyday use.',
                'products' => [
                    ['name' => 'A4 Notebook', 'price' => '6.90', 'stock' => 120, 'unit' => 'item', 'description' => 'A4 notebook with plenty of space for lesson notes, meeting records, and everyday writing.'],
                    ['name' => 'Exercise Book', 'price' => '1.20', 'stock' => 400, 'unit' => 'item', 'description' => 'Exercise book for classroom activities, homework, revision, and writing practice.'],
                    ['name' => 'A4 Printing Paper', 'price' => '14.90', 'stock' => 60, 'unit' => 'pack', 'description' => 'A4 paper for everyday document printing, photocopying, and written work.'],
                    ['name' => 'Sticky Notes', 'price' => '3.50', 'stock' => 3, 'unit' => 'pack', 'description' => 'Adhesive notes for reminders, page markers, quick messages, and organising tasks.'],
                    ['name' => 'Graph Paper', 'price' => '4.20', 'stock' => 75, 'unit' => 'pack', 'description' => 'Grid-lined paper for plotting graphs, working through mathematics exercises, and drawing diagrams.'],
                ],
            ],
            'Erasers & Sharpeners' => [
                'description' => 'Keep your work neat and your pencils sharp with erasers and sharpeners for school, office, and everyday use. Ideal for correcting pencil marks and maintaining precise pencil tips.',
                'products' => [
                    ['name' => 'Eraser', 'price' => '0.90', 'stock' => 260, 'unit' => 'item', 'description' => 'Eraser for removing graphite pencil marks and correcting written work or sketches.'],
                    ['name' => 'Pencil Sharpener', 'price' => '1.50', 'stock' => 140, 'unit' => 'item', 'description' => 'Pencil sharpener for maintaining a pointed tip for clear writing and precise drawing.'],
                    ['name' => 'Dust-Free Eraser', 'price' => '1.80', 'stock' => 110, 'unit' => 'item', 'description' => 'Soft eraser that gathers its crumbs together, keeping desks and paper clean while you correct mistakes.'],
                    ['name' => 'Double-Hole Sharpener with Container', 'price' => '3.90', 'stock' => 0, 'unit' => 'item', 'description' => 'Sharpener with two hole sizes for standard and jumbo pencils, plus a container that catches the shavings.'],
                ],
            ],
            'Correction Supplies' => [
                'description' => 'Correct writing mistakes neatly with correction tape and fluid, keeping your notes and documents clean and easy to read.',
                'products' => [
                    ['name' => 'Correction Tape', 'price' => '3.90', 'stock' => 95, 'unit' => 'item', 'description' => 'Cover writing mistakes with correction tape that allows you to write over the corrected area immediately.'],
                    ['name' => 'Correction Fluid', 'price' => '3.50', 'stock' => 70, 'unit' => 'item', 'description' => 'Cover writing and typing errors with correction fluid. Allow it to dry before writing over the area.'],
                    ['name' => 'Correction Tape Refill', 'price' => '2.50', 'stock' => 5, 'unit' => 'item', 'description' => 'Refill cartridge for StudyMart correction tape, so you can keep using the same dispenser.'],
                ],
            ],
            'Geometry & Measuring Tools' => [
                'description' => 'Measure accurately and draw precise shapes with rulers, protractors, compasses, and geometry sets for school and technical work.',
                'products' => [
                    ['name' => '30 cm Ruler', 'price' => '1.90', 'stock' => 200, 'unit' => 'item', 'description' => 'A 30 cm ruler for measuring lengths, drawing straight lines, and completing school projects.'],
                    ['name' => 'Geometry Set', 'price' => '8.90', 'stock' => 65, 'unit' => 'set', 'description' => 'A set of geometry tools for measuring angles, drawing shapes, and completing mathematics exercises.'],
                    ['name' => 'Protractor', 'price' => '1.50', 'stock' => 120, 'unit' => 'item', 'description' => 'Protractor for measuring and drawing angles in geometry lessons and technical work.'],
                    ['name' => '15 cm Ruler', 'price' => '1.00', 'stock' => 180, 'unit' => 'item', 'description' => 'Compact 15 cm ruler that fits in most pencil cases for quick measurements and straight lines.'],
                    ['name' => 'Compass', 'price' => '4.90', 'stock' => 2, 'unit' => 'item', 'description' => 'Compass with a pencil holder for drawing accurate circles and arcs in geometry work.'],
                ],
            ],
            'Files & Folders' => [
                'description' => 'Keep documents organised and protected with files, folders, and binders for school, office, and home use.',
                'products' => [
                    ['name' => 'Ring Binder', 'price' => '7.90', 'stock' => 45, 'unit' => 'item', 'description' => 'Ring binder for securing punched papers and organising notes, reports, and reference materials.'],
                    ['name' => 'Document Folder', 'price' => '2.20', 'stock' => 130, 'unit' => 'item', 'description' => 'Keep loose papers together and organise assignments, reports, and important documents.'],
                    ['name' => 'Clear Document Holder', 'price' => '1.20', 'stock' => 160, 'unit' => 'item', 'description' => 'Transparent document holder that keeps papers together while allowing you to identify the contents easily.'],
                    ['name' => 'Zip Document Bag', 'price' => '3.50', 'stock' => 80, 'unit' => 'item', 'description' => 'Zip-close document bag that keeps worksheets and small stationery dry and together on the way to class.'],
                    ['name' => 'Arch File', 'price' => '9.90', 'stock' => 0, 'unit' => 'item', 'description' => 'Sturdy lever arch file for storing large sets of punched documents, coursework, and records.'],
                ],
            ],
            'Office Accessories' => [
                'description' => 'Everyday essentials such as staplers, staples, paper clips, and binder clips to keep your paperwork secure and workspace organised.',
                'products' => [
                    ['name' => 'Stapler', 'price' => '9.90', 'stock' => 40, 'unit' => 'item', 'description' => 'Stapler for fastening sheets of paper together and keeping assignments or documents organised.'],
                    ['name' => 'Staples', 'price' => '2.50', 'stock' => 110, 'unit' => 'box', 'description' => 'Staples for fastening paperwork together. Choose the correct size for your stapler.'],
                    ['name' => 'Paper Clips', 'price' => '2.20', 'stock' => 90, 'unit' => 'box', 'description' => 'Hold loose sheets together temporarily without punching holes or stapling them.'],
                    ['name' => 'Binder Clips', 'price' => '4.50', 'stock' => 70, 'unit' => 'box', 'description' => 'Removable clips for holding stacks of paper together and organising documents.'],
                    ['name' => 'Two-Hole Punch', 'price' => '12.90', 'stock' => 25, 'unit' => 'item', 'description' => 'Two-hole punch for preparing worksheets and notes for ring binders and arch files.'],
                ],
            ],
            'Cutting & Adhesives' => [
                'description' => 'Cut, stick, and assemble with scissors, glue, and adhesive tapes for school projects, crafts, and everyday tasks.',
                'products' => [
                    ['name' => 'Scissors', 'price' => '4.90', 'stock' => 85, 'unit' => 'item', 'description' => 'Scissors for cutting paper and suitable craft materials for school, office, and home projects.'],
                    ['name' => 'Glue Stick', 'price' => '2.80', 'stock' => 150, 'unit' => 'item', 'description' => 'Apply adhesive to paper and lightweight card for scrapbooks, classroom activities, and craft projects.'],
                    ['name' => 'Transparent Tape', 'price' => '1.90', 'stock' => 130, 'unit' => 'item', 'description' => 'Clear adhesive tape for joining paper, wrapping gifts, and everyday stationery tasks.'],
                    ['name' => 'Safety Scissors for Kids', 'price' => '3.90', 'stock' => 60, 'unit' => 'item', 'description' => 'Round-tipped scissors sized for small hands, for safe cutting in classrooms and at home.'],
                    ['name' => 'Double-Sided Tape', 'price' => '2.90', 'stock' => 4, 'unit' => 'item', 'description' => 'Tape with adhesive on both sides for mounting paper, posters, and craft work without visible tape.'],
                ],
            ],
            'Art & Colouring Supplies' => [
                'description' => 'Bring your ideas to life with coloured pencils, crayons, paints, and other creative essentials for art projects and colouring activities.',
                'products' => [
                    ['name' => 'Coloured Pencils', 'price' => '8.90', 'stock' => 70, 'unit' => 'set', 'description' => 'Coloured pencils for colouring, illustrating, and adding detail to artwork and school projects.'],
                    ['name' => 'Crayons', 'price' => '5.50', 'stock' => 90, 'unit' => 'set', 'description' => 'Crayons for colouring pictures, making drawings, and exploring creative activities.'],
                    ['name' => 'Watercolour Set', 'price' => '12.90', 'stock' => 35, 'unit' => 'set', 'description' => 'Watercolour paints for creating washes, blending colours, and exploring painting techniques on suitable paper.'],
                    ['name' => 'Oil Pastels', 'price' => '9.90', 'stock' => 50, 'unit' => 'set', 'description' => 'Smooth, bold oil pastels for blending colours in art lessons and colouring projects.'],
                    ['name' => 'A4 Drawing Block', 'price' => '6.50', 'stock' => 55, 'unit' => 'item', 'description' => 'Thick A4 drawing paper for sketching, pastels, and light watercolour work.'],
                    ['name' => 'Paint Brush Set', 'price' => '7.90', 'stock' => 3, 'unit' => 'set', 'description' => 'Brushes in several sizes for watercolour and poster colour painting.'],
                ],
            ],
            'Storage & Organisers' => [
                'description' => 'Keep stationery tidy and within reach with pencil cases, desk organisers, and storage solutions for your study or workspace.',
                'products' => [
                    ['name' => 'Pencil Case', 'price' => '9.90', 'stock' => 75, 'unit' => 'item', 'description' => 'Keep pens, pencils, erasers, and other small stationery items together for easy storage and carrying.'],
                    ['name' => 'Desk Organiser', 'price' => '19.90', 'stock' => 20, 'unit' => 'item', 'description' => 'Multi-compartment organiser that keeps pens, sticky notes, and small supplies tidy on your desk.'],
                    ['name' => 'Pen Holder Cup', 'price' => '5.90', 'stock' => 45, 'unit' => 'item', 'description' => 'Simple holder that keeps pens, pencils, and rulers upright and within reach.'],
                    ['name' => 'A4 Storage Box', 'price' => '14.90', 'stock' => 0, 'unit' => 'item', 'description' => 'Lidded storage box sized for A4 papers, past-year exam papers, and art supplies.'],
                ],
            ],
            'Books' => [
                'description' => 'Workbooks, revision guides, and dictionaries to support learning at school and at home.',
                'products' => [
                    ['name' => 'Mathematics Workbook', 'price' => '15.90', 'stock' => 20, 'unit' => 'item', 'description' => 'Practice exercises for primary school students.'],
                    ['name' => 'Science Workbook', 'price' => '15.90', 'stock' => 18, 'unit' => 'item', 'description' => 'Topic-by-topic science exercises with diagrams and revision questions for primary school students.'],
                    ['name' => 'English Grammar Practice Book', 'price' => '12.90', 'stock' => 30, 'unit' => 'item', 'description' => 'Short grammar lessons with practice exercises to build accurate everyday English.'],
                    ['name' => 'Bahasa Melayu Karangan Guide', 'price' => '13.90', 'stock' => 5, 'unit' => 'item', 'description' => 'Essay-writing guide with sample karangan, useful phrases, and practice topics.'],
                    ['name' => 'Pocket English–Malay Dictionary', 'price' => '18.90', 'stock' => 25, 'unit' => 'item', 'description' => 'Compact bilingual dictionary for quick word lookups in class and at home.'],
                ],
            ],
        ];
    }
}
