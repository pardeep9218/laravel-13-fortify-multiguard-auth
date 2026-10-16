<?php

namespace App\Http\Controllers;

use App\Models\Website;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Services\GeminiWebsiteAnalysisService;
use Throwable;

class WebsiteController extends Controller
{
    /**
     * Display the authenticated user's websites.
     */
    public function index(Request $request): View
    {
        $websites = $request->user()
            ->websites()
            ->latest()
            ->paginate(10);

        return view('websites.index', compact('websites'));
    }

    /**
     * Store a website for the authenticated user.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'url' => [
                'required',
                'string',
                'max:2048',
                'url:http,https',
            ],
        ]);

        $domain = parse_url($validated['url'], PHP_URL_HOST);

        if (! is_string($domain) || $domain === '') {
            return back()
                ->withErrors(['url' => 'Please enter a valid website URL.'])
                ->withInput();
        }

        $request->user()->websites()->create([
            'name' => filled($validated['name'])
                ? $validated['name']
                : $domain,
            'url' => $validated['url'],
            'domain' => $domain,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('websites.index')
            ->with('success', 'Website added successfully.');
    }

    public function analyze(
        Request $request,
        Website $website,
        GeminiWebsiteAnalysisService $analyzer
    ): RedirectResponse {
        // Ensure the website belongs to the logged-in user.
        $website = $request->user()
            ->websites()
            ->findOrFail($website->id);

        $website->update([
            'status' => 'analyzing',
            'analysis_error' => null,
        ]);

        try {
            // Send the website URL to Gemini for analysis.
            $analysis = $analyzer->analyze($website->url);

            // Save the analysis to the database.
            $website->update([
                'status' => 'completed',
                'analysis_data' => $analysis,
                'analyzed_at' => now(),
                'analysis_error' => null,
            ]);

            return redirect()
                ->route('websites.index')
                ->with('success', 'Website analyzed successfully!');
        }catch (\Throwable $exception) {
            report($exception);

            \Illuminate\Support\Facades\Log::error(
                'Website AI analysis failed.',
                [
                    'website_id' => $website->id,
                    'url' => $website->url,
                    'exception' => get_class($exception),
                    'message' => $exception->getMessage(),
                ]
            );

            $errorMessage = config('app.debug')
                ? $exception->getMessage()
                : 'Website analysis failed. Check the application logs for details.';

            $website->update([
                'status' => 'failed',
                'analysis_error' => $errorMessage,
            ]);

            return redirect()
                ->route('websites.index')
                ->with('error', $errorMessage);
        }
    }
}