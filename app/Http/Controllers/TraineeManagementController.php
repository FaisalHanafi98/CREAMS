<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Trainees;
use App\Models\Centres;
use Carbon\Carbon;

class TraineeManagementController extends Controller
{
    /**
     * Display a listing of the trainees
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            // Fetch all trainees with related data
            $trainees = Trainees::orderBy('id', 'desc')->get();
            
            // Get statistics data
            $totalTrainees = $trainees->count();
            $centerCount = $trainees->pluck('centre_name')->unique()->count();
            $conditionTypes = $trainees->pluck('trainee_condition')->unique()->count();
            $newTraineesCount = $trainees->where('created_at', '>=', Carbon::now()->subDays(30))->count();
            
            // Log the data retrieval
            Log::info('Trainee management accessed', [
                'user_id' => session('id'),
                'trainees_count' => $totalTrainees
            ]);
            
            return view('trainee-management', [
                'trainees' => $trainees,
                'totalTrainees' => $totalTrainees,
                'centerCount' => $centerCount,
                'conditionTypes' => $conditionTypes,
                'newTraineesCount' => $newTraineesCount
            ]);
        } catch (\Exception $e) {
            Log::error('Error accessing trainee management', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'An error occurred while loading the trainee management page. Please try again later.');
        }
    }
    
    /**
     * Display the specified trainee.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        try {
            $trainee = Trainees::findOrFail($id);
            
            return view('traineeprofile', [
                'trainee' => $trainee
            ]);
        } catch (\Exception $e) {
            Log::error('Error viewing trainee profile', [
                'trainee_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('traineesmanagement')
                ->with('error', 'Trainee not found or an error occurred.');
        }
    }
    
    /**
     * Remove the specified trainee from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            $trainee = Trainees::findOrFail($id);
            
            // Delete avatar file if exists
            if ($trainee->trainee_avatar && !str_contains($trainee->trainee_avatar, 'default') && Storage::exists($trainee->trainee_avatar)) {
                Storage::delete($trainee->trainee_avatar);
            }
            
            // Delete the trainee record
            $trainee->delete();
            
            Log::info('Trainee deleted', [
                'user_id' => session('id'),
                'trainee_id' => $id
            ]);
            
            return redirect()->route('traineesmanagement')
                ->with('success', 'Trainee deleted successfully');
        } catch (\Exception $e) {
            Log::error('Error deleting trainee', [
                'trainee_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('traineesmanagement')
                ->with('error', 'An error occurred while deleting the trainee.');
        }
    }
    
    /**
     * Export trainees to Excel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(Request $request)
    {
        try {
            $format = $request->input('format', 'xlsx');
            $trainees = Trainees::all();
            
            // Log the export action
            Log::info('Trainee data exported', [
                'user_id' => session('id'),
                'format' => $format,
                'count' => $trainees->count()
            ]);
            
            // Format the data for export
            $exportData = [];
            
            foreach ($trainees as $trainee) {
                $exportData[] = [
                    'ID' => $trainee->id,
                    'First Name' => $trainee->trainee_first_name,
                    'Last Name' => $trainee->trainee_last_name,
                    'Email' => $trainee->trainee_email,
                    'Phone Number' => $trainee->trainee_phone_number,
                    'Date of Birth' => $trainee->trainee_date_of_birth,
                    'Age' => $trainee->trainee_date_of_birth ? Carbon::parse($trainee->trainee_date_of_birth)->age : 'N/A',
                    'Centre' => $trainee->centre_name,
                    'Condition' => $trainee->trainee_condition,
                    'Created At' => $trainee->created_at ? $trainee->created_at->format('Y-m-d H:i:s') : 'N/A',
                    'Updated At' => $trainee->updated_at ? $trainee->updated_at->format('Y-m-d H:i:s') : 'N/A'
                ];
            }
            
            // Create the file name
            $fileName = 'trainees_' . date('Y-m-d') . '.' . $format;
            
            // Return response based on format
            if ($format == 'csv') {
                return $this->downloadAsCsv($exportData, $fileName);
            } else {
                return $this->downloadAsExcel($exportData, $fileName);
            }
        } catch (\Exception $e) {
            Log::error('Error exporting trainee data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('traineesmanagement')
                ->with('error', 'An error occurred while exporting trainee data.');
        }
    }
    
    /**
     * Helper method to download data as CSV.
     *
     * @param  array  $data
     * @param  string  $fileName
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    private function downloadAsCsv($data, $fileName)
    {
        // This is a simplified version - in a real app you might use Laravel Excel or similar
        $output = fopen('php://temp', 'r+');
        
        // Add headers
        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
            
            // Add data rows
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }
        
        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);
        
        // Create response
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];
        
        return response($csvContent, 200, $headers);
    }
    
    /**
     * Helper method to download data as Excel.
     *
     * @param  array  $data
     * @param  string  $fileName
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    private function downloadAsExcel($data, $fileName)
    {
        // This is a placeholder - in a real app you would use Laravel Excel or similar
        // For demonstration purposes, we'll return a CSV file instead
        return $this->downloadAsCsv($data, str_replace('.xlsx', '.csv', $fileName));
    }
}