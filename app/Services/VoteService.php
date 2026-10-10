<?php

namespace App\Services;

use App\Models\Donate;
use App\Models\SRO\Account\TbUser;
use App\Models\User;
use App\Models\Vote;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VoteService
{
    public function postbackXtremetop100(Request $request)
    {
        $config = config("vote.xtremetop100");
        // Only the TrustProxies-resolved IP; CF-Connecting-IP is client-spoofable.
        $remoteIp = $request->ip() ?? '0.0.0.0';

        $allowedIps = array_map('trim', explode(',', $config['ip']));
        if (!in_array($remoteIp, $allowedIps, true)) {
            Log::warning("Unauthorized {$config['name']} postback", ['ip' => $remoteIp]);
            return response("Unauthorized IP: {$remoteIp}", 401);
        }

        $jid = $request->input('custom');
        if (!$jid) {
            return response('Missing user ID', 400);
        }

        $user = User::where('jid', (int)$jid)->first();
        if (!$user) {
            return response('User not found', 200);
        }

        // Cooldown row locked + re-checked in-transaction: concurrent postbacks
        // serialize and only one grants the reward per timeout window.
        $timeout = $config['timeout'] ?? 12;
        $rewardAmount = $config['reward'] ?? 0;

        $outcome = DB::transaction(function () use ($user, $rewardAmount, $config, $jid, $remoteIp, $timeout) {
            $now = Carbon::now();

            $voteLog = Vote::where('jid', $jid)->where('site', $config['route'])->lockForUpdate()->first();
            if ($voteLog && $voteLog->expire && $now->lessThan(Carbon::parse($voteLog->expire))) {
                return 'cooldown:' . $voteLog->expire;
            }

            TbUser::updateSilk($user->jid, $config['type'] ?? 0, $rewardAmount);

            Donate::log([
                'method' => "Vote [{$config['name']}]",
                'value' => $rewardAmount,
                'jid' => $user->jid,
            ]);

            Vote::updateOrCreate(['jid' => $jid, 'site' => $config['route']], ['ip' => $remoteIp, 'expire' => $now->addHours($timeout)]);

            return 'ok';
        });

        if (str_starts_with($outcome, 'cooldown:')) {
            return response('Cooldown active until ' . substr($outcome, 9), 200);
        }

        return response("OK", 200);
    }

    public function postbackGtop100(Request $request)
    {
        $config = config("vote.gtop100");
        // Only the TrustProxies-resolved IP; CF-Connecting-IP is client-spoofable.
        $remoteIp = $request->ip() ?? '0.0.0.0';

        $allowedIps = array_map('trim', explode(',', $config['ip']));
        if (!in_array($remoteIp, $allowedIps, true)) {
            Log::warning("Unauthorized {$config['name']} postback", ['ip' => $remoteIp]);
            return response("Unauthorized IP: {$remoteIp}", 401);
        }

        $jid = $request->input('pingUsername');
        if (!$jid) {
            return response('Missing user ID', 400);
        }

        if((int)$request->input('Successful') !== 1) {
            return response($request->input('Reason') ?? 'Vote not successful', 200);
        }

        $user = User::where('jid', (int)$jid)->first();
        if (!$user) {
            return response('User not found', 200);
        }

        // Cooldown row locked + re-checked in-transaction: concurrent postbacks
        // serialize and only one grants the reward per timeout window.
        $timeout = $config['timeout'] ?? 12;
        $rewardAmount = $config['reward'] ?? 0;

        $outcome = DB::transaction(function () use ($user, $rewardAmount, $config, $jid, $remoteIp, $timeout) {
            $now = Carbon::now();

            $voteLog = Vote::where('jid', $jid)->where('site', $config['route'])->lockForUpdate()->first();
            if ($voteLog && $voteLog->expire && $now->lessThan(Carbon::parse($voteLog->expire))) {
                return 'cooldown:' . $voteLog->expire;
            }

            TbUser::updateSilk($user->jid, $config['type'] ?? 0, $rewardAmount);

            Donate::log([
                'method' => "Vote [{$config['name']}]",
                'value' => $rewardAmount,
                'jid' => $user->jid,
            ]);

            Vote::updateOrCreate(['jid' => $jid, 'site' => $config['route']], ['ip' => $remoteIp, 'expire' => $now->addHours($timeout)]);

            return 'ok';
        });

        if (str_starts_with($outcome, 'cooldown:')) {
            return response('Cooldown active until ' . substr($outcome, 9), 200);
        }

        return response("OK", 200);
    }

    public function postbackTopg(Request $request)
    {
        $config = config("vote.topg");
        // Only the TrustProxies-resolved IP; CF-Connecting-IP is client-spoofable.
        $remoteIp = $request->ip() ?? '0.0.0.0';

        $allowedIps = array_map('trim', explode(',', $config['ip']));
        if (!in_array($remoteIp, $allowedIps, true)) {
            Log::warning("Unauthorized {$config['name']} postback", ['ip' => $remoteIp]);
            return response("Unauthorized IP: {$remoteIp}", 401);
        }

        $jid = $request->input('p_resp');
        if (!$jid) {
            return response('Missing user ID', 400);
        }

        $user = User::where('jid', (int)$jid)->first();
        if (!$user) {
            return response('User not found', 200);
        }

        // Cooldown row locked + re-checked in-transaction: concurrent postbacks
        // serialize and only one grants the reward per timeout window.
        $timeout = $config['timeout'] ?? 12;
        $rewardAmount = $config['reward'] ?? 0;

        $outcome = DB::transaction(function () use ($user, $rewardAmount, $config, $jid, $remoteIp, $timeout) {
            $now = Carbon::now();

            $voteLog = Vote::where('jid', $jid)->where('site', $config['route'])->lockForUpdate()->first();
            if ($voteLog && $voteLog->expire && $now->lessThan(Carbon::parse($voteLog->expire))) {
                return 'cooldown:' . $voteLog->expire;
            }

            TbUser::updateSilk($user->jid, $config['type'] ?? 0, $rewardAmount);

            Donate::log([
                'method' => "Vote [{$config['name']}]",
                'value' => $rewardAmount,
                'jid' => $user->jid,
            ]);

            Vote::updateOrCreate(['jid' => $jid, 'site' => $config['route']], ['ip' => $remoteIp, 'expire' => $now->addHours($timeout)]);

            return 'ok';
        });

        if (str_starts_with($outcome, 'cooldown:')) {
            return response('Cooldown active until ' . substr($outcome, 9), 200);
        }

        return response("OK", 200);
    }

    public function postbackTop100arena(Request $request)
    {
        $config = config("vote.top100arena");
        // Only the TrustProxies-resolved IP; CF-Connecting-IP is client-spoofable.
        $remoteIp = $request->ip() ?? '0.0.0.0';

        $allowedIps = array_map('trim', explode(',', $config['ip']));
        if (!in_array($remoteIp, $allowedIps, true)) {
            Log::warning("Unauthorized {$config['name']} postback", ['ip' => $remoteIp]);
            return response("Unauthorized IP: {$remoteIp}", 401);
        }

        $jid = $request->input('postback');
        if (!$jid) {
            return response('Missing user ID', 400);
        }

        $user = User::where('jid', (int)$jid)->first();
        if (!$user) {
            return response('User not found', 200);
        }

        // Cooldown row locked + re-checked in-transaction: concurrent postbacks
        // serialize and only one grants the reward per timeout window.
        $timeout = $config['timeout'] ?? 12;
        $rewardAmount = $config['reward'] ?? 0;

        $outcome = DB::transaction(function () use ($user, $rewardAmount, $config, $jid, $remoteIp, $timeout) {
            $now = Carbon::now();

            $voteLog = Vote::where('jid', $jid)->where('site', $config['route'])->lockForUpdate()->first();
            if ($voteLog && $voteLog->expire && $now->lessThan(Carbon::parse($voteLog->expire))) {
                return 'cooldown:' . $voteLog->expire;
            }

            TbUser::updateSilk($user->jid, $config['type'] ?? 0, $rewardAmount);

            Donate::log([
                'method' => "Vote [{$config['name']}]",
                'value' => $rewardAmount,
                'jid' => $user->jid,
            ]);

            Vote::updateOrCreate(['jid' => $jid, 'site' => $config['route']], ['ip' => $remoteIp, 'expire' => $now->addHours($timeout)]);

            return 'ok';
        });

        if (str_starts_with($outcome, 'cooldown:')) {
            return response('Cooldown active until ' . substr($outcome, 9), 200);
        }

        return response("OK", 200);
    }

    public function postbackArenatop100(Request $request)
    {
        $config = config("vote.arenatop100");
        // Only the TrustProxies-resolved IP; CF-Connecting-IP is client-spoofable.
        $remoteIp = $request->ip() ?? '0.0.0.0';

        $allowedIps = array_map('trim', explode(',', $config['ip']));
        if (!in_array($remoteIp, $allowedIps, true)) {
            Log::warning("Unauthorized {$config['name']} postback", ['ip' => $remoteIp]);
            return response("Unauthorized IP: {$remoteIp}", 401);
        }

        $jid = $request->input('userid');
        if (!$jid) {
            return response('Missing user ID', 400);
        }

        if ((int)$request->input('voted') !== 1) {
            return response("User $jid voted already today!", 200);
        }

        $user = User::where('jid', (int)$jid)->first();
        if (!$user) {
            return response('User not found', 200);
        }

        // Cooldown row locked + re-checked in-transaction: concurrent postbacks
        // serialize and only one grants the reward per timeout window.
        $timeout = $config['timeout'] ?? 12;
        $rewardAmount = $config['reward'] ?? 0;

        $outcome = DB::transaction(function () use ($user, $rewardAmount, $config, $jid, $remoteIp, $timeout) {
            $now = Carbon::now();

            $voteLog = Vote::where('jid', $jid)->where('site', $config['route'])->lockForUpdate()->first();
            if ($voteLog && $voteLog->expire && $now->lessThan(Carbon::parse($voteLog->expire))) {
                return 'cooldown:' . $voteLog->expire;
            }

            TbUser::updateSilk($user->jid, $config['type'] ?? 0, $rewardAmount);

            Donate::log([
                'method' => "Vote [{$config['name']}]",
                'value' => $rewardAmount,
                'jid' => $user->jid,
            ]);

            Vote::updateOrCreate(['jid' => $jid, 'site' => $config['route']], ['ip' => $remoteIp, 'expire' => $now->addHours($timeout)]);

            return 'ok';
        });

        if (str_starts_with($outcome, 'cooldown:')) {
            return response('Cooldown active until ' . substr($outcome, 9), 200);
        }

        return response("OK", 200);
    }

    public function postbackSilkroadservers(Request $request)
    {
        $config = config("vote.silkroadservers");
        // Only the TrustProxies-resolved IP; CF-Connecting-IP is client-spoofable.
        $remoteIp = $request->ip() ?? '0.0.0.0';

        $allowedIps = array_map('trim', explode(',', $config['ip']));
        if (!in_array($remoteIp, $allowedIps, true)) {
            Log::warning("Unauthorized {$config['name']} postback", ['ip' => $remoteIp]);
            return response("Unauthorized IP: {$remoteIp}", 401);
        }

        $jid = $request->input('userid');
        if (!$jid) {
            return response('Missing user ID', 400);
        }

        $user = User::where('jid', (int)$jid)->first();
        if (!$user) {
            return response('User not found', 200);
        }

        // Cooldown row locked + re-checked in-transaction: concurrent postbacks
        // serialize and only one grants the reward per timeout window.
        $timeout = $config['timeout'] ?? 12;
        $rewardAmount = $config['reward'] ?? 0;

        $outcome = DB::transaction(function () use ($user, $rewardAmount, $config, $jid, $remoteIp, $timeout) {
            $now = Carbon::now();

            $voteLog = Vote::where('jid', $jid)->where('site', $config['route'])->lockForUpdate()->first();
            if ($voteLog && $voteLog->expire && $now->lessThan(Carbon::parse($voteLog->expire))) {
                return 'cooldown:' . $voteLog->expire;
            }

            TbUser::updateSilk($user->jid, $config['type'] ?? 0, $rewardAmount);

            Donate::log([
                'method' => "Vote [{$config['name']}]",
                'value' => $rewardAmount,
                'jid' => $user->jid,
            ]);

            Vote::updateOrCreate(['jid' => $jid, 'site' => $config['route']], ['ip' => $remoteIp, 'expire' => $now->addHours($timeout)]);

            return 'ok';
        });

        if (str_starts_with($outcome, 'cooldown:')) {
            return response('Cooldown active until ' . substr($outcome, 9), 200);
        }

        return response("OK", 200);
    }

    public function postbackPrivateserver(Request $request)
    {
        $config = config("vote.privateserver");
        // Only the TrustProxies-resolved IP; CF-Connecting-IP is client-spoofable.
        $remoteIp = $request->ip() ?? '0.0.0.0';

        $allowedIps = array_map('trim', explode(',', $config['ip']));
        if (!in_array($remoteIp, $allowedIps, true)) {
            Log::warning("Unauthorized {$config['name']} postback", ['ip' => $remoteIp]);
            return response("Unauthorized IP: {$remoteIp}", 401);
        }

        $jid = $request->input('userid');
        if (!$jid) {
            return response('Missing user ID', 400);
        }

        $user = User::where('jid', (int)$jid)->first();
        if (!$user) {
            return response('User not found', 200);
        }

        // Cooldown row locked + re-checked in-transaction: concurrent postbacks
        // serialize and only one grants the reward per timeout window.
        $timeout = $config['timeout'] ?? 12;
        $rewardAmount = $config['reward'] ?? 0;

        $outcome = DB::transaction(function () use ($user, $rewardAmount, $config, $jid, $remoteIp, $timeout) {
            $now = Carbon::now();

            $voteLog = Vote::where('jid', $jid)->where('site', $config['route'])->lockForUpdate()->first();
            if ($voteLog && $voteLog->expire && $now->lessThan(Carbon::parse($voteLog->expire))) {
                return 'cooldown:' . $voteLog->expire;
            }

            TbUser::updateSilk($user->jid, $config['type'] ?? 0, $rewardAmount);

            Donate::log([
                'method' => "Vote [{$config['name']}]",
                'value' => $rewardAmount,
                'jid' => $user->jid,
            ]);

            Vote::updateOrCreate(['jid' => $jid, 'site' => $config['route']], ['ip' => $remoteIp, 'expire' => $now->addHours($timeout)]);

            return 'ok';
        });

        if (str_starts_with($outcome, 'cooldown:')) {
            return response('Cooldown active until ' . substr($outcome, 9), 200);
        }

        return response("OK", 200);
    }

    public function postbackVote4rewards(Request $request)
    {
        $config = config("vote.vote4rewards");
        // Only the TrustProxies-resolved IP; CF-Connecting-IP is client-spoofable.
        $remoteIp = $request->ip() ?? '0.0.0.0';

        $allowedIps = array_map('trim', explode(',', $config['ip']));
        if (!in_array($remoteIp, $allowedIps, true)) {
            Log::warning("Unauthorized {$config['name']} postback", ['ip' => $remoteIp]);
            return response("Unauthorized IP: {$remoteIp}", 401);
        }

        $serverId = $request->input('server_uuid');
        $webhookSecret = (string) ($config['webhook_secret'] ?? '');
        $signature = $request->header('X-Webhook-Signature');
        if ($webhookSecret === '' || !is_string($signature) || !hash_equals($webhookSecret, $signature)) {
            return response('Wrong Signature', 403);
        }

        $jid = $request->input('voter_id');
        if (!$jid) {
            return response('Missing user ID', 400);
        }

        $user = User::where('jid', (int)$jid)->first();
        if (!$user) {
            return response('User not found', 200);
        }

        // Cooldown row locked + re-checked in-transaction (see other handlers).
        $timeout = $config['timeout'] ?? 6;
        $rewardAmount = $config['reward'] ?? 0;

        $outcome = DB::transaction(function () use ($user, $rewardAmount, $config, $jid, $remoteIp, $timeout) {
            $now = Carbon::now();

            $voteLog = Vote::where('jid', $jid)->where('site', $config['route'])->lockForUpdate()->first();
            if ($voteLog && $voteLog->expire && $now->lessThan(Carbon::parse($voteLog->expire))) {
                return 'cooldown:' . $voteLog->expire;
            }

            TbUser::updateSilk($user->jid, $config['type'] ?? 0, $rewardAmount);

            Donate::log([
                'method' => "Vote [{$config['name']}]",
                'value' => $rewardAmount,
                'jid' => $user->jid,
            ]);

            Vote::updateOrCreate(['jid' => $jid, 'site' => $config['route']], ['ip' => $remoteIp, 'expire' => $now->addHours($timeout)]);

            return 'ok';
        });

        if (str_starts_with($outcome, 'cooldown:')) {
            return response('Cooldown active until ' . substr($outcome, 9), 200);
        }

        return response("OK", 200);
    }
}
