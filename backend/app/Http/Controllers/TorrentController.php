<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Models\Category;
use SandFox\Bencode\Bencode;
use App\Models\Post;
use App\Models\PostPopular;
use App\Models\Comment;
use App\Models\Torrent;
use App\Models\PopularTorrent;
use App\Models\HomeImageList;
use App\Models\MovieLibrary;
use App\Models\SubCategory;
use App\Models\TorrentDetail;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use App\Services\BingIndexingService;


use DB;

class TorrentController extends Controller
{
  //
  private $prefix_title = '';

  private $max_page = 2000;


  // todo make slug form name
  // $title = "Barurot 2025 1080p Tagalog WEB-DL HEVC x265 BONE";
  // $slug = Str::slug($title, '-');

  // todo update seeder and leechers
  // $torrents = Post::where('last_checked', '<', now()->subMinutes(10))->get();

  // foreach ($torrents as $torrent) {
  //     $infoHash = $torrent->infohash;
  //     $trackers = $torrent->tracker_list;

  //     foreach ($trackers as $tracker) {
  //         $stats = queryTracker($tracker, $infoHash); // custom function
  //         if ($stats) {
  //             $torrent->seeders = $stats['seeders'];
  //             $torrent->leechers = $stats['leechers'];
  //             $torrent->downloads = $stats['downloaded'];  // download count
  //             $torrent->last_checked = now();
  //             $torrent->save();
  //             break; // stop if one tracker gives data
  //         }
  //     }
  // }

  public function getHomeData(Request $request)
  {

    $home_torrent_list = [
      ['data' => [], 'slug' => 'trending-week', 'title' => 'Most Popular Torrents', 'icon' => 'flaticon-top'],
      ['data' => [], 'slug' => 'popular-movies', 'title' => 'Popular Movie Torrents', 'icon' => 'flaticon-movies'],
      ['data' => [], 'slug' => 'popular-tv', 'title' => 'Popular TV Torrents', 'icon' => 'flaticon-tv'],
      ['data' => [], 'slug' => 'popular-music', 'title' => 'Popular Music Torrents', 'icon' => 'flaticon-music'],
      ['data' => [], 'slug' => 'popular-foreign-movies', 'title' => 'Popular Foreign Movie Torrents', 'icon' => 'flaticon-movies'],
      ['data' => [], 'slug' => 'popular-apps', 'title' => 'Popular Application Torrents', 'icon' => 'flaticon-apps'],
      ['data' => [], 'slug' => 'popular-games', 'title' => 'Popular Game Torrents', 'icon' => 'flaticon-games'],
      ['data' => [], 'slug' => 'popular-other', 'title' => 'Popular Other Torrents', 'icon' => 'flaticon-other'],
    ];


    $home_torrent_list = Cache::remember('homes.data1', 86400, function () use ($home_torrent_list) {

      foreach ($home_torrent_list as $key => $item) {
        $ids = PostPopular::where('kind', $item['slug'])
                        ->orderByDesc('created_at', 'desc')
                        ->pluck('torrent_id')
                        ->toArray();
        $torrents = Post::with('subcategory:id,icon')
          ->whereIn('id', $ids)
          ->select('id', 'name', 'seeders', 'leechers', 'size', 'uploaded_by', 'subcategory_id','category_id', 'comments_count', 'uploaded_at')
          ->orderByRaw('FIELD(id, ' . implode(',', $ids) . ')')
          ->limit(10)
          ->get();
        $home_torrent_list[$key]['data'] = $torrents;
      }
      return $home_torrent_list;
    });


    $home_image_list = Cache::remember('homes.slider2', 86400, function () {
      $images = HomeImageList::with('torrent:id,title')
        ->select('id', 'link', 'image_url', 'quality', 'torrent_id')
        ->distinct()
        ->orderBy('id', 'desc')
        ->limit(10)
        ->get();

      return $images;

      return $images;
    });

    $home = [
      'torrents' => $home_torrent_list,
      'images' => $home_image_list,
      'title' => 'Movies, TV Shows, Music & Games',
      'description' => '1331x and 1337x is top torrent site, movies, games, tv series, music, Anime, torrent, software, utorrent, bittorrent, qbittorrent, streaming.'
      // 'description' => 'Discover the latest torrents for movies, TV shows, music, and games in 2025. Fast, safe, and easy downloads updated daily.'
      // 'description' => 'Download the best torrents in 2025 – movies, TV shows, music, games, and software. Fast, safe, and updated daily with the latest torrent files.'
    ];

    return response()->json($home);
  }

