<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Project;
use App\Services\AnalyzerInterface;

class ApiController extends Controller
{
    public function getData($name, $from, $to)
    {
        return redirect()->route('review.generateapi', array($name, $from, $to));
    }
}
