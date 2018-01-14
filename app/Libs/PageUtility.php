<?php

namespace App\Libs;

use App\Http\Models\Activity;
use App\Http\Models\Page;
use App\Http\Models\User;
use DB;

class PageUtility
{
    /**
     * 検索
     *
     * @param $keyword
     * @return array
     */
    public static function findUserViewedPage($keyword){

      $userPageArray = []; // 検索から取得したデータを格納する配列
      // キーワードがnullでない場合、検索処理実行
      if(!is_null($keyword)) {

        $pages = Page::where('title', 'LIKE', "$keyword%")->get();

        $i = 0;
        foreach($pages as $pages_key => $pages_value) {

          $activities = Activity::select(DB::raw("user_id as user_id, page_id as page_id, count(id) as view_count"))
                                ->where('page_id', $pages_value['id'])
                                ->groupby('user_id', 'page_id')
                                ->get();

          if($activities->isEmpty()) {
            $userPageArray[$i]['page_id']    = $pages_value['id'];
            $userPageArray[$i]['page_title'] = $pages_value['title'];
            $userPageArray[$i]['user_id']    = '';
            $userPageArray[$i]['user_name']  = '';
            $userPageArray[$i]['view_count'] = '';

            $i ++;

          } else {

            foreach($activities as $activities_key => $activities_value) {

              $user_name = User::where('id', $activities_value['user_id'])->get();

              $userPageArray[$i]['page_id']    = $pages_value['id'];
              $userPageArray[$i]['page_title'] = $pages_value['title'];
              $userPageArray[$i]['user_id']    = $activities_value['user_id'];
              $userPageArray[$i]['user_name']  = $user_name[0]->name;
              $userPageArray[$i]['view_count'] = $activities_value['view_count'];

              $i ++;

            }
          }

        } // end foreach($pages as $pages_key => $pages_value)

        // 第1ソート：ユーザーID(昇順) / 第2ソート：ページID(昇順)にソート
        array_multisort(array_column($userPageArray, 'user_id'), SORT_ASC, SORT_NUMERIC,
                        array_column($userPageArray, 'page_id'), SORT_ASC, SORT_NUMERIC,
                        $userPageArray);

      } // end if(!is_null($keyword))

      return $userPageArray;

    } // end public static function findUserViewedPage($keyword)

} // end class PageUtility