  public function getByCategory($category, $page = 1)
  {
    $page = (int) $page;   // ✅ ensure it's always an integer

    $cacheKey = "category_{$category}_page_{$page}";

    $data = Cache::remember($cacheKey, 86400, function () use ($category, $page) {
      $categoryModel = Category::where('slug', $category)->first();
      if (!$categoryModel) {
        return null;
      }

      $perPage = 20;
      $posts = Post::with('subcategory:id,icon')
        ->where('category_id', $categoryModel->id)
        ->orderByDesc('uploaded_at')
        ->select('id', 'name', 'seeders', 'leechers', 'size', 'uploaded_by', 'subcategory_id', 'category_id', 'comments_count', 'uploaded_at')
        ->limit($this->max_page)->get();

      $torrents = $this->getPagination($posts, $page, $perPage);

      $subcategories = SubCategory::with(['category'])
        ->where('category_id', $categoryModel->id)
        ->get();

      return [
        'torrents'      => $torrents,
        'subcategories' => $subcategories,
        'title'         => $this->prefix_title . $categoryModel->name,
        'icon'          => $categoryModel->icon,
        'description'   => $categoryModel->description,
      ];
    });

    if ($data === null) {
      return response()->json(['message' => 'Category not found', 'success' => false], 404);
    }

    return response()->json($data);
  }

  public function getBySubCategory($category, $page = 1)
  {
    $page = (int) $page;   // ✅ ensure it's always an integer

    $cacheKey = "subcategory_{$category}_page_{$page}";

    $data = Cache::remember($cacheKey, 86400, function () use ($category, $page) {
      $perPage = 20;
      $posts = Post::with('subcategory:id,icon')->where('subcategory_id', $category)
        ->limit($this->max_page)
        ->select('id', 'name', 'seeders', 'leechers', 'size', 'uploaded_by', 'subcategory_id', 'category_id', 'comments_count', 'uploaded_at')
        ->orderByDesc('uploaded_at')
        ->get();
      $torrents = $this->getPagination($posts, $page, $perPage);

      $sub = SubCategory::find($category);
      if (!$sub) {
        return null;
      }

      return [
        'torrents'     => $torrents,
        'title'        => $this->prefix_title . $sub->name,
        'icon'         => $sub->icon,
        'description'  => $sub->description,
      ];
    });

    if ($data === null) {
      return response()->json(['message' => 'Subcategory not found', 'success' => false], 404);
    }

    return response()->json($data);
  }

  public function getBySearch($key, $page = 1)
  {
    $page = (int) $page;   // ✅ ensure it's always an integer
    $search = trim($key);

    $cacheKey = "search_" . md5($search) . "_page_{$page}";

    $data = Cache::remember($cacheKey, 86400, function () use ($search, $page) {
      $perPage = 20;
      $keywords = array_filter(explode(' ', $search));

      $posts = Post::with(['category', 'subcategory:id,icon'])
        ->where(function ($q) use ($keywords) {
          foreach ($keywords as $word) {
            $q->where(function ($sub) use ($word) {
              $sub->where('name', 'LIKE', "%{$word}%")
                ->orWhere('title', 'LIKE', "%{$word}%");
            });
          }
        })
        ->select('id', 'name',  'seeders', 'leechers', 'size', 'uploaded_by','category_id', 'subcategory_id', 'comments_count', 'uploaded_at')
        ->orderByDesc('uploaded_at')
        ->take($this->max_page)
        ->get();    
      $torrents = $this->getPagination($posts, $page, $perPage);

      return [
        'torrents'     => $torrents,
        'title'        => 'Download ' . $search . ' Torrents',
        'icon'         => '',
        'description'  => '',
      ];
    });

    return response()->json($data);
  }

