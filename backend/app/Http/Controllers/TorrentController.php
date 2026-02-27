<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage
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
            
     
    return $torrents;
  }
}
