<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    /** The semester picked in the URL (?semester=...), or the default one. */
    protected function selectedSemester(Request $request): string
    {
        $semester = $request->query('semester');

        return in_array($semester, config('hostel.semesters'), true) ? $semester : config('hostel.default_semester');
    }
}