  public function getByType($type)
  {
    $cacheKey = "type_{$type}";

    $data = Cache::remember($cacheKey, 86400, function () use ($type) {
      // Fetch torrents based on type
      if ($type === 'trending') {
        $torrent_ids = PostPopular::where('kind', $type)->orderByDesc('created_at')->limit(100)
          ->pluck('torrent_id')->toArray();
        $torrents['title'] = 'Trending Torrents last 24 hours';
        $torrents['icon'] = 'flaticon-trending';
      } else if ($type === 'trending-week') {
        $torrent_ids = PostPopular::where('kind', $type)->orderByDesc('created_at')->limit(100)
          ->pluck('torrent_id')->toArray();
        $torrents['title'] = 'Trending Torrents last 7 Days';
        $torrents['icon'] = 'flaticon-trending';
      } else if ($type === 'trending-d-movies') {
        $torrent_ids = PostPopular::where('kind', 'trending/d/movies/')->orderByDesc('created_at')->limit(100)
          ->pluck('torrent_id')->toArray();
        $torrents['title'] = 'Trending MOVIES Torrents last 24 hours';
        $torrents['icon'] = 'flaticon-movies';
      } // Fetch most Category torrents based on type
      else if ($type === 'trending-d-tv') {
        $torrent_ids = PostPopular::where('kind', 'trending/d/tv/')->orderByDesc('created_at')->limit(100)
          ->pluck('torrent_id')->toArray();
        $torrents['title'] = 'Trending TV Torrents last 24 hours';
        $torrents['icon'] = 'flaticon-tv';
      } // Fetch most Category torrents based on type
      else if ($type === 'trending-d-games') {
        $torrent_ids = PostPopular::where('kind', 'trending/d/games/')->orderByDesc('created_at')->limit(100)
          ->pluck('torrent_id')->toArray();
        $torrents['title'] = 'Trending Games Torrents last 24 hours';
        $torrents['icon'] = 'flaticon-games';
      } // Fetch most Category torrents based on type
      else if ($type === 'trending-d-apps') {
        $torrent_ids = PostPopular::where('kind', 'trending/d/applications/')->orderByDesc('created_at')->limit(100)
          ->pluck('torrent_id')->toArray();
        $torrents['title'] = 'Trending Applications Torrents last 24 hours';
        $torrents['icon'] = 'flaticon-apps';
      } // Fetch most Category torrents based on type
      else if ($type === 'trending-d-music') {
        $torrent_ids = PostPopular::where('kind', 'trending/d/music/')->orderByDesc('created_at')->limit(100)
          ->pluck('torrent_id')->toArray();
        $torrents['title'] = 'Trending MUSIC Torrents last 24 hours';
        $torrents['icon'] = 'flaticon-music';
      } // Fetch most Category torrents based on type
      else if ($type === 'trending-d-doc') {
        $torrent_ids = PostPopular::where('kind', 'trending/d/documentaries/')->orderByDesc('created_at')->limit(100)
          ->pluck('torrent_id')->toArray();
        $torrents['title'] = 'Trending DOCMENTRAIES Torrents last 24 hours';
        $torrents['icon'] = 'flaticon-documentary';
      } // Fetch most Category torrents based on type
      else if ($type === 'trending-d-anime') {
        $torrent_ids = PostPopular::where('kind', 'trending/d/anime/')->orderByDesc('created_at')->limit(100)
          ->pluck('torrent_id')->toArray();
        $torrents['title'] = 'Trending ANIME Torrents last 24 hours';
        $torrents['icon'] = 'flaticon-ninja-portrait';
      } // Fetch most Category torrents based on type
      else if ($type === 'trending-d-other') {
        $torrent_ids = PostPopular::where('kind', 'trending/d/other/')->orderByDesc('created_at')->limit(100)
          ->pluck('torrent_id')->toArray();
        $torrents['title'] = 'Trending OTHER Torrents last 24 hours';
        $torrents['icon'] = 'flaticon-other';
      } // Fetch most Category torrents based on type
      else if ($type === 'trending-d-xxx') {
        $torrent_ids = PostPopular::where('kind', 'trending/d/xxx/')->orderByDesc('created_at')->limit(100)
          ->pluck('torrent_id')->toArray();
        $torrents['title'] = 'Trending XXX Torrents last 24 hours';
        $torrents['icon'] = 'flaticon-xxx';
      }
      // Top 
      else if ($type === 'top') {
        $torrent_ids = PostPopular::where('kind', 'top-100')->orderByDesc('created_at')->limit(100)
          ->pluck('torrent_id')->toArray();
        $torrents['title'] = 'Top 100 Torrents';
        $torrents['icon'] = 'flaticon-top';
      } else if ($type === 'top-100-movies') {
        $torrent_ids = PostPopular::where('kind', 'top-100-movies')->orderByDesc('created_at')->limit(100)
          ->pluck('torrent_id')->toArray();
        $torrents['title'] = 'Top 100 MOVIE Torrents';
        $torrents['icon'] = 'flaticon-top';
      } else if ($type === 'top-100-tv') {
        $torrent_ids = PostPopular::where('kind', 'top-100-television')->orderByDesc('created_at')->limit(100)
          ->pluck('torrent_id')->toArray();
        $torrents['title'] = 'Top 100 TV Torrents';
        $torrents['icon'] = 'flaticon-top';
      } else if ($type === 'top-100-games') {
        $torrent_ids = PostPopular::where('kind', 'top-100-games')->orderByDesc('created_at')->limit(100)
          ->pluck('torrent_id')->toArray();
        $torrents['title'] = 'Top 100 GAME Torrents';
        $torrents['icon'] = 'flaticon-top';
      } else if ($type === 'top-100-apps') {
        $torrent_ids = PostPopular::where('kind', 'top-100-applications')->orderByDesc('created_at')->limit(100)
          ->pluck('torrent_id')->toArray();
        $torrents['title'] = 'Top 100 APPS Torrents';
        $torrents['icon'] = 'flaticon-top';
      } else if ($type === 'top-100-music') {
        $torrent_ids = PostPopular::where('kind', 'top-100-music')->orderByDesc('created_at')->limit(100)
          ->pluck('torrent_id')->toArray();
        $torrents['title'] = 'Top 100 MUSIC Torrents';
        $torrents['icon'] = 'flaticon-top';
      } else if ($type === 'top-100-doc') {
        $torrent_ids = PostPopular::where('kind', 'top-100-documentaries')->orderByDesc('created_at')->limit(100)
          ->pluck('torrent_id')->toArray();
        $torrents['title'] = 'Top 100 DOCUMENTARY Torrents';
        $torrents['icon'] = 'flaticon-top';
      } else if ($type === 'top-100-anime') {
        $torrent_ids = PostPopular::where('kind', 'top-100-anime')->orderByDesc('created_at')->limit(100)
          ->pluck('torrent_id')->toArray();
        $torrents['title'] = 'Top 100 ANIME Torrents';
        $torrents['icon'] = 'flaticon-top';
      } else if ($type === 'top-100-other') {
        $torrent_ids = PostPopular::where('kind', 'top-100-other')->orderByDesc('created_at')->limit(100)
          ->pluck('torrent_id')->toArray();
        $torrents['title'] = 'Top 100 OTHER Torrents';
        $torrents['icon'] = 'flaticon-top';
      } else if ($type === 'top-100-xxx') {
        $torrent_ids = PostPopular::where('kind', 'top-100-xxx')->orderByDesc('created_at')->limit(100)
          ->pluck('torrent_id')->toArray();
        $torrents['title'] = 'Top 100 XXX Torrents';
        $torrents['icon'] = 'flaticon-top';
      } else {
        $torrent_ids = [];
        $torrents['title'] = '';
        $torrents['icon'] = '';
      }

      $torrents['data'] = !empty($torrent_ids)
        ? Post::with('subcategory:id,icon')
          ->whereIn('id', $torrent_ids)
          ->select('id', 'name','category_id', 'seeders', 'leechers', 'size', 'uploaded_by', 'subcategory_id', 'comments_count', 'uploaded_at')
          ->orderByRaw('FIELD(id, ' . implode(',', $torrent_ids) . ')')
          ->get()
        : collect();

      return [
        'torrents'     => $torrents,
        'title'        => $this->prefix_title . $torrents['title'],
        'icon'         => '',
        'description'  => '',
      ];
    });

    return response()->json($data);
  }

