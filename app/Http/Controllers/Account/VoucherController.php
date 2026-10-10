<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Donate;
use App\Models\SRO\Account\TbUser;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class VoucherController extends Controller
{
    public function index(Request $request)
    {
        $data = Voucher::getUserVoucher($request->user()->jid);

        return view('account.voucher.index', [
            'data' => $data,
        ]);
    }

    public function redeem(Request $request)
    {
        $request->validate([
            'voucher_code' => 'required|string|max:50',
        ]);

        $code = strtoupper(trim($request->voucher_code));
        $user = $request->user();

        // The voucher row is locked and every guard re-checked inside the
        // transaction, so two simultaneous requests cannot both redeem it.
        try {
            DB::transaction(function () use ($code, $user) {
                $voucher = Voucher::where('code', $code)->lockForUpdate()->first();

                if (!$voucher) {
                    throw new \RuntimeException('Invalid voucher code.');
                }

                if ($voucher->jid === $user->jid) {
                    throw new \RuntimeException('You have already redeemed this voucher.');
                }

                if ($voucher->status == 'Disabled') {
                    throw new \RuntimeException('Invalid voucher code.');
                }

                if ($voucher->status == 'Used') {
                    throw new \RuntimeException('This voucher has already been used.');
                }

                if ($voucher->valid_date && Carbon::now()->greaterThan($voucher->valid_date)) {
                    throw new \RuntimeException('This voucher has expired.');
                }

                TbUser::updateSilk($user->jid, $voucher->type, $voucher->amount);

                Donate::log([
                    'method' => 'Voucher',
                    'value' => $voucher->amount,
                    'jid' => $user->jid,
                ]);

                $voucher->update(['jid' => $user->jid, 'status' => 'Used']);
            });
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        Cache::forget("vouchers_user_{$user->jid}");

        return redirect()->back()->with('success', 'Voucher redeemed successfully!');
    }
}
