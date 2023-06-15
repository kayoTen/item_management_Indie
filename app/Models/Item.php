<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Item extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'type',
        'detail',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [];

    public function csvHeader(): array
    {
        return [
            'user_id',
            'name',
            'type',
            'detail',
        ];
    }

    public function getCsvData(): \Illuminate\Support\Collection
    {
        $data = DB::table('items')->get();
        return $data;
    }
    public function insertRow($row): array
    {
        return [
            $row->user_id,
            $row->name,
            $row->category_id,
            $row->detail,
        ];
    }
}
