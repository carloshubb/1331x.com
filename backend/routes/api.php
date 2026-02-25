<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TorrentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ScriptSettingController;

Route::get('/torrent/get/{id}/{slug}', [TorrentController::class, 'getDetailData']);
//
Route::get('/torrents/home', [TorrentController::class, 'getHomeData']);

Route::get('/torrents/type/{type}', [TorrentController::class, 'getByType']);
Route::get('/torrents/cat/{category}/{page?}', [TorrentController::class, 'getByCategory']);
Route::get('/torrents/catsub/{category}/{page?}', [TorrentController::class, 'getBySubCategory']);
Route::get('/torrents/search/{key}/{page?}', [TorrentController::class, 'getBySearch']);
Route::get('/torrents/user/{user}/{page?}', [TorrentController::class, 'getByUser']);


Route::get('/torrents/urls', [TorrentController::class, 'getTorrentUrls']);
Route::get('/torrents/count', [TorrentController::class, 'getTorrentCount']);

Route::get('/library/movies/{page?}', [TorrentController::class, 'getMovieLibraryData']);
Route::get('/movie/{id}/{slug}', [TorrentController::class, 'getMovieLibraryRelease']);

Route::get('/categories', [TorrentController::class, 'getCategory']);

Route::get('/settings/script', [ScriptSettingController::class, 'index']);
Route::post('/settings/script/status', [ScriptSettingController::class, 'storeStatus']);

Route::get('/user/{param1}/{param2?}', [UserController::class, 'index'])->name('torrents.user');
Route::get('/option/category', [TorrentController::class, 'getOptionCategory']);
Route::get('/option/torrent', [TorrentController::class, 'getOptionTorrent']);

Route::post('/login', [LoginController::class, 'login']);

Route::get('/posts/slugs', [TorrentController::class, 'getAllTorrentUrls']);

// Bing indexing
Route::middleware('auth:sanctum')->post('/bing/submit', [TorrentController::class, 'submitToBing']);

// save
Route::middleware('auth:sanctum')->post('/torrent/save', [TorrentController::class, 'torrent_save'])->name('torrents.torrent_save');
Route::middleware('auth:sanctum')->post('/torrent/create', [TorrentController::class, 'torrent_create'])->name('torrents.torrent_create');
Route::middleware('auth:sanctum')->post('/torrent/upload-image', [TorrentController::class, 'image_upload'])->name('torrents.image_upload');

// my uploads
Route::middleware('auth:sanctum')->get('/my_uploads', [TorrentController::class, 'my_uploads']);
Route::middleware('auth:sanctum')->post('/torrent/delete', [TorrentController::class, 'delete'])->name('upload.delete');


//get next link
Route::get('/get_next_link',function(){
    $url = "https://venisonglum.com/xhmcn5s3?key=2e89e31c5834f8ba996def9012870a45";
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true, // follow redirects
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
    ]);

    $html = curl_exec($ch);
    return $html;
    if ($html === false) {
        curl_close($ch);
        return null;
    }

    curl_close($ch);

    // Load HTML
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML($html);
    libxml_clear_errors();
    
    // Find first <a href>
    $links = $dom->getElementsByTagName('a');
    foreach ($links as $link) {
        $href = $link->getAttribute('href');
        if (!empty($href)) {            
            echo $href;
        }
    }

    echo null;
});