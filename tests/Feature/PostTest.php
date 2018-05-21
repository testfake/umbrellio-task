<?php

namespace Tests\Feature;

use Faker\Factory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Post;
use App\User;
use App\Rating;

class PostTest extends TestCase
{
    /**
     * Create post.
     *
     * @return void
     */
    public function testCreatePost()
    {
        // Send invalid data
        $response = $this->json('POST', '/api/v1/posts', ['title' => 'test', 'ip' => '915.111.22.45']);
        $response
            ->assertStatus(422)
            ->assertJson([
                'message' => "The given data was invalid.",
                'errors'  => [
                    'body'   => [ "The body field is required." ],
                    'author' => ["The author field is required."],
                    'ip'     => ["The ip must be a valid IP address."]
                ]
            ]);

        // Send valid data
        $response = $this->json('POST', '/api/v1/posts', [
            'title'  => 'test',
            'body'   => 'long text',
            'author' => 'test author',
            'ip'     => '111.222.33.45'
        ]);
        // Get post from database
        $post = Post::where( 'title', 'test' )
                          ->where( 'ip', '111.222.33.45' )
                          ->first();
        $this->assertNotNull( $post );
        // Check new user
        $this->assertNotNull( User::find( $post->user_id ) );
        $response
            ->assertStatus(200)
            ->assertJson([
                'title' => $post->title,
                'body'  => $post->body,
                'ip'    => $post->ip
            ]);
        $post->delete();
    }

    /**
     * Rate post.
     *
     * @return void
     */
    public function testRatePost()
    {
        // create fake post
        $user = factory(User::class)->create();
        $post = factory(Post::class)->create([
            'user_id' => $user->id
        ]);

        // Send invalid rating
        $response = $this->json( 'GET', '/api/v1/posts/' . $post->id . '/rate/0' );
        $response
            ->assertStatus(422)
            ->assertJson([
                'error' => "The value of the evaluation should be between 1 and 5",
            ]);

        // Request
        $response = $this->json( 'GET', '/api/v1/posts/' . $post->id . '/rate/3' );
        $response
            ->assertStatus(200)
            ->assertJson([
                'rating' => 3,
            ]);
        $post = $post->fresh();
        $this->assertEquals( 3, $post->average_rating );
        $this->assertEquals( 1, count( Rating::where( 'post_id', $post->id )->get() ) );

        // Rate again
        $response = $this->json( 'GET', '/api/v1/posts/' . $post->id . '/rate/5' );
        $response
            ->assertStatus(200)
            ->assertJson([
                'rating' => 4,
            ]);
        $post = $post->fresh();
        $this->assertEquals( 4, $post->average_rating );
        $this->assertEquals( 2, count( Rating::where( 'post_id', $post->id )->get() ) );

        // delete test data
        $user->delete();
        $post->delete();
    }

    /**
     * Test posts top list.
     *
     * @return void
     */
    public function testTopList()
    {
        // create fake post
        $user = factory(User::class)->create();
        $post = factory(Post::class)->create([
            'user_id' => $user->id
        ]);

        // check request
        $response = $this->json( 'GET', '/api/v1/posts/top', [ 'limit' => 3 ] );
        $response
            ->assertStatus(200)
            ->assertJsonStructure( [
                "data" => [
                    [ 'title', 'body' ]
                ]
            ] );

        // delete test data
        $user->delete();
        $post->delete();
    }

    /**
     * Test ip list.
     *
     * @return void
     */
    public function testIpList()
    {
        // create fake users
        $users = factory(User::class, 3)->create();
        // create posts with same ip
        $test_ip    = '123.45.67.89';
        Post::where( 'ip', $test_ip )->delete();
        $test_users = [];

        foreach ( $users as $user ) {
            factory(Post::class)->create([
                'user_id' => $user->id,
                'ip'      => $test_ip
            ]);
            $test_users[] = $user->login;
        }

        sort( $test_users );
        // check request
        $response = $this->json( 'GET', '/api/v1/posts/ip' );
        $response
            ->assertStatus(200)
            ->assertJsonFragment( [
                'ip'      => $test_ip,
                'authors' => $test_users
            ] );
        // delete test data
        Post::where( 'ip', $test_ip )->delete();
        User::whereIn( 'login', $test_users )->delete();
    }
}
