<?php

namespace App\Http\Controllers;

use App\Providers\RouteServiceProvider;
use App\Services\DashboardMetricsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardMetricsService $metrics)
    {
    }

    public function index(Request $request)
    {
        if (! $request->user()->canViewModule('dashboard')) {
            return redirect(RouteServiceProvider::homeFor($request->user()));
        }

        return view('dashboard', [
            'dashboard' => $this->metrics->forUser(
                $request->user(),
                $request->integer('renewal_year')
            ),
        ]);
    }

    public function metrics(Request $request): JsonResponse
    {
        abort_unless($request->user()->canViewModule('dashboard'), 403);

        return response()->json(
            $this->metrics->forUser(
                $request->user(),
                $request->integer('renewal_year')
            )
        );
    }
}
