<?php

namespace App\Http\Controllers;

use App\Models\EPSSubmission;
use Illuminate\Http\Request;
use ZipArchive;

class EPSSubmissionController extends Controller
{
    // GEt EPS Sumbissions

    public function getEPSSubmissions(){
        $eps_submissions=EPSSubmission::get();
        return response()->json($eps_submissions,200);
    }

    // Download Files
    public function downloadEPSSubmissionFiles(Request $request)
    {
        $fps_submission = EPSSubmission::find($request->submission_id);
        $files = [
            public_path($fps_submission->submission_xml),
            public_path($fps_submission->response_xml)
        ];

        // Define ZIP file name
        $zipFileName = 'eps.zip';
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