  public function getByUser($user, $page = 1)
  {
    $page = (int) $page;   // ✅ ensure it's always an integer

    $cacheKey = "user_{$user}_page_{$page}";

    $data = Cache::remember($cacheKey, 86400, function () use ($user, $page) {
      $perPage = 20;
      $torrents = Post::with(['subcategory:id,icon'])
        ->where('uploaded_by', $user)
        ->select('id', 'name', 'seeders', 'leechers', 'size', 'uploaded_by', 'subcategory_id','category_id', 'comments_count', 'uploaded_at')
        ->orderByDesc('uploaded_at')
        ->limit($this->max_page)
        ->get();

      $info = User::where('username', $user)->first();

      return [
        'torrents' => $this->getPagination($torrents, $page, $perPage),
        'info'     => $info,
      ];
    });

    return response()->json($data);
  }


  /**
   * get torrent detail data
   */
  public function getDetailData(Request $request, $id, $slug = null)
  {
    //Log::info("Fetching torrent details for ID: {$id}, Slug: {$slug}");
    
    try {
      // Cache key based on torrent ID
      $cacheKey = "torrent_detail_{$id}";
      
      // Try to get from cache first (forever cache - cleared only when torrent is updated)
      $torrent = Cache::rememberForever($cacheKey, function () use ($id) {
        // Optimize query: select only needed fields and limit comments
        return Post::with([
          'category:id,name',
          'subcategory:id,name,icon',
          'comments' => function ($query) {
            // Limit comments to reduce query time (load more via separate endpoint if needed)
            $query->orderBy('created_at', 'desc')->limit(50);
          }
        ])
          ->where('id', $id)
          ->firstOrFail();
      });

      // Process cover image URL
      if ($torrent->cover_image) {
        // If it's NOT already an absolute or protocol-relative URL
        if (!preg_match('#^(https?:)?//#i', $torrent->cover_image)) {
          $torrent->cover_image = 'https://www.1377x.to' . $torrent->cover_image;
        }
      }

      // Process content: fix image tags, add alt attributes, and wrap with progress bars
      if ($torrent->content) {
        $content = $torrent->content;
        
        // Fix image tags: replace placeholder src with data-original if present, and wrap with progress bar
        $content = preg_replace_callback(
          '/<img\s+([^>]*?)>/i',
          function ($matches) {
            $attributes = $matches[1];
            
            // Check if src="/images/profile-load.svg"
            if (preg_match('/src=["\']\/images\/profile-load\.svg["\']/i', $attributes)) {
              // Try to find data-original attribute
              if (preg_match('/data-original=["\']([^"\']+)["\']/i', $attributes, $dataOriginalMatch)) {
                // Replace src with data-original value
                $attributes = preg_replace(
                  '/src=["\']\/images\/profile-load\.svg["\']/i',
                  'src="' . htmlspecialchars($dataOriginalMatch[1], ENT_QUOTES, 'UTF-8') . '"',
                  $attributes
                );
              }
            }
            
            // Add alt attribute if not present
            if (!preg_match('/\balt=["\']/i', $attributes)) {
              $alt = "Movie cover image";
              $imgTag = '<img alt="' . e($alt) . '" ' . $attributes . '>';
            } else {
              $imgTag = '<img ' . $attributes . '>';
            }
            
            // Wrap image in progress bar container
            return '<div class="image-progress-wrapper">' . $imgTag . '<div class="image-progress-bar"><div class="image-progress-fill"></div></div></div>';
          },
          $content
        );
        
        $torrent->content = $content;
      }

      return response()->json($torrent);
    } catch (ModelNotFoundException $e) {
      return response()->json(['message' => 'Torrent not found', 'success' => false], 404);
    }
  }



