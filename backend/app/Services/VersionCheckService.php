<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class VersionCheckService
{
    protected string $owner = 'reinehr06-jpg';
    protected string $repo = 'BasileaVendor';

    public function checkForUpdates(): array
    {
        $cacheKey = "github_release_{$this->owner}_{$this->repo}";
        $cached = Cache::get($cacheKey);
        if ($cached) {
            return $cached;
        }

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'BasileiaVendor-Checker'
            ])->timeout(5)->get("https://api.github.com/repos/{$this->owner}/{$this->repo}/releases/latest");

            if ($response->successful()) {
                $latest = $response->json();
                $current = Config::get('app.version', '1.0.0');
                $hasUpdate = version_compare($current, $latest['tag_name'], '<');

                $result = [
                    'current_version' => $current,
                    'latest_version' => $latest['tag_name'],
                    'has_update' => $hasUpdate,
                    'release_url' => $latest['html_url'],
                    'release_notes' => $latest['body'] ?? '',
                    'published_at' => $latest['published_at'],
                    'assets' => $latest['assets'] ?? [],
                ];

                Cache::put($cacheKey, $result, now()->addHours(6));
                return $result;
            }
        } catch (\Exception $e) {
            \Log::warning('Version check failed', ['error' => $e->getMessage()]);
        }

        return ['has_update' => false];
    }
}
