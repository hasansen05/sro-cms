<?php

namespace App\Models\SRO\Shard;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BuyCashItemListByWeb extends Model
{
    /**
     * The Database connection name for the model.
     *
     * @var string
     */
    protected $connection = 'shard';

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
    protected $table = 'dbo._BuyCashItemList_By_Web';

    /**
     * The table primary Key
     *
     * @var string
     */
    protected $primaryKey = 'IDX';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

    ];

    public static function addItemToChest(Char $char, int $itemID, int $quantity, string $itemCode): void
    {
        try {
            $jid = $char->JID;
            if (!$jid) {
                throw new \InvalidArgumentException('Character not found in _User');
            }

            $accountPdo = DB::connection('account')->getPdo();

            $stmt1 = $accountPdo->prepare("INSERT INTO WEB_ITEM_GIVE_LIST VALUES (:jid, 64, :charid, 130, :itemcode, NULL, 'SN_' + :itemcodestr, 1, 0, 0, 0, 0, 0, 'Web', 0, GETDATE(), NULL, NULL, NULL)");
            $stmt1->execute([
                ':jid' => $jid,
                ':charid' => $char->CharID,
                ':itemcode' => $itemCode,
                ':itemcodestr' => $itemCode,
            ]);

            $identity = (int) $accountPdo->lastInsertId();

            $shardPdo = DB::connection('shard')->getPdo();
            $stmt2 = $shardPdo->prepare("INSERT INTO _BuyCashItemList_By_Web VALUES (:id, :jid2, 1, 'SN_' + :itemcodestr2, :count, :itemid, :totalcount, GETDATE(), NULL, NULL, NULL, NULL, NULL, NULL)");
            $stmt2->execute([
                ':id' => $identity,
                ':jid2' => $jid,
                ':itemcodestr2' => $itemCode,
                ':count' => $quantity,
                ':itemid' => $itemID,
                ':totalcount' => $quantity,
            ]);
        } catch (\InvalidArgumentException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new \InvalidArgumentException($e->getMessage());
        }
    }

    public static function getWebChest($JID, $page = null)
    {
        $page ??= request()->get('page', 1);

        return Cache::remember("character_info_web_chest_{$JID}_page_{$page}", config('global.cache.character_info', 86400), function () use ($JID, $page) {
                return self::select(
                    '_BuyCashItemList_By_Web.*',
                    '_Rigid_ItemNameDesc.ENG as ItemName',
                    DB::raw("REPLACE(REPLACE(_RefObjCommon.AssocFileIcon128, '\\\\', '/'), '.ddj', '') as IconPath")
                )
                ->join('_RefObjCommon', '_BuyCashItemList_By_Web.RefItemID', '=', '_RefObjCommon.ID')
                ->leftJoin(DB::raw(DB::connection('account')->getDatabaseName() .'.dbo._Rigid_ItemNameDesc'), '_Rigid_ItemNameDesc.StrID', '=', '_RefObjCommon.NameStrID128')
                ->join('_RefObjItem', '_RefObjCommon.Link', '=', '_RefObjItem.ID')
                ->join('_User', '_BuyCashItemList_By_Web.JID', '=', '_User.UserJID')
                ->join('_Char', '_User.CharID', '=', '_Char.CharID')
                ->where('_BuyCashItemList_By_Web.JID', $JID)
                ->where('_BuyCashItemList_By_Web.RefItemID', '!=', 0)
                ->orderBy('_BuyCashItemList_By_Web.RegDate', 'desc')
                ->paginate(10);
        });
    }
}