  public function getOptionCategory(Request $request)
  {
    $category = $request->query('category');
    $torrents = Category::with('subcategory')->where('id', $category)->first();

    return response()->json($torrents);
  }

  public function getOptionTorrent(Request $request)
  {
    $category = $request->query('category');
    $page = $request->query('page');
    $perPage = 20;
    $data = Post::with('subcategory')
      ->where('category_id', $category)
      ->orderBy('uploaded_at', 'desc')
      ->limit($this->max_page)->get();
    $torrents = $this->getPagination($data, $page, $perPage);
    return response()->json($torrents);
  }


  public function getMovieLibraryData($page)
  {
    $page = (int) $page; // ensure it's always an integer

    $cacheKey = "movie_library_page_{$page}";

    $data = Cache::remember($cacheKey, 86400, function () use ($page) {
      $movies = MovieLibrary::with('subCategory')
        ->paginate(24, ['*'], 'page', $page);

      return [
        'torrents'     => $movies,
        'title'        => $this->prefix_title . 'Movie Library',
        'icon'         => '',
        'description'  => '',
      ];
    });

    return response()->json($data);
  }

  public function getMovieLibraryRelease($id, $slug)
  {
    $cacheKey = "movie_release_{$id}_" . md5($slug);

    $data = Cache::remember($cacheKey, 86400, function () use ($id, $slug) {
      $info_url = '/movie/' . $id . '/' . trim($slug) . '/';
      $movie = MovieLibrary::where('info_url', $info_url)->first();
      if (!$movie) {
        return null;
      }

      $movies = Post::with('subcategory:id,icon')
        ->select(
          'id',
          'title',
          'name',
          'seeders',
          'leechers',
          'size',
          'uploaded_by',
          'subcategory_id',
          'category_id',
          'comments_count',
          'uploaded_at'
        )
        ->whereRaw(
          'LOWER(title) LIKE ?',
          ['%' . strtolower($movie->info_title) . '%']
        )
        ->limit(20)
        ->get();

      return [
        'torrents'     => ['data' => $movies],
        'name'         => $movie->info_title,
        'title'        => $this->prefix_title . $movie->info_title,
        'icon'         => '',
        'cover_image'  => $movie->img_url,
        'description'  => $movie->content,
      ];
    });

    if ($data === null) {
      return response()->json(['message' => 'Movie not found', 'success' => false], 404);
    }

    return response()->json($data);
  }


