<?php
/**
 * Post factory
 */

namespace App\Posts;

use App\Post;
use App\User;


class PostCreater
{
    /**
     * @param $data
     *
     * @return Post
     */
    public static function create($data): Post
    {
        $post = new Post();

        $post->title = $data[ 'title' ];
        $post->body = $data[ 'body' ];
        // add user or create if not exist
        $user = User::firstOrCreate(['login' => $data[ 'author' ]]);
        $post->user_id = $user->id;
        $post->ip = $data[ 'ip' ];
        $post->save();

        return $post;
    }
}