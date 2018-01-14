## チューニング内容

下記、ディレクトリの関数内でデータ取得方法を修正

<b>[ディレクトリと関数]</b>
ディレクトリ : /app/Http/Libs/PageUtility.php
関数 : findUserViewedPage($keyword)

<b>[データ取得方法]</b>

<b>(1)</b> キーワードからページIDとページタイトルを取得

```c
$pages = Page::where('title', 'LIKE', "$keyword%")->get();
```

<b>(2)</b> (1)で取得したページIDからユーザID、閲覧数取得

```c
$activities = Activity::select(DB::raw("user_id as user_id,
                                        page_id as page_id,
                                        count(id) as view_count"))
                       ->where('page_id',  $pages_value['id'])
                       ->groupby('user_id', 'page_id')
                       ->get();
```

<b>(3)</b> (2)で取得したユーザーIDからユーザ名を取得

```c
$user_name = User::where('id', $activities_value['user_id'])->get();
```

<b>(4)</b> (1),(2),(3)で取得したデータを配列に格納

```c
$userPageArray[$i]['page_id']    = $pages_value['id'];
$userPageArray[$i]['page_title'] = $pages_value['title'];
$userPageArray[$i]['user_id']    = $activities_value['user_id'];
$userPageArray[$i]['user_name']  = $user_name[0]->name;
$userPageArray[$i]['view_count'] = $activities_value['view_count'];
// iは取得したデータ数
```

<b>(5)</b> (2)の返り値が空だった場合は配列にユーザID、ユーザ名、閲覧数を空で格納

```c
$userPageArray[$i]['page_id']    = $pages_value['id'];
$userPageArray[$i]['page_title'] = $pages_value['title'];
$userPageArray[$i]['user_id']    = '';
$userPageArray[$i]['user_name']  = '';
$userPageArray[$i]['view_count'] = '';
```

<b>(6)</b> データ格納後、ユーザーID(第1優先 / 昇順)、ページID(第2優先 / 昇順)にソート

```c
array_multisort(array_column($userPageArray, 'user_id'), SORT_ASC, SORT_NUMERIC,
                array_column($userPageArray, 'page_id'), SORT_ASC, SORT_NUMERIC,
                $userPageArray);
```


※ キーワードが空で検索された場合は **findUserViewedPage($keyword)** の関数を実行せず、空の配列をviewに渡す

※ index追加
```c
ALTER TABLE page ADD INDEX title(title);
ALTER TABLE activity ADD INDEX page_id (page_id);
ALTER TABLE activity ADD INDEX groupby_index (page_id, user_id);
```
database/sql/alter.sqlに記載
