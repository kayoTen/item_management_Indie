<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use Exception;
use Illuminate\Support\Facades\Validator;
use phpDocumentor\Reflection\Types\Boolean;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    /**
     * エラーメッセージを返す処理
     *
     * @param [type] $validate required|numeric|max
     * @param [type] $name
     * @param [type] $number
     * @return void
     */
    public function validator_msg($validate, $name, $number)
    {
        switch ($validate) {
            case 'required':
                return "{$name}の入力は必須です";
                break;

            case 'numeric':
                return "{$name}は数字で入力してください";
                break;

            case 'max':
                return "{$name}は{$number}字以内で入力してください";
                break;

            default:
                return "不明なエラーが発生しました";
                break;
        }
    }

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
     * 商品一覧
     */
    public function index()
    {
        // 商品一覧取得
        $items = Item::where('items.status', 'active')
            ->select()
            ->get();

        return view('item.index', compact('items'));
    }

    /**
     * 商品登録
     */
    public function add(Request $request)
    {
        // POSTリクエストのとき
        if ($request->isMethod('post')) {
            // バリデーション
            $this->validate($request, [
                'user_id' => 'required|numeric',
                'name' => 'required|max:100',
                'type' => 'required|numeric',
                'detail' => 'required|max:255',
            ]);

            // 商品登録
            Item::create([
                'user_id' => Auth::user()->id,
                'name' => $request->name,
                'type' => $request->type,
                'detail' => $request->detail,
            ]);

            return redirect('/items');
        }

        return view('item.add');
    }

    /**
     * 商品一括登録
     */
    public function add_multi(Request $request)
    {
        // POSTリクエストのとき
        if ($request->isMethod('post')) {

            // CSVファイルが存在するかの確認
            $uploadedData = $this->chkCSV($request);

            // テーブルとCSVファイルのヘッダーの比較
            $header = $this->chkHeader($uploadedData);

            // 不正なデータがないかを確認
            $items = $this->chkInjustice($uploadedData, $header);

            // 不正なデータは登録しない
            $flg = true;
            foreach ($items as $item) {
                if (!is_numeric($item["user_id"])) {
                    $flg = false;
                    break;
                }
                if (!(mb_strlen($item["name"]) <= 100)) {
                    $flg = false;
                    break;
                }
                if (!is_numeric($item["type"])) {
                    $flg = false;
                    break;
                }
                if (!(mb_strlen($item["detail"]) <= 255)) {
                    $flg = false;
                    break;
                }
            }
            // データベースに登録する
            if ($flg) {
                foreach ($items as $item) {
                    Item::create([
                        'user_id' => (int)$item["user_id"],
                        'name' => $item["name"],
                        'type' => (int)$item["type"],
                        'detail' => $item["detail"],
                    ]);
                }
            } else {
                print_r("CSVに不正なデータが存在します");
                exit;
            }

            return redirect('/items');
        }

        return view('item.add_multi');
    }

    /**
     * CSVファイルが存在するかの確認
     *
     * @return Boolean
     */
    function chkCSV($request)
    {
        // CSVファイルが存在するかの確認
        if ($request->hasFile('csvFile')) {
            //拡張子がCSVであるかの確認
            if ($request->csvFile->getClientOriginalExtension() !== "csv") {
                print_r('不適切な拡張子です。');
                exit;
            }
            //ファイルの保存
            $newCsvFileName = $request->csvFile->getClientOriginalName();
            $request->csvFile->storeAs('public/csv', $newCsvFileName);
        } else {
            print_r('CSVファイルの取得に失敗しました。');
            exit;
        }
        //保存したCSVファイルの取得
        $csv = Storage::disk('local')->get("public/csv/{$newCsvFileName}");
        // OS間やファイルで違う改行コードをexplode統一
        $csv = str_replace(array("\r\n", "\r"), "\n", $csv);
        // CSVファイルの空行を削除する
        $csv = trim($csv);
        $csv = preg_replace("/(\r?\n)+/", "\n", $csv);

        // $csvを元に行単位のコレクション作成。explodeで改行ごとに分解
        $uploadedData = collect(explode("\n", $csv));

        foreach ($uploadedData as $val) {
            $split = preg_split("/[,-]/", $val);
            if (count($split) !== 4) {
                print_r('１レコードの個数を確認してください');
                exit;
            }
        }

        return $uploadedData;
    }

    /**
     * テーブルとCSVファイルのヘッダーの比較
     *
     * @return Boolean
     */
    function chkHeader($uploadedData)
    {
        // テーブルとCSVファイルのヘッダーの比較
        $item = new Item();
        $header = collect($item->csvHeader());
        $uploadedHeader = collect(explode(",", $uploadedData->shift()));
        if ($header->count() !== $uploadedHeader->count()) {
            print_r('Error:ヘッダーが一致しません');
            exit;
        } else {
            return $header;
        }
    }

    /**
     * 不正なデータがないかを確認
     *
     * @return Boolean
     */
    function chkInjustice($uploadedData, $header)
    {
        // 連想配列のコレクションを作成
        // combine 一方の配列をキー、もう一方を値として一つの配列生成。haederをキーとして、一つ一つの$oneRecordと組み合わせて、連想配列のコレクション作成
        try {
            $items = $uploadedData->map(fn ($oneRecord) => $header->combine(collect(explode(",", $oneRecord))));
        } catch (Exception $e) {
            print_r('Error:ヘッダーが一致しません');
            exit;
        }

        return $items;
    }

    /**
     * 商品編集・削除
     */
    public function edit(Request $request, $id)
    {
        // POSTリクエストのとき
        if ($request->isMethod('post')) {
            // 編集ボタンが押された場合の処理
            if (isset($request->edit)) {
                $validator = Validator::make(
                    $request->all(),
                    [
                        'id' => 'required|numeric',
                        'user_id' => 'required|numeric',
                        'name' => 'required|max:100',
                        'type' => 'required|numeric',
                        'detail' => 'required|max:255',
                    ],
                    [
                        'id.required' => $this->validator_msg("required", "id", ""),
                        'user_id.required' => $this->validator_msg("required", "user_id", ""),
                        'name.required' => $this->validator_msg("required", "名前", ""),
                        'type.required' => $this->validator_msg("required", "種別", ""),
                        'detail.required' => $this->validator_msg("required", "詳細", ""),
                        'id.numeric' => $this->validator_msg("numeric", "id", ""),
                        'user_id.numeric' => $this->validator_msg("numeric", "user_id", ""),
                        'name.max' => $this->validator_msg("max", "名前", "100"),
                        'type.numeric' => $this->validator_msg("numeric", "種別", ""),
                        'detail.max' => $this->validator_msg("max", "詳細", "255"),
                    ]
                );
                if ($validator->fails()) {
                    return redirect('/items/edit/' . $request->id)
                        ->withErrors($validator)
                        ->withInput();
                } else {
                    // 編集内容を反映する
                    Item::where('id', '=', $request->id)
                        ->update([
                            'user_id' => $request->user_id,
                            'name' => $request->name,
                            'type' => $request->type,
                            'detail' => $request->detail,
                        ]);
                    return redirect('/items')
                        ->with('result', '商品の編集を完了しました');
                }
            }

            // 削除ボタンが押された場合の処理
            if (isset($request->delete)) {
                $validator = Validator::make(
                    $request->all(),
                    [
                        'id' => 'required|numeric',
                    ],
                    [
                        'id.required' => $this->validator_msg("required", "id", ""),
                        'id.numeric' => $this->validator_msg("numeric", "id", ""),
                    ]
                );
                if ($validator->fails()) {
                    return redirect('/items/edit/' . $request->id)
                        ->withErrors($validator)
                        ->withInput();
                } else {
                    // 削除内容を反映する
                    Item::where('id', '=', $request->id)->first()->delete();

                    return redirect('/items')
                        ->with('result', '商品の削除を完了しました');
                }
            }
            return redirect('/items');
        }

        // 商品詳細を取得する
        $itemdetail = $this->get_select_item_detail($id);

        return view('item.edit', ['itemdetail' => $itemdetail,]);
    }

    /**
     * 選択した商品の詳細内容を取得する処理
     *
     * @return void
     */
    function get_select_item_detail($id)
    {
        $select_item = Item::select([
            'id as id',
            'user_id as user_id',
            'name as name',
            'type as type',
            'detail as detail',
        ])
            ->from('items')
            ->where('id', '=', $id)
            ->orderBy('id')
            ->get();

        return $select_item;
    }
}
