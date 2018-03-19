<?php

namespace App\Http\Controllers;

use App\Services\Backblaze\Backblaze;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $backblazeService = new Backblaze();
        //$backblazeService->getMasterToken();
      // var_dump( $backblazeService->list_buckets() );
        //var_dump( $backblazeService->list_file_names('d5a37e7ae1dc2eee612f0011') );
        return ($backblazeService->download_file_by_name('qqq 001.jpg'));
    }
}
