<?php

namespace App\Models\SRO\Account;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class SkSilkBuyList extends Model
{
    /**
     * The Database connection name for the model.
     *
     * @var string
     */
    protected $connection = 'account';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dbo.SK_SilkBuyList';

    /**
     * The table primary Key
     *
     * @var string JID
     */
    protected $primaryKey = 'BuyNo';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'BuyNo',
        'UserJID',
        'Silk_Type', // tinyint
        'Silk_Reason', // tinyint
        'Silk_Offset', // int
        'Silk_Remain', // int
        'ID', // int
        'BuyQuantity', // int
        'OrderNumber', // varchar(30)
        'PGCompany', // tinyint
        'PayMethod', // tinyint
        'PGUniqueNo', // varchar(20)
        'AuthNumber', // varchar(14)
        'AuthDate', // datetime
        'SubJID', // int
        'srID', // varchar(25)
        'SlipPaper', // varchar(128)
        'MngID', // int
        'IP', // varchar(16)
        'RegDate' // datetime
    ];

    /**
     * Type 3 is for Web
     */
    public const SILKTYPEWEB = 3;

    /**
     * Type 2 is for Web
     */
    public const SILKTYPEVOUCHER = 2;

    /**
     * Reason 3 is for Web
     */
    public const SILKREASONWEB = 3;


    public static function getSilkHistory($jid, $paginate = 10, $page = 1): LengthAwarePaginator
    {
        $minutes = config('global.cache.account_info', 5);

        $data = Cache::remember("account_info_vsro_donate_history_{$jid}_{$paginate}_{$page}", now()->addMinutes($minutes), function () use ($paginate, $page, $jid) {
            return self::select(
                'SK_SilkBuyList.BuyNo',
                'SK_SilkBuyList.OrderNumber',
                'SK_SilkBuyList.Silk_Offset',
                'SK_SilkBuyList.Silk_Remain',
                'SK_SilkBuyList.Silk_Type',
                'SK_SilkBuyList.RegDate'
            )
                ->where('SK_SilkBuyList.UserJID', $jid)
                ->orderBy('SK_SilkBuyList.RegDate', 'desc')
                ->get();
        });

        return new LengthAwarePaginator(
            $data->forPage($page, $paginate)->values(),
            $data->count(),
            $paginate,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
    }

}
