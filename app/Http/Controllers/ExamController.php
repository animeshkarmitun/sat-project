<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    /**
     * Display a paginated, sortable, and filterable listing of exams.
     *
     * Example API Requests:
     * - Fetch All Exams (Default Sorting and Pagination):
     *   GET /api/exams
     * 
     * - Fetch Only Active Exams:
     *   GET /api/exams?is_active=true
     * 
     * - Fetch Exams by Category:
     *   GET /api/exams?category=SAT 1
     * 
     * - Fetch Exams with Custom Sorting:
     *   GET /api/exams?sort_by=start_time&sort_order=desc
     * 
     * - Fetch Active Exams in a Category:
     *   GET /api/exams?category=SAT 1&is_active=true
     * 
     * - Fetch Exams with Pagination:
     *   GET /api/exams?per_page=5
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */


    public function index(Request $request)
    {
        // Initialize the query
        $query = Exam::with('sections');

        // Filter by category if provided
        if ($category = $request->query('category')) {
            $query->byCategory($category);
        }

        // Filter by active status if provided
        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->query('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        // Sorting
        $sortBy = $request->query('sort_by', 'title'); // Default to sorting by 'title'
        $sortOrder = $request->query('sort_order', 'asc'); // Default to ascending order
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->query('per_page', 10); // Default to 10 items per page
        $exams = $query->paginate($perPage);

        // Response
        return response()->json([
            'message' => 'Exams retrieved successfully.',
            'exams' => $exams->items(), // The paginated items
            'pagination' => [
                'current_page' => $exams->currentPage(),
                'per_page' => $exams->perPage(),
                'total' => $exams->total(),
                'last_page' => $exams->lastPage(),
            ],
        ]);
    }



    /**
     * Store a newly created exam in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'required|integer|min:1',
            'category' => 'nullable|string|max:255',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'is_active' => 'required|boolean',
        ]);

        $exam = Exam::create($validated);

        return response()->json([
            'message' => 'Exam created successfully.',
            'exam' => $exam,
        ], 201);
    }

    /**
     * Display the specified exam along with its sections.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $exam = Exam::with('sections')->findOrFail($id);

        return response()->json([
            'message' => 'Exam retrieved successfully.',
            'exam' => $exam,
        ]);
    }

    /**
     * Update the specified exam in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $exam = Exam::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'required|integer|min:1',
            'category' => 'nullable|string|max:255',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'is_active' => 'required|boolean',
        ]);

        $exam->update($validated);

        return response()->json([
            'message' => 'Exam updated successfully.',
            'exam' => $exam,
        ]);
    }

    /**
     * Remove the specified exam from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $exam = Exam::findOrFail($id);

        $exam->delete();

        return response()->json([
            'message' => 'Exam deleted successfully.',
        ]);
    }
}
