<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Torrent;
use App\Models\SubCategory;
use App\Models\User;
use Inertia\Inertia;
class UserController extends Controller
{
    //
    public function index($user, $page = 1){

        $data['torrents'] = Torrent::with(['subcategory'])
                    ->where('uploader',"/user/$user/")
                    ->orderByDesc('approved_at')
                    ->paginate(10, ['*'], 'page', $page);

        $data['info'] = User::where('username',$user)->first();
        
        return response()->json($data);
    }
}
