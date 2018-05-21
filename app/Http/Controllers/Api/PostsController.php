<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Post;
use App\Posts\PostCreater;
use App\Posts\PostRater;
use App\Http\Requests\StorePost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

    }

    /**
     * Create a new resource.
     *
     * @param  StorePost  $request
     * @return \Illuminate\Http\Response
     */
    public function create(StorePost $request)
    {
        $validated = $request->validated();
        $post = PostCreater::create( $validated );
        return response()->json( [
            'title' => $post->title,
            'body'  => $post->body,
            'ip'    => $post->ip,
        ] );
    }

    /**
     * Rate the post.
     *
     * @param  \App\Post  $post
     * @param  integer    $rating
     * @return \Illuminate\Http\Response
     */
    public function rate(Post $post, int $rating)
    {
        // validate value
        if ( $rating < 1 || $rating > 5 ) {
            return response()->json( [
                'error' => 'The value of the evaluation should be between 1 and 5',
            ], 422 );
        }

        $post = PostRater::rate( $post, $rating );

        return response()->json( [
            'rating' => (float) $post->average_rating,
        ] );
    }

    /**
     * Get top list of the posts.
     *
     * @return \Illuminate\Http\Response
     */
    public function getTopList( Request $request )
    {
        $limit = $request->get('limit', 50);
        $list = Post::select( [ 'title', 'body' ] )
                     ->orderBy( 'average_rating', 'desc' )
                     ->limit( $limit )
                     ->get();
        return response()->json( [
            'data' => $list,
        ] );
    }

    /**
     * Get list of the ip.
     *
     * @return \Illuminate\Http\Response
     */
    public function getIpList()
    {
        // get list of users and ip from which several authors write
        $list = Post::select( [ 'ip', 'users.login' => 'login' ] )
                    ->join( 'users', 'users.id', '=', 'posts.user_id' )
                    ->whereRaw( 'ip IN (select "ip" from "posts" group by "ip" having COUNT(DISTINCT user_id) > 1)' )
                    ->groupBy( [ 'ip', 'login' ] )
                    ->orderBy( 'ip', 'login' )
                    ->get();

        // Group by ip
        $result = [];

        foreach ( $list as $item ) {
            if ( !isset( $result[ $item->ip ] )) {
                $result[ $item->ip ] = new \StdClass();
                $result[ $item->ip ]->ip = $item->ip;
                $result[ $item->ip ]->authors = [];
            }

            $result[ $item->ip ]->authors[] = $item->login;
        }

        return response()->json( [
            'data' => array_values( $result ),
        ] );
    }
}
