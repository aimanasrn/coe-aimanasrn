<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Writing Instruments',
                'description' => 'Essential tools for writing, drawing, and highlighting, including pens, pencils, markers, and highlighters. Ideal for school, office, and everyday use.',
            ],
            [
                'name' => 'Notebooks & Paper',
                'description' => 'Notebooks, exercise books, sticky notes, and paper for taking notes, completing assignments, printing, and organising ideas. Perfect for school, office, and everyday use.',
            ],
            [
                'name' => 'Erasers & Sharpeners',
                'description' => 'Keep your work neat and your pencils sharp with erasers and sharpeners for school, office, and everyday use. Ideal for correcting pencil marks and maintaining precise pencil tips.',
            ],
            [
                'name' => 'Correction Supplies',
                'description' => 'Correct writing mistakes neatly with correction tape and fluid, keeping your notes and documents clean and easy to read.',
            ],
            [
                'name' => 'Geometry & Measuring Tools',
                'description' => 'Measure accurately and draw precise shapes with rulers, protractors, compasses, and geometry sets for school and technical work.',
            ],
            [
                'name' => 'Files & Folders',
                'description' => 'Keep documents organised and protected with files, folders, and binders for school, office, and home use.',
            ],
            [
                'name' => 'Office Accessories',
                'description' => 'Everyday essentials such as staplers, staples, paper clips, and binder clips to keep your paperwork secure and workspace organised.',
            ],
            [
                'name' => 'Cutting & Adhesives',
                'description' => 'Cut, stick, and assemble with scissors, glue, and adhesive tapes for school projects, crafts, and everyday tasks.',
            ],
            [
                'name' => 'Art & Colouring Supplies',
                'description' => 'Bring your ideas to life with coloured pencils, crayons, paints, and other creative essentials for art projects and colouring activities.',
            ],
            [
                'name' => 'Storage & Organisers',
                'description' => 'Keep stationery tidy and within reach with pencil cases, desk organisers, and storage solutions for your study or workspace.',
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name']],
                ['description' => $category['description']],
            );
        }
    }
}