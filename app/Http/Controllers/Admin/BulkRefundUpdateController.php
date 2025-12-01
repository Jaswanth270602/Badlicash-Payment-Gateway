<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\LogsConditionally;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class BulkRefundUpdateController extends Controller
{
    use LogsConditionally;

    public function index(): View
    {
        $this->logInfo('Admin bulk refund update page accessed', ['user_id' => auth()->id()]);
        return view('admin.payments.bulk-refund-update');
    }

    public function upload(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:csv,xlsx,xls|max:10240',
            ]);

            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('bulk_refunds', $fileName, 'local');

            // Process file and create job record
            $job = DB::table('bulk_refund_jobs')->insertGetId([
                'job_name' => 'Bulk Refund Update - ' . $fileName,
                'file_path' => $filePath,
                'status' => 'processing',
                'progress' => 0,
                'started_at' => now(),
                'user_id' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // TODO: Queue job to process file

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully. Processing started.',
                'job_id' => $job,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload file: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getJobs(Request $request): JsonResponse
    {
        try {
            $perPage = min($request->get('per_page', 5), 50);
            
            $query = DB::table('bulk_refund_jobs')->latest();

            // Filters
            if ($request->has('filter_job_id') && $request->get('filter_job_id')) {
                $query->where('id', 'like', "%{$request->get('filter_job_id')}%");
            }
            if ($request->has('filter_job_name') && $request->get('filter_job_name')) {
                $query->where('job_name', 'like', "%{$request->get('filter_job_name')}%");
            }
            if ($request->has('filter_status') && $request->get('filter_status') !== 'all') {
                $query->where('status', $request->get('filter_status'));
            }

            $jobs = $query->paginate($perPage);

            $data = collect($jobs->items())->map(function($job) {
                return [
                    'id' => $job->id,
                    'job_id' => $job->id,
                    'job_name' => $job->job_name ?? '-',
                    'progress' => $job->progress ?? 0,
                    'status' => $job->status ?? 'pending',
                    'export_files' => $job->export_file_path ?? '-',
                    'started_at' => $job->started_at ? date('Y-m-d H:i:s', strtotime($job->started_at)) : '-',
                    'finished_at' => $job->finished_at ? date('Y-m-d H:i:s', strtotime($job->finished_at)) : '-',
                    'error' => $job->error ?? '-',
                    'status_info' => $job->status_info ?? '-',
                    'user_name' => 'Admin',
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'current_page' => $jobs->currentPage(),
                    'per_page' => $jobs->perPage(),
                    'total' => $jobs->total(),
                    'last_page' => $jobs->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch jobs',
            ], 500);
        }
    }

    public function downloadTemplate(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="bulk_refund_template.csv"',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Refund ID', 'Status', 'Notes']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function downloadStatusFile($id): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $job = DB::table('bulk_refund_jobs')->where('id', $id)->first();
        
        if (!$job || !$job->export_file_path) {
            abort(404, 'File not found');
        }

        return Storage::download($job->export_file_path);
    }
}
