<?php
/**
 * Rate post class
 */

namespace App\Posts;

use App\Post;
use App\Rating;
use Illuminate\Support\Facades\DB;

class PostRater
{
    /**
     * @param Post $post
     * @param int  $rating
     *
     * @return Post
     */
    public static function rate(Post $post, int $rating): Post
    {
        // use transaction for several inquiries at an estimation of the post
        DB::beginTransaction();

        // create rating entity
        $vote = new Rating();
        $vote->post_id = $post->id;
        $vote->value = $rating;
        $vote->save();

        // calculate average rating of the post
        $post->average_rating = Rating::where('post_id', $post->id)->avg('value');
        $post->save();

        DB::commit();

        return $post;
    }
}