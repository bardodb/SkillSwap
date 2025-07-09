<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Programação',
                'description' => 'Linguagens de programação, frameworks e desenvolvimento de software',
                'icon' => 'code',
                'color' => '#3B82F6'
            ],
            [
                'name' => 'Design',
                'description' => 'Design gráfico, UI/UX, ilustração e design digital',
                'icon' => 'palette',
                'color' => '#EC4899'
            ],
            [
                'name' => 'Idiomas',
                'description' => 'Ensino e aprendizado de idiomas',
                'icon' => 'globe',
                'color' => '#10B981'
            ],
            [
                'name' => 'Música',
                'description' => 'Instrumentos musicais, canto, produção musical',
                'icon' => 'music',
                'color' => '#F59E0B'
            ],
            [
                'name' => 'Culinária',
                'description' => 'Receitas, técnicas culinárias e gastronomia',
                'icon' => 'chef-hat',
                'color' => '#EF4444'
            ],
            [
                'name' => 'Esportes',
                'description' => 'Atividades físicas, esportes e fitness',
                'icon' => 'trophy',
                'color' => '#8B5CF6'
            ],
            [
                'name' => 'Artesanato',
                'description' => 'Trabalhos manuais, DIY e artes aplicadas',
                'icon' => 'scissors',
                'color' => '#F97316'
            ],
            [
                'name' => 'Negócios',
                'description' => 'Empreendedorismo, marketing, vendas e gestão',
                'icon' => 'briefcase',
                'color' => '#06B6D4'
            ],
            [
                'name' => 'Fotografia',
                'description' => 'Técnicas fotográficas, edição de imagens',
                'icon' => 'camera',
                'color' => '#84CC16'
            ],
            [
                'name' => 'Educação',
                'description' => 'Tutoria, ensino e métodos pedagógicos',
                'icon' => 'book',
                'color' => '#6366F1'
            ]
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
