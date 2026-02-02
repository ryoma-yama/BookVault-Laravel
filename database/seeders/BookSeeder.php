<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\BookCopy;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample books data with authors
        $books = [
            [
                'isbn_13' => '9784798179216',
                'title' => 'リーダブルコード',
                'publisher' => 'オライリージャパン',
                'published_date' => '2012-06-23',
                'description' => '読みやすいコードを書くための実践的なテクニックを解説した書籍。変数名の付け方、コメントの書き方、関数の分割など、すぐに実践できるノウハウが満載。',
                'authors' => ['Dustin Boswell', 'Trevor Foucher'],
                'tags' => ['programming', 'best practices'],
            ],
            [
                'isbn_13' => '9784873119038',
                'title' => 'Clean Code',
                'publisher' => 'アスキー・メディアワークス',
                'published_date' => '2009-01-01',
                'description' => 'ソフトウェア開発の名著。保守性の高い美しいコードを書くための原則とパターンを詳しく解説。',
                'authors' => ['Robert C. Martin'],
                'tags' => ['programming', 'best practices'],
            ],
            [
                'isbn_13' => '9784297124021',
                'title' => 'Laravel入門',
                'publisher' => '技術評論社',
                'published_date' => '2021-03-15',
                'description' => 'PHPフレームワーク「Laravel」の入門書。基礎から実践的な機能まで丁寧に解説。',
                'authors' => ['掌田津耶乃'],
                'tags' => ['php', 'laravel', 'web development'],
            ],
        ];

        foreach ($books as $bookData) {
            $authorNames = $bookData['authors'];
            $tagNames = $bookData['tags'] ?? [];
            unset($bookData['authors']);
            unset($bookData['tags']);

            $book = Book::create($bookData);

            // Attach authors
            foreach ($authorNames as $authorName) {
                $author = Author::firstOrCreate(['name' => $authorName]);
                $book->authors()->attach($author);
            }

            // Attach tags
            foreach ($tagNames as $tagName) {
                $tag = \App\Models\Tag::firstOrCreate(['name' => $tagName]);
                $book->tags()->attach($tag);
            }

            // Create 2-5 copies for each book
            $copyCount = rand(2, 5);

            BookCopy::factory()
                ->count($copyCount)
                ->create(['book_id' => $book->id]);

            // Mark some copies as discarded (20% chance per copy)
            $book->copies->each(function ($copy) {
                if (rand(1, 5) === 1) {
                    $copy->update([
                        'discarded_date' => now()->subDays(rand(1, 365)),
                    ]);
                }
            });
        }
    }
}
