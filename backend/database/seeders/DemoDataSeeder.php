<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Skill;
use App\Models\Category;
use App\Models\Exchange;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar usuários de demonstração
        $users = [
            [
                'name' => 'João Silva',
                'email' => 'joao@skillswap.com',
                'password' => Hash::make('password123'),
                'bio' => 'Desenvolvedor Full Stack com 5 anos de experiência em Laravel e Vue.js',
                'location' => 'São Paulo, SP',
                'rating' => 4.8,
                'total_exchanges' => 12
            ],
            [
                'name' => 'Maria Santos',
                'email' => 'maria@skillswap.com',
                'password' => Hash::make('password123'),
                'bio' => 'Designer UX/UI apaixonada por criar experiências incríveis',
                'location' => 'Rio de Janeiro, RJ',
                'rating' => 4.9,
                'total_exchanges' => 8
            ],
            [
                'name' => 'Carlos Oliveira',
                'email' => 'carlos@skillswap.com',
                'password' => Hash::make('password123'),
                'bio' => 'Professor de inglês com certificação internacional',
                'location' => 'Belo Horizonte, MG',
                'rating' => 4.7,
                'total_exchanges' => 15
            ]
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }

        // Buscar categorias existentes
        $programmingCategory = Category::where('name', 'Programação')->first();
        $designCategory = Category::where('name', 'Design')->first();
        $languageCategory = Category::where('name', 'Idiomas')->first();
        $musicCategory = Category::where('name', 'Música')->first();
        $cookingCategory = Category::where('name', 'Culinária')->first();

        // Criar habilidades de demonstração
        $skills = [
            // Habilidades do João (Programação)
            [
                'user_id' => 1,
                'category_id' => $programmingCategory->id,
                'title' => 'Desenvolvimento Laravel',
                'description' => 'Ensino desenvolvimento web com Laravel, desde básico até avançado. Incluindo APIs, autenticação, e boas práticas.',
                'level' => 'expert',
                'tags' => ['Laravel', 'PHP', 'API', 'Backend']
            ],
            [
                'user_id' => 1,
                'category_id' => $programmingCategory->id,
                'title' => 'Vue.js Frontend',
                'description' => 'Desenvolvimento de interfaces modernas com Vue.js 3, Composition API, Pinia e TypeScript.',
                'level' => 'advanced',
                'tags' => ['Vue.js', 'JavaScript', 'Frontend', 'TypeScript']
            ],

            // Habilidades da Maria (Design)
            [
                'user_id' => 2,
                'category_id' => $designCategory->id,
                'title' => 'Design UX/UI',
                'description' => 'Criação de interfaces intuitivas e experiências de usuário excepcionais usando Figma e Adobe XD.',
                'level' => 'expert',
                'tags' => ['UX', 'UI', 'Figma', 'Adobe XD']
            ],
            [
                'user_id' => 2,
                'category_id' => $designCategory->id,
                'title' => 'Prototipagem',
                'description' => 'Criação de protótipos interativos para validação de ideias e testes com usuários.',
                'level' => 'advanced',
                'tags' => ['Prototipagem', 'Testes', 'Validação']
            ],

            // Habilidades do Carlos (Idiomas)
            [
                'user_id' => 3,
                'category_id' => $languageCategory->id,
                'title' => 'Inglês Conversação',
                'description' => 'Aulas de conversação em inglês para melhorar fluência e confiança na comunicação.',
                'level' => 'expert',
                'tags' => ['Inglês', 'Conversação', 'Fluência']
            ],
            [
                'user_id' => 3,
                'category_id' => $languageCategory->id,
                'title' => 'Preparação TOEFL',
                'description' => 'Preparação completa para o exame TOEFL com material atualizado e técnicas de prova.',
                'level' => 'expert',
                'tags' => ['TOEFL', 'Certificação', 'Preparação']
            ],

            // Habilidades extras para variedade
            [
                'user_id' => 1,
                'category_id' => $musicCategory->id,
                'title' => 'Violão Iniciante',
                'description' => 'Aulas básicas de violão, acordes fundamentais e primeiras músicas.',
                'level' => 'intermediate',
                'tags' => ['Violão', 'Música', 'Acordes']
            ],
            [
                'user_id' => 2,
                'category_id' => $cookingCategory->id,
                'title' => 'Culinária Italiana',
                'description' => 'Receitas tradicionais italianas, massas caseiras e molhos autênticos.',
                'level' => 'intermediate',
                'tags' => ['Culinária', 'Italiana', 'Massas']
            ]
        ];

        foreach ($skills as $skillData) {
            Skill::create($skillData);
        }

        // Criar algumas trocas de exemplo (concluídas)
        $exchanges = [
            [
                'initiator_id' => 1,
                'receiver_id' => 2,
                'offered_skill_id' => 1, // Laravel
                'requested_skill_id' => 3, // Design UX/UI
                'status' => Exchange::STATUS_COMPLETED,
                'message' => 'Gostaria de aprender UX/UI em troca de ensinar Laravel',
                'completed_at' => now()->subDays(10),
                'rating_initiator' => 5,
                'rating_receiver' => 5,
            ],
            [
                'initiator_id' => 2,
                'receiver_id' => 3,
                'offered_skill_id' => 3, // Design UX/UI
                'requested_skill_id' => 5, // Inglês
                'status' => Exchange::STATUS_COMPLETED,
                'message' => 'Preciso melhorar meu inglês para trabalhar com clientes internacionais',
                'completed_at' => now()->subDays(5),
                'rating_initiator' => 5,
                'rating_receiver' => 4,
            ],
            [
                'initiator_id' => 3,
                'receiver_id' => 1,
                'offered_skill_id' => 5, // Inglês
                'requested_skill_id' => 2, // Vue.js
                'status' => Exchange::STATUS_COMPLETED,
                'message' => 'Quero aprender Vue.js para criar meu portfólio online',
                'completed_at' => now()->subDays(3),
                'rating_initiator' => 5,
                'rating_receiver' => 5,
            ],
            [
                'initiator_id' => 1,
                'receiver_id' => 3,
                'offered_skill_id' => 7, // Violão
                'requested_skill_id' => 6, // TOEFL
                'status' => Exchange::STATUS_COMPLETED,
                'message' => 'Preciso de preparação para TOEFL, posso ensinar violão',
                'completed_at' => now()->subDays(1),
                'rating_initiator' => 4,
                'rating_receiver' => 5,
            ],
        ];

        foreach ($exchanges as $exchangeData) {
            Exchange::create($exchangeData);
        }
    }
}
