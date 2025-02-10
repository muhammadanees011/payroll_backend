<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FPSSubmission;
use ZipArchive;
use App\Http\Resources\FPSSubmissionResource;

class FPSSubmissionController extends Controller
{
    // GEt FPS Sumbissions
    public function getFPSSubmissions(){

        $fps_submissions = FPSSubmissionResource::collection(
        FPSSubmission::with('payroll.payschedule')->get()     
        );
        return response()->json($fps_submissions,200);
    }

    // Download Files
    public function downloadFPSSubmissionFiles(Request $request)
    {
        $fps_submission = FPSSubmission::find($request->submission_id);
        $files = [
            public_path($fps_submission->submission_xml),
            public_path($fps_submission->response_xml)
        ];

        // Define ZIP file name
        $zipFileName = 'fps.zip';
        $zipFilePath = storage_path($zipFileName);

        // Create ZIP archive
        $zip = new ZipArchive;
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach ($files as $file) {
                if (file_exists($file)) {
                    $zip->addFile($file, basename($file));
                }
            }
            $zip->close();
        } else {
            return response()->json(['error' => 'Could not create ZIP file'], 500);
        }
        return response()->download($zipFilePath);
    }
}
