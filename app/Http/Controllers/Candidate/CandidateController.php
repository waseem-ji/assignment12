<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\TestToken;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CandidateController extends Controller
{
    public function login()
    {
        return view('candidate.login');
    }

    public function verifyToken(Request $request)
    {
        $request->validate([
            'token' => ['required','digits:6']
        ]);
        echo $request->token;
        $tokenData = TestToken::where('token', $request->token);

        $tokenData = $tokenData->firstOr(function () {
            return 0;
        });
        if (!$tokenData) {
            return redirect()->back()->withErrors(['error' => 'The access Code does not exists. Please carefully check and re-enter the code. If the problem persists contact the invigilator ']);
        }

        $tokenData = $tokenData->where('token', $request->token)->where('is_expired', false)->firstOr(function () {
            return 1;
        });

        if ($tokenData === 1) {
            return redirect()->back()->withErrors(['error' => 'The access Code has expired ']);
        }
        return redirect(route('candidate.email', $tokenData));
    }

    public function emailView(TestToken $token)
    {
        return view('candidate.email', compact(['token']));
    }

    public function checkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required','email'],
            'drive_id' => ['required'],
        ]);

        $candidate = Candidate::where('email', $request->email)->first();

        if ($candidate) {
            return view('candidate.details', compact(['candidate']));
        } else {
            $candidate = Candidate::create([
                'email' => $request->email,
                'drive_id' => $request->drive_id,
            ]);


            return redirect(route('candidate.create', $candidate->id));
        }
    }

    public function registerView(Candidate $candidate)
    {
        return view('candidate.register', compact('candidate'));
    }

    public function register(Candidate $candidate, Request $request)
    {
        $request->validate([
            'name' => ['required'],
            'phone_number' => ['required' ,'digits:10'],
            'position' => ['required']
        ]);

        $candidate->update([
            'name' => $request->name,
            'phone' => $request->phone_number,
            'position' => $request->position
        ]);
        return view('candidate.details', compact(['candidate']));
    }

    public function edit(Candidate $candidate)
    {
        return view('candidate.edit', compact(['candidate']));
    }

    public function update(Request $request,Candidate $candidate)
    {
        // dd($candidate);
        $request->validate([
            'email' => ['required','email',  Rule::unique('candidates')->ignore($candidate->id)],
            'name' => ['required'],
            'phone_number' => ['required' ,'digits:10'],
            'position' => ['required']
        ]);

        $candidate->update([
            'email' => $request->email,
            'name' => $request->name,
            'phone' => $request->phone_number,
            'position' => $request->position
        ]);

        return view('candidate.details',compact(['candidate']));

    }
}
