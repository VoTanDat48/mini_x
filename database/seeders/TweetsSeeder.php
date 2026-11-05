<?php

namespace Database\Seeders;

use App\Models\Bookmark;
use App\Models\Like;
use App\Models\Retweet;
use App\Models\Tweet;
use App\Models\User;
use App\Notifications\AppActivityNotification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TweetsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Create Curated Seed Users
        $demo = User::firstOrCreate(
            ['email' => 'demo@example.com'],
            [
                'name' => 'Demo User',
                'username' => 'demouser',
                'password' => Hash::make('password'),
                'bio' => "Full-stack developer exploring Laravel 9, Vue 3, and Inertia.js.\nBuilding modern web applications.",
                'location' => 'San Francisco, CA',
                'website' => 'https://github.com',
                'avatar_path' => 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=200&h=200&fit=crop',
                'banner_path' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=1200&h=400&fit=crop',
            ]
        );

        $taylor = User::firstOrCreate(
            ['email' => 'taylor@laravel.com'],
            [
                'name' => 'Taylor Otwell',
                'username' => 'taylorotwell',
                'password' => Hash::make('password'),
                'bio' => 'Creator of @laravelphp. Loving PHP, Vue.js, and elegant full-stack architecture.',
                'location' => 'Arkansas, USA',
                'website' => 'https://laravel.com',
                'avatar_path' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200&h=200&fit=crop',
                'banner_path' => 'https://images.unsplash.com/photo-1579546929518-9e396f3cc809?w=1200&h=400&fit=crop',
            ]
        );

        $evan = User::firstOrCreate(
            ['email' => 'evan@vuejs.org'],
            [
                'name' => 'Evan You',
                'username' => 'youyuxi',
                'password' => Hash::make('password'),
                'bio' => 'Creator of @vuejs & @vitejs. Passionate about developer tooling and reactive web UI.',
                'location' => 'Singapore',
                'website' => 'https://evanyou.me',
                'avatar_path' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=200&h=200&fit=crop',
                'banner_path' => 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=1200&h=400&fit=crop',
            ]
        );

        $elon = User::firstOrCreate(
            ['email' => 'elon@x.com'],
            [
                'name' => 'Elon Musk',
                'username' => 'elonmusk',
                'password' => Hash::make('password'),
                'bio' => 'Technoking of Tesla, owner of X.',
                'location' => 'Austin, TX',
                'website' => 'https://x.com',
                'avatar_path' => 'https://images.unsplash.com/photo-1570295999919-56ceb5ecca61?w=200&h=200&fit=crop',
                'banner_path' => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=1200&h=400&fit=crop',
            ]
        );

        $john = User::firstOrCreate(
            ['email' => 'john@example.com'],
            [
                'name' => 'John Weeks',
                'username' => 'johnweeksdev',
                'password' => Hash::make('password'),
                'bio' => 'Software Engineer & Tech Educator building full-stack software walkthroughs.',
                'location' => 'London, UK',
                'website' => 'https://youtube.com',
                'avatar_path' => 'https://images.unsplash.com/photo-1492562080023-ab3db95bfbce?w=200&h=200&fit=crop',
                'banner_path' => 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?w=1200&h=400&fit=crop',
            ]
        );

        // 2. Establish Social Follow Connections
        $demo->following()->syncWithoutDetaching([$taylor->id, $evan->id, $elon->id, $john->id]);
        $taylor->following()->syncWithoutDetaching([$evan->id, $demo->id]);
        $evan->following()->syncWithoutDetaching([$taylor->id, $demo->id]);
        $elon->following()->syncWithoutDetaching([$taylor->id]);
        $john->following()->syncWithoutDetaching([$taylor->id, $evan->id, $demo->id]);

        // 3. Create Realistic Seed Tweets
        $taylorTweet = $taylor->tweets()->create([
            'tweet' => "Laravel 9 with Inertia.js and Vue 3 is such a joy to work with. You get the productivity of a classic monolith combined with the snappy responsiveness of a modern single-page app! #Laravel #VueJS",
            'file' => null,
            'is_video' => false,
        ]);

        $evanTweet = $evan->tweets()->create([
            'tweet' => "The Vue 3 <script setup> syntax alongside Vite has fundamentally transformed the developer experience. Fast HMR, concise reactive primitives, and zero bundle bloat.",
            'file' => '/pics/Code.png',
            'is_video' => false,
        ]);

        $elonTweet = $elon->tweets()->create([
            'tweet' => "Clean dark theme, real-time activity notifications, threaded replies, and instant media previews. Engineering at high velocity.",
            'file' => null,
            'is_video' => false,
        ]);

        $johnTweet = $john->tweets()->create([
            'tweet' => "We went rock climbing this weekend! Climbing is way more fun than exercising on gym equipment. It works both mind and body. Enjoy life outside the code editor!",
            'file' => '/videos/Sportsman.mp4',
            'is_video' => true,
        ]);

        $demoTweet = $demo->tweets()->create([
            'tweet' => "Welcome to our Twitter/X clone! Everything here is powered by real database queries, policy authorization, FormRequest validations, and automated feature tests.",
            'file' => null,
            'is_video' => false,
        ]);

        // 4. Create Threaded Reply Chain
        $reply1 = $evan->tweets()->create([
            'parent_id' => $taylorTweet->id,
            'tweet' => "100% agreed Taylor! Not having to write and maintain duplicate API contracts is a massive superpower for small teams.",
        ]);

        $reply2 = $taylor->tweets()->create([
            'parent_id' => $taylorTweet->id,
            'tweet' => "Exactly Evan. Typed controllers passing props directly to Vue components is the sweet spot of web dev.",
        ]);

        $reply3 = $demo->tweets()->create([
            'parent_id' => $taylorTweet->id,
            'tweet' => "This architecture has completely transformed our product shipping speed. Huge appreciation to both of you!",
        ]);

        // 5. Create Quoted Tweet
        $taylorQuote = $taylor->tweets()->create([
            'quote_of_id' => $evanTweet->id,
            'tweet' => "Couldn't agree more with @youyuxi on Vite. It is by far our favorite build tool across the entire Laravel ecosystem.",
        ]);

        // 6. Populate Realistic Interactions (Likes, Retweets, Bookmarks)
        // Likes on Taylor's main tweet
        Like::firstOrCreate(['user_id' => $demo->id, 'tweet_id' => $taylorTweet->id]);
        Like::firstOrCreate(['user_id' => $evan->id, 'tweet_id' => $taylorTweet->id]);
        Like::firstOrCreate(['user_id' => $elon->id, 'tweet_id' => $taylorTweet->id]);
        Like::firstOrCreate(['user_id' => $john->id, 'tweet_id' => $taylorTweet->id]);

        // Likes on Evan's tweet
        Like::firstOrCreate(['user_id' => $demo->id, 'tweet_id' => $evanTweet->id]);
        Like::firstOrCreate(['user_id' => $taylor->id, 'tweet_id' => $evanTweet->id]);

        // Likes on Demo's tweet
        Like::firstOrCreate(['user_id' => $taylor->id, 'tweet_id' => $demoTweet->id]);
        Like::firstOrCreate(['user_id' => $evan->id, 'tweet_id' => $demoTweet->id]);
        Like::firstOrCreate(['user_id' => $elon->id, 'tweet_id' => $demoTweet->id]);

        // Retweets
        Retweet::firstOrCreate(['user_id' => $demo->id, 'tweet_id' => $taylorTweet->id]);
        Retweet::firstOrCreate(['user_id' => $taylor->id, 'tweet_id' => $evanTweet->id]);
        Retweet::firstOrCreate(['user_id' => $elon->id, 'tweet_id' => $demoTweet->id]);

        // Bookmarks for Demo User
        Bookmark::firstOrCreate(['user_id' => $demo->id, 'tweet_id' => $taylorTweet->id]);
        Bookmark::firstOrCreate(['user_id' => $demo->id, 'tweet_id' => $evanTweet->id]);

        // 7. Seed Activity Notifications for Demo User
        $demo->notify(new AppActivityNotification($taylor, 'like', $demoTweet));
        $demo->notify(new AppActivityNotification($evan, 'follow'));
        $demo->notify(new AppActivityNotification($elon, 'retweet', $demoTweet));
        $demo->notify(new AppActivityNotification($john, 'reply', $reply3));
    }
}