  /**
   * Upload by Abudulla
   * 
   */
  public function torrent_create(Request $request)
  {
    // get one unused id from post table
    $ids = Post::pluck('id')->toArray();
    $max = max($ids);
    $all = range(1, $max);
    $missing = array_diff($all, $ids);
    $firstID = reset($missing);


    $user_name = Auth::user()->username;

    $torrentFile = $request->file('torrent_file');
    $rawData = file_get_contents($torrentFile->getRealPath());
    $torrent = Bencode::decode($rawData);
    // Get the raw bencoded "info" dictionary (important for infohash)
    $infoRaw = Bencode::encode($torrent['info']);
    // Calculate SHA-1 hash
    $infoHashHex    = sha1($infoRaw);         // hex form (for display)

    $t = Post::where('infohash', $infoHashHex)->count();
    if ($t > 0) return response()->json([
      'success' => false,
      'message' => 'Same name torrent is already exist.',
    ]);

    // Torrent metadata
    $name = $torrent['info']['name'] ?? '';
    $pieceLength = $torrent['info']['piece length'] ?? 0;

    // Calculate total size
    $totalSize = 0;
    if (isset($torrent['info']['files'])) {
      // Multi-file torrent
      foreach ($torrent['info']['files'] as $file) {
        $totalSize += $file['length'];
      }
    } else {
      // Single-file torrent
      $totalSize = $torrent['info']['length'] ?? 0;
    }
    $data['file_name'] = $name;
    $total_size = number_format($totalSize / 1048576, 2) . " MB";
    $magnet = "magnet:?xt=urn:btih:$infoHashHex&dn=" . urlencode($name);
    $data['file_name'] = $name;

    $torrent = new Post();
    $torrent->id = $firstID;
    $torrent->name = $request->title ? $request->title : '';
    $torrent->title = $request->title ? $request->title : '';
    $torrent->description = $request->description ? $request->description : '';;
    $torrent->category_id = $request->category;
    $torrent->subcategory_id = $request->subcategory;
    $torrent->magnet_link = $magnet;
    $torrent->infohash = $infoHashHex;
    $torrent->language = 'English';
    $torrent->size = $total_size;
    $torrent->seeders = 1;
    $torrent->leechers = 1;
    $torrent->uploaded_at = date("Y-m-d H:i:s");
    $torrent->uploaded_by = $user_name;
    $torrent->save();

    Cache::flush();

    return response()->json([
      'success' => true,
      'message' => 'Torrent uploaded successfully',
      'torrent' => $torrent
    ]);
  }

