<?php

namespace App\Services;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

use App\Models\Post;
use App\Models\PostPopular;
use App\Models\Comment;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\HomeImageList;
use App\Models\User;

use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;

class TorrentService
{

  // Get id from name
  private $category_info = [];
  // Get id from a category id and name like 3_HD
  private $sub_category_info = [];

  private $detail_info = [
    "category" => "",
    "type" => "",
    "language" => "English",
    "totalsize" => "",
    "uploadedby" => "",
    "downloads" => 0,
    "lastchecked" => "",
    "dateuploaded" => "1 decade ago",
    "seeders" => 0,
    "leechers" => 0,
  ];

  public function __construct()
  {
    // get category list for torrent info
    $this->category_info = Category::pluck('id', 'slug')->toArray();

    $sub_categories = SubCategory::get()->toArray();
    $subs = [];
    foreach ($sub_categories as $sub) {
      $key = $sub['category_id'] . '_' . $sub['name'];
      $value = $sub['id'];
      $subs[$key] = $value;
    }
    $this->sub_category_info = $subs;
  }

  /**
   * get & store a torrent info
   */
  public function getTorrentInfo($torrent_id, Command $command)
  {
    $torrent = Post::find($torrent_id);
    if ($torrent) return false;
    $client = new Client([
            'timeout' => 15,
            'headers' => [
                'User-Agent' =>
                    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept-Language' => 'en-US,en;q=0.9',
            ],
        ]);
    //$httpClient = HttpClient::create();
    $url = "https://1377x.to/torrent/{$torrent_id}/test/";
    try {
            $response = $client->get($url);
            $html = (string) $response->getBody();
        } catch (\Exception $e) {
            // $this->error('Request failed: ' . $e->getMessage());
            return;
        }
    //$html = $response->getContent();
    $crawler = new Crawler($html);
    $rows = $crawler->filter('.infohash-box');
    if (count($rows) == 0) {
      return false;
    }

    $torrent = new Post();

    // to parsing of a page
    // 1. detail info area
    $detail_info = $this->detail_info;
    $rows_list = $crawler->filter('ul.list li');
    $rows_list->each(function (Crawler $item, $ii) use (&$detail_info) {
      if ($item->filter('strong')->count() > 0) {
        $fieldName = strtolower(
          preg_replace('/\s+/', '', $item->filter('strong')->text())
        );
        $fieldValue = $item->filter('span')->text();
        $detail_info[$fieldName] = $fieldValue;
      }
    });

    $category_id = $this->category_info[$detail_info['category']] ? $this->category_info[$detail_info['category']] : 0;
    $_id = $category_id . '_' . $detail_info['type'];
    $sub_category_id = $this->sub_category_info[$_id] ? $this->sub_category_info[$_id] : 0;
    if ($category_id == 0 || $sub_category_id == 0) {
      return false;
    }

    $torrent['id'] = $torrent_id;
    $torrent['category_id'] = $category_id;
    $torrent['subcategory_id'] = $sub_category_id;
    $torrent['language'] = $detail_info['language'];
    $torrent['uploaded_by'] = $detail_info['uploadedby'];
    // to update user info if user is new
    $this->updateUser($detail_info['uploadedby']);
    $torrent['size'] = $detail_info['totalsize'];
    // set fake value
    $down = mt_rand(30000, 50000);
    $seed = mt_rand(12000, 30000);
    $leech = mt_rand(8000, 12000);
    $torrent['downloads'] = $down; //intval($detail_info['downloads']);
    $torrent['seeders'] = $seed; //intval($detail_info['seeders']);
    $torrent['leechers'] = $leech; //intval($detail_info['leechers']);

    $torrent['last_checked'] = $detail_info['lastchecked'];
    $torrent['date_uploaded'] = $detail_info['dateuploaded'];
    $torrent['uploaded_at'] = now();
    // 2. download info area
    $rows_magnet = $crawler->filter('a[href^="magnet:"]');
    $torrent['magnet_link'] = $rows_magnet->count() > 0 ? $rows_magnet->attr('href') : null;
    $infohash = $crawler->filter('div.infohash-box p span')->text();
    $torrent['infohash'] = $infohash ? $infohash : null;
    $strem_link_dom = $crawler->filterXPath('//a[text()="Play now (Stream)"]');
    if ($strem_link_dom->count() > 0) $torrent['stream_link'] = $strem_link_dom->attr('href');

    // 3. detail description area of movie torrent
    if ($crawler->filter('.torrent-detail')->count() > 0) {
      if ($crawler->filter('.torrent-detail .torrent-image img')->count() > 0) {
        try {
          $torrent['cover_image'] = $crawler->filter('.torrent-detail .torrent-image img')->attr('src');
        } catch (\Exception $e) {
          Log::warning("Cover image parse error: " . $e->getMessage());
          $torrent['cover_image'] = '';
        }
      } else {
        $torrent['cover_image'] = '';
      }
     
      $torrent['title'] = $crawler->filter('.torrent-detail-info h3 a')->count()
        ? $crawler->filter('.torrent-detail-info h3 a')->text()
        : '';

      $torrent['slogan'] = $crawler->filter('.torrent-detail-info .torrent-category')->count()
        ? trim($crawler->filter('.torrent-detail-info .torrent-category')->html())
        : '';


      $torrent['description'] = $crawler->filter('.torrent-detail-info p')->count()
        ? $crawler->filter('.torrent-detail-info p')->text()
        : '';
    }

    // 4. description area
    $torrent['name'] = $crawler->filter('.box-info-heading h1')->text();
    
    // to change img src with data-original
    $content = trim($crawler->filter('div#description p')->count() ? $crawler->filter('div#description')->html() : '');
    $content = Str::replace('"/images/profile-load.svg" data-original=', '', $content);
    $torrent['content'] = $content;
    
    $torrent['files'] = trim($crawler->filter('div#files')->count() ? $crawler->filter('div#files')->html() : '');
    $torrent['tracker_list'] = trim($crawler->filter('div#tracker-list')->count() ? $crawler->filter('div#tracker-list')->html() : '');

    // $comments_count = intval($crawler->filterXPath('//a[text()="Comments"]')->filter('a span.active')->text());
    // if ($comments_count) {
    //   sleep(1);
    //   $url = "https://1377x.to/comments.php?torrentid={$torrent_id}";
    //   $response = Http::withoutVerifying()->get($url);
    //   if ($response->successful()) {
    //     $comments = $response->json(); // usually HTML or JSON
    //     $comments_data = [];
    //     foreach ($comments as $row) {
    //       $comments_data[] = [
    //         'torrent_id' => $torrent_id,
    //         'user_name' => $row['username'],
    //         'comment' => $row['comment'],
    //         'id' => $row['commentid'],
    //         'class' => $row['class'],
    //         'posted' => $row['posted'],
    //       ];
    //     }
    //     Comment::insert($comments_data);
    //   }
    // }
    //
    $torrent['comments_count'] = 0;
    $torrent->save();

    return true;
  }

