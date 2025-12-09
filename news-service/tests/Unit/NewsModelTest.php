<?php

namespace Tests\Unit;

use App\Models\News;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: News model can be created
     */
    public function test_news_can_be_created()
    {
        $news = News::create([
            'title' => 'Test News',
            'description' => 'Test description',
            'date' => now(),
        ]);

        $this->assertNotNull($news->id);
        $this->assertEquals('Test News', $news->title);
    }

    /**
     * Test: News creator relationship
     */
    public function test_news_belongs_to_user()
    {
        $user = User::factory()->create();
        $news = News::create([
            'title' => 'Test News',
            'description' => 'Test description',
            'date' => now(),
            'created_by' => $user->id,
        ]);

        $this->assertEquals($user->id, $news->creator->id);
    }

    /**
     * Test: News likes relationship
     */
    public function test_news_many_to_many_with_users_for_likes()
    {
        $news = News::create([
            'title' => 'Test News',
            'description' => 'Test description',
            'date' => now(),
        ]);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $news->likedUsers()->attach($user1->id);
        $news->likedUsers()->attach($user2->id);

        $this->assertEquals(2, $news->likedUsers()->count());
        $this->assertTrue($news->likedUsers()->where('user_id', $user1->id)->exists());
        $this->assertTrue($news->likedUsers()->where('user_id', $user2->id)->exists());
    }

    /**
     * Test: News increment likes
     */
    public function test_news_likes_increment()
    {
        $news = News::create([
            'title' => 'Test News',
            'description' => 'Test description',
            'date' => now(),
            'likes' => 0,
        ]);

        $news->increment('likes');

        $this->assertEquals(1, $news->likes);
    }

    /**
     * Test: News increment views
     */
    public function test_news_views_increment()
    {
        $news = News::create([
            'title' => 'Test News',
            'description' => 'Test description',
            'date' => now(),
            'views' => 0,
        ]);

        $news->increment('views');
        $news->increment('views');

        $this->assertEquals(2, $news->views);
    }

    /**
     * Test: News date casting
     */
    public function test_news_date_is_cast_to_datetime()
    {
        $date = now();
        $news = News::create([
            'title' => 'Test News',
            'description' => 'Test description',
            'date' => $date,
        ]);

        $this->assertInstanceOf('Illuminate\Support\Carbon', $news->date);
    }

    /**
     * Test: News fillable attributes
     */
    public function test_news_fillable_attributes()
    {
        $fillable = ['title', 'image', 'description', 'date', 'created_by', 'likes', 'views'];

        foreach ($fillable as $attribute) {
            $this->assertContains($attribute, (new News())->getFillable());
        }
    }

    /**
     * Test: News can be deleted
     */
    public function test_news_can_be_deleted()
    {
        $news = News::create([
            'title' => 'Test News',
            'description' => 'Test description',
            'date' => now(),
        ]);

        $newsId = $news->id;
        $news->delete();

        $this->assertNull(News::find($newsId));
    }

    /**
     * Test: News with same user liking multiple times - relationship
     */
    public function test_news_user_like_uniqueness()
    {
        $news = News::create([
            'title' => 'Test News',
            'description' => 'Test description',
            'date' => now(),
        ]);

        $user = User::factory()->create();

        // Attach once
        $news->likedUsers()->attach($user->id);
        $count1 = $news->likedUsers()->count();

        // Try to attach again (should fail due to unique constraint)
        try {
            $news->likedUsers()->attach($user->id);
        } catch (\Exception $e) {
            // Expected - unique constraint violation
        }

        $count2 = $news->likedUsers()->count();

        // Count should be the same (still only 1 like)
        $this->assertEquals($count1, $count2);
    }
}
