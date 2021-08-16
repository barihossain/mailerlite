<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use App\Rules\CheckApiKeyRule;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    /**
     * Display a MailerLite API key add/update page.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $apiKeySetitng = Settings::where('key', 'MAILERLITE_API_KEY')->first();
        if (is_null($apiKeySetitng)) {
            $apiKey = null;
        } else {
            $apiKey = $apiKeySetitng->value;
        }

        return view('welcome', [
            'title' => 'Welcome',
            'api_key' => $apiKey,
        ]);
    }

    /**
     * Store MailerLite API key.
     *
     * @param  Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'api_key' => [
                'required',
                new CheckApiKeyRule(),
            ],
        ]);

        $apiKey = $request->post('api_key');
        try {
            Settings::create([
                'key' => 'MAILERLITE_API_KEY',
                'value' => $apiKey,
            ]);
        } catch (\Exception$e) {
            return redirect()->back()->with('message', ['type' => 'danger', 'body' => 'Something went wrong. Please try again.']);
        }

        return redirect()->route('home')->with('message', ['type' => 'success', 'body' => 'MailerLite API Key successfully saved.']);
    }

    /**
     * Update MailerLite API key
     *
     * @param \App\Http\Requests\Address\AddressRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $request->validate([
            'api_key' => [
                'required',
                new CheckApiKeyRule(),
            ],
        ]);

        $apiKey = $request->post('api_key');
        try {
            Settings::where('key', 'MAILERLITE_API_KEY')->update([
                'value' => $apiKey,
            ]);
        } catch (\Exception$e) {
            return redirect()->back()->with('message', ['type' => 'danger', 'body' => 'Something went wrong. Please try again.']);
        }

        return redirect()->route('home')->with('message', ['type' => 'success', 'body' => 'MailerLite API Key successfully updated.']);
    }
}
