<?php

namespace Database\Seeders;

use App\Models\News;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        News::create([
            'title' => 'How To Learn English - Seminar',
            'image' => null,
            'description' => 'The "How to Learn English" seminar is an interactive session designed to help students and English learners discover effective strategies for mastering English language skills and building confidence in communication.',
            'date' => now()->subDays(5),
            'created_by' => null,
            'likes' => 3,
            'views' => 88,
        ]);

        News::create([
            'title' => 'English Speaking Tips',
            'image' => null,
            'description' => 'Learn practical tips and tricks to improve your English speaking skills. This guide covers pronunciation, fluency, and common phrases used in daily conversations.',
            'date' => now()->subDays(10),
            'created_by' => null,
            'likes' => 5,
            'views' => 120,
        ]);

        News::create([
            'title' => 'TOEFL Preparation Guide',
            'image' => null,
            'description' => 'A comprehensive guide to preparing for the TOEFL exam. Includes study strategies, sample questions, and expert advice to help you achieve your target score.',
            'date' => now()->subDays(15),
            'created_by' => null,
            'likes' => 7,
            'views' => 156,
        ]);
    }
}