  public function torrent_save(Request $request)
  {
    $torrent_id = $request->input('id');

    $update_torrent = Post::where('id', $torrent_id)->first();
    $update_torrent->seeders = $request->input('seeders');
    $update_torrent->leechers = $request->input('leechers');
    $update_torrent->name = $request->input('name');

    $update_torrent->title = $request->input('title');
    $update_torrent->description = $request->input('description');
    $update_torrent->cover_image = $request->input('cover_image');
    $update_torrent->slogan = $request->input('genre');
    $update_torrent->seeders = $request->input('seeders');
    $update_torrent->leechers = $request->input('leechers');
    $update_torrent->poster_alt = $request->input('poster_alt');
    $update_torrent->downloads = $request->input('download_count');
    $update_torrent->content = $request->input('description_html');

    $update_torrent->save();

    // Clear the specific torrent detail cache (forever cache needs manual invalidation)
    Cache::forget("torrent_detail_{$torrent_id}");
    // Also clear all other caches that might be affected
    Cache::flush();

    // if it is on home_images table, to update img_url
    $home = HomeImageList::where('torrent_id', $torrent_id)->first();
    if ($home) {
      $home->image_url = $request->input('cover_image');
      $home->save();
    }

    return response()->json([
      'success' => true
    ]);
  }

  public function getCategory(Request $request)
  {
    $categories = Cache::remember('categories_all', 86400, function () {
      return Category::with('subcategory')->get();
    });

    return response()->json($categories);
  }

  public function my_uploads()
  {

    $username = Auth::user()->username;
    $uploads = Post::with(['subcategory'])->where('uploaded_by', $username)->latest()->get();
    return response()->json($uploads);
  }

  public function delete(Request $request)
  {
    $torrent_id = $request->input('id');
    $torrent = Post::find($torrent_id);

    if ($torrent) {
      $torrent->delete();
      return response()->json([
        'success' => true,
        'message' => 'Torrent deleted successfully'

      ]);
    }
    return response()->json([
      'success' => false,
      'message' => 'Torrent not found',
    ], 404);
  }

  public function image_upload(Request $request)
  {
    $request->validate([
      'image' => 'required|image|max:2048', // max 2MB
    ]);

    // Store the image in storage/app/public
    $path = $request->file('image')->store('images', 'public');

    // Return full URL
    $url = asset('storage/' . $path);

    return response()->json([
      'success' => true,
      'url' => $url
    ]);
  }

  /**
   * get torrent urls for SEO
   */
  public function getTorrentUrls(Request $request)
  {
    $urls = [];
    return response()->json($urls);
  }

  public function getAllTorrentUrls(Request $request)
  {
    $min_id = 6520000;
    $max_id = 6573500;
    $posts = Post::select('id', 'name', 'title', 'cover_image', 'updated_at')
    ->where('id', '>', $min_id)
    ->where('id', '<', $max_id)
    ->orderBy('updated_at', 'desc')->get();
    $paths = [];
    foreach ($posts as $post) {
      $path = [
        'loc' => 'torrent/' . $post->id . '/' . $post->slugged_title,
        'lastmod' => $post->updated_at,
      ];
      if ($post->cover_image != '') {
        $image['loc'] = preg_replace('/^\/\//', 'https://', $post->cover_image);
        $image['title'] = $post->title;
        $path['images'] = [$image];
      }
      $paths[] = $path;
    }
    return response()->json($paths);
  }

  public function getTorrentCount(Request $request)
  {
    $torrent_count = Post::count();
    return response()->json(['total' => $torrent_count]);
  }

  /**
   * Submit URLs to Bing for indexing
   * Accepts either a single URL or an array of URLs
   */
  public function submitToBing(Request $request)
  {
    $request->validate([
      'urls' => 'required|array',
      'urls.*' => 'required|string|url'
    ]);

    try {
      $bingService = new BingIndexingService();
      $result = $bingService->submitToBing($request->input('urls'));

      return response()->json($result);
    } catch (\Exception $e) {
      Log::error('Bing submission error', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);

      return response()->json([
        'success' => false,
        'message' => 'Failed to submit URLs to Bing: ' . $e->getMessage()
      ], 500);
    }
  }

  private function getPagination($data, $page = 1, $perPage = 10)
  {
    $page = (int) $page;   // ✅ ensure it's always an integer
    // Slice the data for the current page
    $items = $data->slice(($page - 1) * $perPage, $perPage)->values();

    $ct = $data->count();
    $page_count = min($ct, $this->max_page);

    // Build paginator with total = 1000
    $torrents = new LengthAwarePaginator(
      $items,
      $page_count, // <-- total capped
      $perPage,
      $page,
      ['path' => request()->url(), 'query' => request()->query()]
    );

    return $torrents;
  }
}
