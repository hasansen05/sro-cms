<?php

namespace App\Http\Controllers;

use App\Models\VoteLog;
use App\Services\VoteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class VoteController extends Controller
{
    public function index()
    {
        $data = config('vote');
        return view('profile.vote', compact('data'));
    }

    public function voting($site, Request $request)
    {
        $request->validate([
            'fingerprint' => 'required|string|max:255',
        ]);
        $config = config("vote.{$site}");
        $user = Auth::user();
        $now = Carbon::now();
        $ip = $request->ip();
        $fingerprint = $request->input('fingerprint');

        $voteLog = VoteLog::where('site', $config['route'])
            ->where(function($q) use ($ip, $fingerprint) {
                $q->where('ip', $ip)->orWhere('fingerprint', $fingerprint);
            })
            ->whereNotNull('expire')
            ->where('expire', '>', $now)
            ->first();

        if ($voteLog) {
            return redirect()->back()->with('error', "You (or someone using your device) have already voted and must wait until {$voteLog->expire} to vote again for {$config['name']}.");
        }

        VoteLog::updateOrCreate(
            ['jid' => $user->jid, 'site' => $config['route']],
            ['ip' => $ip, 'fingerprint' => $fingerprint]
        );

        $url = str_replace('{JID}', $user->jid, $config['url']);
        return redirect()->away($url);
    }

    public function postback($site, Request $request, VoteService $voteService)
    {
        $config = config("vote.{$site}");

        if (!$config || !$config['enabled']) {
            return redirect()->back()->withErrors('Vote Site not found or disabled.');
        }

        if (method_exists($voteService, "postback" . ucfirst($site))) {
            return $voteService->{"postback" . ucfirst($site)}($request);
        }

        return redirect()->back()->withErrors('Invalid postback method.');
    }
}
