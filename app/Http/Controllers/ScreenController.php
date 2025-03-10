<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Screen;

class ScreenController extends Controller
{
    public function addScreen(Request $request)
    {
        $existingScreens = Screen::all()->pluck('table_name')->toArray();
        $numbers = [];
        foreach ($existingScreens as $screen) {
            if (preg_match('/Ekrāns(\d+)/', $screen, $matches)) {
                $numbers[] = (int)$matches[1];
            }
        }
        $nextScreenNumber = 1;
        while (in_array($nextScreenNumber, $numbers)) {
            $nextScreenNumber++;
        }
        $newScreenName = 'Ekrāns' . $nextScreenNumber;
        $newScreen = Screen::create(['table_name' => $newScreenName]);
    
        return response()->json([
            'message' => 'Screen created successfully',
            'screen' => $newScreen,
        ],201);
    }
    
    public function getAllScreens()
    {
        $screens = Screen::all();
        return response()->json($screens);
    }

    public function deleteScreen(Request $request)
    {
        $id = $request->input('id');

        $screen = Screen::find($id);
        if ($screen) {
            $screen->delete();

            return response()->json([
                'message' => 'Ekrāns veiskmīgi izdēsts ',
            ],200);
        } else {
            return response()->json([
                'message' => 'Ekrāns nav atrasts',
            ], 404);
        }
    }
}