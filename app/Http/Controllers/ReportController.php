<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function generateReport()
    {
        return view('report.generateReport');
    }
}
