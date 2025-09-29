<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DonateLog;
use App\Models\Referral;
use App\Models\SRO\Account\SkSilk;
use App\Models\SRO\Account\SmcLog;
use App\Models\SRO\Account\TbUser;
use App\Models\SRO\Portal\AphChangedSilk;
use App\Models\SRO\Shard\Char;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        $userCount = TbUser::getTbUserCount();
        $charCount = Char::getCharCount();
        $totalGold = Char::getGoldSum();

        if (config('global.server.version') === 'vSRO') {
            $totalSilk = SkSilk::getSilkSum();
        } else {
            $totalSilk = AphChangedSilk::getSilkSum();
        }

        $phpVersion = phpversion();
        $memoryLimit = ini_get('memory_limit');
        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);
        $diskTotal = disk_total_space('/');
        $diskFree = disk_free_space('/');
        $appDebug = config('app.debug') ? 'true' : 'false';
        $adminCount = \App\Models\User::whereHas('role', function ($q) {$q->where('is_admin', 1);})->count();

        return view('admin.index', [
            'userCount' => $userCount,
            'charCount' => $charCount,
            'totalGold' => $totalGold,
            'totalSilk' => $totalSilk,
            'phpVersion' => $phpVersion,
            'memoryLimit' => $memoryLimit,
            'memoryUsage' => $memoryUsage,
            'memoryPeak' => $memoryPeak,
            'diskTotal' => $diskTotal,
            'diskFree' => $diskFree,
            'appDebug' => $appDebug,
            'adminCount' => $adminCount,
        ]);
    }

    public function referralLogs(Request $request)
    {
        $data = Referral::select('jid', DB::raw('SUM(points) as total_points'))
            ->groupBy('jid')
            ->orderByDesc('total_points')
            ->paginate(20);

        $data->getCollection()->transform(function ($ref) {
            $referral = Referral::where('jid', $ref->jid)->latest()->first();
            return (object)[
                'jid' => $ref->jid,
                'total_points' => $ref->total_points,
                'code' => $referral->code,
                'ip' => $referral->ip,
                'name' => optional($referral->creator)->username,
            ];
        });

        return view('admin.referral-logs', compact('data'));
    }

    public function donateLogs(Request $request)
    {
        $data = DonateLog::query()
            ->when($request->transaction_id, fn($q) =>
            $q->where('transaction_id', 'like', '%' . $request->transaction_id . '%'))
            ->when($request->method_type, fn($q) =>
            $q->where('method', $request->method_type))
            ->when($request->status, fn($q) =>
            $q->where('status', $request->status))
            ->when($request->jid, fn($q) =>
            $q->where('jid', $request->jid))
            ->when($request->ip, fn($q) =>
            $q->where('ip', 'like', '%' . $request->ip . '%'))
            ->latest()
            ->paginate(20);

        return view('admin.donate-logs', compact('data'));
    }

    public function smcLogs(Request $request)
    {
        $query = SmcLog::query();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('szUserID', 'like', "%{$search}%")
                    ->orWhere('szLog', 'like', "%{$search}%");
            });
        }

        $data = $query->orderBy('dLogDate', 'desc')->paginate(20);

        return view('admin.smc-logs', compact('data'));
    }
}
