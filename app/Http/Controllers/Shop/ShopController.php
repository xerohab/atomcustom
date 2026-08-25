<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShopController extends Controller
{
    public function __invoke($categorySlug = null)
    {
        $categories = DB::table('website_shop_categories')->orderBy('id', 'asc')->get();

        $selectedCategory = null;
        if ($categorySlug) {
            $selectedCategory = DB::table('website_shop_categories')->where('slug', $categorySlug)->first();
        }

        $articles = DB::table('website_shop_articles')
            ->when($selectedCategory, function ($query) use ($selectedCategory) {
                return $query->where('website_shop_category_id', $selectedCategory->id);
            })
            ->orderBy('costs', 'asc')
            ->orderBy('position', 'asc')
            ->get();

        return view('shop-custom', compact('categories', 'articles', 'selectedCategory'));
    }

    public function purchase($packageId)
    {
        $user = auth()->user();
        $article = DB::table('website_shop_articles')->where('id', $packageId)->first();

        if (!$article) {
            return redirect()->back()->with('error', 'Shop package not found!');
        }

        if ($user->website_balance < $article->costs) {
            return redirect()->back()->with('error', 'Insufficient website balance to purchase this package.');
        }

        // Deduct balance
        DB::table('users')->where('id', $user->id)->decrement('website_balance', $article->costs);

        // Instant Hotel Fulfillment
        if ($article->credits > 0) {
            DB::table('users')->where('id', $user->id)->increment('credits', $article->credits);
        }

        if ($article->duckets > 0) {
            DB::table('users')->where('id', $user->id)->increment('activity_points', $article->duckets);
        }

        if ($article->diamonds > 0) {
            DB::table('users')->where('id', $user->id)->increment('vip_points', $article->diamonds);
        }

        if ($article->give_rank && $article->give_rank > $user->rank) {
            DB::table('users')->where('id', $user->id)->update(['rank' => $article->give_rank]);
        }

        if (!empty($article->badges)) {
            $badges = explode(',', $article->badges);
            foreach ($badges as $badge) {
                $badgeCode = trim($badge);
                if (!empty($badgeCode)) {
                    DB::table('user_badges')->insertOrIgnore([
                        'user_id' => $user->id,
                        'badge_code' => $badgeCode,
                        'slot_id' => 0,
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', 'Purchase successful! Your rewards have been added to your account.');
    }
}