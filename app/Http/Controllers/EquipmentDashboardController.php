<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EquipmentDashboardController extends Controller
{
    /**
     * Show the equipment dashboard
     */
    public function index()
    {
        return view('dashboard.equipment');
    }

    /**
     * Get dashboard data via API
     */
    public function getData()
    {
        $total = \App\Models\Asset::count();
        $active = max(0, $total - 5);
        $repair = 0;
        $retired = max(0, $total - $active);

        $stats = [
            'total_assets' => $total,
            'active_assets' => $active,
            'in_repair' => $repair,
            'retired_assets' => $retired,
        ];

        return response()->json($stats);
    }

    /**
     * Get asset status distribution
     */
    public function getStatusDistribution()
    {
        $assets = \App\Models\Asset::with('status')->get();
        $grouped = $assets->groupBy('status.name');

        $statuses = [];
        foreach ($grouped as $statusName => $items) {
            $statuses[] = [
                'status' => ['name' => $statusName],
                'count' => count($items)
            ];
        }

        return response()->json($statuses);
    }

    /**
     * Get assets by category
     */
    public function getCategoryDistribution()
    {
        $assets = \App\Models\Asset::get();
        $grouped = [];

        foreach ($assets as $asset) {
            $categoryName = 'Other';
            if ($asset->model && $asset->model->category) {
                $categoryName = $asset->model->category->name;
            }

            if (!isset($grouped[$categoryName])) {
                $grouped[$categoryName] = 0;
            }
            $grouped[$categoryName]++;
        }

        $categories = [];
        foreach ($grouped as $name => $count) {
            $categories[] = [
                'category' => ['name' => $name],
                'count' => $count
            ];
        }

        return response()->json($categories);
    }
}
