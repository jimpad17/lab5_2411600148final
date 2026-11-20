<?php

namespace App\Http\Middleware;

use App\Services\ActivityLogger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LogActivity
{
    /**
     * Records page visits and searches. CRUD and auth events are logged by
     * controllers / auth listeners so descriptions can be more specific.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Remember the last real page so the fallback route can bring the user
        // back here after typing a random / broken URL. Only valid GET module
        // pages are stored - never the fallback itself, otherwise it loops.
        if ($request->isMethod('get')
            && ! $request->routeIs('login', 'register', 'password.*', 'verification.*', 'fallback')) {
            $request->session()->put('last_page_url', $request->getRequestUri());
        }

        if (! Auth::check() || ! $request->isMethod('get')) {
            return $response;
        }

        if ($request->routeIs('login', 'register', 'password.*', 'verification.*', 'fallback')) {
            return $response;
        }

        $module = $this->moduleFor($request);
        if (! $module) {
            return $response;
        }

        if ($request->filled('search')) {
            ActivityLogger::log(
                'searched',
                $module,
                'Searched '.$module.' for "'.strip_tags((string) $request->input('search')).'".'
            );
        } else {
            ActivityLogger::log('viewed', $module, $this->descriptionFor($module));
        }

        return $response;
    }

    private function moduleFor(Request $request): ?string
    {
        $routeName = $request->route()?->getName() ?? '';
        $prefix = explode('.', $routeName)[0];

        $modules = [
            'dashboard' => 'Dashboard',
            'inventory' => 'Inventory',
            'products' => 'Products',
            'brands' => 'Brands',
            'categories' => 'Categories',
            'users' => 'Users',
            'activity-logs' => 'Activity Log',
            'reports' => 'Reports',
            'profile' => 'Profile',
        ];

        return $modules[$prefix] ?? null;
    }

    private function descriptionFor(string $module): string
    {
        if ($module === 'Dashboard') {
            return 'Opened the dashboard.';
        }

        if ($module === 'Profile') {
            return 'Opened the profile page.';
        }

        return 'Opened the '.$module.' list.';
    }
}