  /**
   * update user info 
   */
  public function updateUser($user_name)
  {
    $user = User::where('username', $user_name)->first();
    if ($user) return false;
    $client = new Client([
            'timeout' => 15,
            'headers' => [
                'User-Agent' =>
                    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept-Language' => 'en-US,en;q=0.9',
            ],
        ]);
    //$httpClient = HttpClient::create();
    $url = "https://1377x.to/user/{$user_name}/";
    try {
            $response = $client->get($url);
            $html = (string) $response->getBody();
        } catch (\Exception $e) {
            // $this->error('Request failed: ' . $e->getMessage());
            return;
        }
    //$html = $response->getContent();
    $crawler = new Crawler($html);
    $rows = $crawler->filter('.box-info-detail');
    if (count($rows) == 0) {
      return false;
    }

    $user = new User();
    $rows_list = $crawler->filter('.box-info-detail li');
    $data = [];
    // $rows_list->each(function (Crawler $row, $i) use (&$data) {
    $rows_list->each(function (Crawler $item, $ii) use (&$user) {
      $fieldName = strtolower(
        preg_replace('/\s+/', '', $item->filter('strong')->text())
      );
      $fieldValue = $item->filter('span')->text();
      $user->{$fieldName} = $fieldValue;
    });
    // });
    $user->username = $user_name;
    $user->password = Hash::make('123456');
    $user->email = "$user_name@example.com";
    $user->save();

    return true;
  }

  // parse for short date type
  public function parseDate($relativeDate, $baseDate = null)
  {
    $baseDate = $baseDate ? Carbon::parse($baseDate) : Carbon::now();

    $relativeDate = strtolower(trim($relativeDate));

    // Handle different patterns
    if (preg_match('/(\d+)\s+decade(s)?\s+ago/', $relativeDate, $matches)) {
      return $baseDate->subYears($matches[1] * 10);
    }

    if (preg_match('/(\d+)\s+year(s)?\s+ago/', $relativeDate, $matches)) {
      return $baseDate->subYears($matches[1]);
    }

    if (preg_match('/(\d+)\s+month(s)?\s+ago/', $relativeDate, $matches)) {
      return $baseDate->subMonths($matches[1]);
    }

    if (preg_match('/(\d+)\s+week(s)?\s+ago/', $relativeDate, $matches)) {
      return $baseDate->subWeeks($matches[1]);
    }

    if (preg_match('/(\d+)\s+day(s)?\s+ago/', $relativeDate, $matches)) {
      return $baseDate->subDays($matches[1]);
    }

    if (preg_match('/(\d+)\s+hour(s)?\s+ago/', $relativeDate, $matches)) {
      return $baseDate->subHours($matches[1]);
    }

    if (preg_match('/(\d+)\s+minute(s)?\s+ago/', $relativeDate, $matches)) {
      return $baseDate->subMinutes($matches[1]);
    }

    // Try Carbon's built-in parser as fallback
    return Carbon::parse($relativeDate, $baseDate);
  }
}
