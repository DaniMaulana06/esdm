<?php

namespace App\Http\Controllers;

use App\Services\WelcomeService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WelcomeController extends Controller
{
   
    public function __invoke(Request $request, WelcomeService $welcomeService): Response
    {
        $currentMonth = (string) date('n');
        $currentYear = (string) date('Y');

        $bulan = $request->query('bulan', $currentMonth);
        $tahun = $request->query('tahun', $currentYear);

        $bkuList = $welcomeService->getBkuListWithProduction($bulan, $tahun);
        $tahunTersedia = $welcomeService->getAvailableYears();

        return Inertia::render('Welcome', [
            'bkuList' => $bkuList,
            'tahunTersedia' => $tahunTersedia,
            'filters' => [
                'bulan' => (string) $bulan,
                'tahun' => (string) $tahun,
            ],
        ]);
    }
}
