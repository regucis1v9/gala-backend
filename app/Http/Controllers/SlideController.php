<?php

namespace App\Http\Controllers;

use App\Models\Slide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon; 

class SlideController extends Controller
{
    public function saveSlides(Request $request)
    {
        $slides = $request->all();
    
        foreach ($slides as $slide) {
            $validated = Validator::make($slide, [
                'imageLink' => 'required|string',
                'description' => 'nullable|string',
                'textColor' => 'required|string',
                'bgColor' => 'required|string',
                'textPosition' => 'string',
                'startDate' => 'required|date',
                'endDate' => 'required|date',
                'selectedScreens' => 'required|array',
            ]);
    
            if ($validated->fails()) {
                return response()->json(['error' => $validated->errors()], 422);
            }
    
            try {
                $validatedData = $validated->validated();
                Slide::create($validatedData);
            } catch (\Exception $e) {
                \Log::error('Slide creation failed: '.$e->getMessage());
                return response()->json(['error' => $e], 500);
            }
        }
    
        return response()->json(['message' => 'Slides created successfully'], 201);
    }
    
    public function getAllSlides()
    {
        $slides = Slide::all();

        return response()->json($slides);
    }

    public function getTodaysSlides()
    {
        $today = Carbon::now()->setTimezone('Europe/Riga');

        $slides = Slide::where('startDate', '<=', $today)
                        ->where('endDate', '>=', $today)
                        ->get();

        return response()->json($slides);
    }

    public function getSlidesByScreen($screenID)
    {
        $today = Carbon::now()->setTimezone('Europe/Riga');

        $slides = Slide::whereJsonContains('selectedScreens', $screenID)
            ->where('startDate', '<=', $today)
            ->where('endDate', '>=', $today)
            ->get();

        return response()->json($slides);
    }
}
