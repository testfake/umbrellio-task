<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Posts\PostCreater;
use App\Posts\PostRater;
use Illuminate\Support\Facades\DB;
use App\Post;

class FillTestData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'develop:filldata
                                {--truncate_tables : Truncate the database before filling data }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Filling the database with test data';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ( \App::environment() === 'production' ) {
            $this->error( 'I\'m on prod!' );
            return;
        }

        // Clear tables before filling
        if ( $this->option( 'truncate_tables' ) ) {
            DB::statement('TRUNCATE ratings');
            DB::statement('TRUNCATE posts CASCADE');
            DB::statement('TRUNCATE users CASCADE');
        }

        // Generate random ip list
        $ip_list = [];

        for ( $i = 0; $i < 50; $i++ ) {
            $ip_list[] = mt_rand( 0, 255 ) . "." . mt_rand( 0, 255 ) . "." . mt_rand( 0, 255 ) . "." . mt_rand( 0, 255 );
        }

        // Create users & posts
        for ( $i = 0; $i < 100; $i++ ) {
            // generate user random name
            $author = $this->generateRandomString();

            // Greate posts
            for ( $j = 0; $j < 2000; $j++ ) {
                $post = PostCreater::create( [
                    'title'  => $this->generateRandomString(),
                    'body'   => $this->generateRandomString( 10, 100, TRUE ),
                    'author' => $author,
                    'ip'     => $ip_list[ array_rand( $ip_list ) ]
                ] );

                // Rate same posts
                if ( rand( 0, 99 ) == 1 ) {
                    $this->ratePost( $post );
                }
            }
        }

        $this->info( 'Done.' );
    }

    /**
     * Generate random string
     *
     * @param int  $minLength
     * @param int  $maxLength
     * @param bool $useSymbols
     *
     * @return string
     */
    private function generateRandomString( $minLength = 3, $maxLength = 10, $useSymbols = FALSE ): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        if ( $useSymbols ) {
            $chars .= '     .';
        }

        $length = rand( $minLength, $maxLength );
        $last_pos_char = strlen($chars) - 1;
        $string = '';

        for ($i = 0; $i < $length; $i++) {
            $string .= $chars[ rand( 0, $last_pos_char ) ];
        }

        return $string;
    }

    /**
     * Rate post
     *
     * @param Post $post
     */
    private function ratePost( Post $post )
    {
        for ($i = 0; $i < rand( 1, 5 ); $i++) {
            PostRater::rate( $post, rand( 1, 5 ) );
        }
    }
}
