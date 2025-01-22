<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\Exam;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    /**
     * Display a listing of sections for a specific exam, with filtering, sorting, and pagination.
     *
     * Example API Requests:
     * - Fetch All Sections for an Exam:
     *   GET /api/exams/{examId}/sections
     * - Fetch Sections by Subject:
     *   GET /api/exams/{examId}/sections?subject=Math
     * - Fetch Deleted Sections (Soft Deleted):
     *   GET /api/exams/{examId}/sections?with_trashed=true
     * - Fetch Sections with Custom Sorting:
     *   GET /api/exams/{examId}/sections?sort_by=title&sort_order=desc
     * - Fetch Sections with Pagination:
     *   GET /api/exams/{examId}/sections?per_page=5
     *
     * @param  int  $examId
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($examId, Request $request)
    {
        $exam = Exam::findOrFail($examId);

        // Initialize the query
        $query = Section::where('exam_id', $examId);

        // Include soft deleted sections if requested
        if ($request->query('with_trashed') === 'true') {
            $query->withTrashed();
        } elseif ($request->query('only_trashed') === 'true') {
            $query->onlyTrashed();
        }

        // Filter by subject if provided
        if ($subject = $request->query('subject')) {
            $query->where('subject', $subject);
        }

        // Sorting
        $sortBy = $request->query('sort_by', 'order'); // Default sorting by 'order'
        $sortOrder = $request->query('sort_order', 'asc'); // Default to ascending order
        $allowedSortFields = ['order', 'title', 'subject', 'created_at', 'updated_at'];

        // Validate sort field
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'order';
        }

        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->query('per_page', 10); // Default to 10 items per page
        $sections = $query->paginate($perPage);

        return response()->json([
            'message' => 'Sections retrieved successfully.',
            'exam' => $exam,
            'sections' => $sections->items(),
            'pagination' => [
                'current_page' => $sections->currentPage(),
                'per_page' => $sections->perPage(),
                'total' => $sections->total(),
                'last_page' => $sections->lastPage(),
            ],
        ]);
    }

    /**
     * Store a newly created section for a specific exam.
     *
     * Example API Requests:
     * - Create a Section for an Exam:
     *   POST /api/exams/{examId}/sections
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $examId
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, $examId)
    {
        $exam = Exam::findOrFail($examId);

        // Dynamic validation based on exam category
        $validationRules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'subject' => $exam->category === 'SAT 2' ? 'required|string|max:255' : 'nullable|string|max:255',
            'order' => 'nullable|integer',
        ];

        $validated = $request->validate($validationRules);

        $section = $exam->sections()->create($validated);

        return response()->json([
            'message' => 'Section created successfully.',
            'section' => $section,
        ], 201);
    }

    /**
     * Display the specified section along with its questions.
     *
     * Example API Requests:
     * - Fetch a Specific Section:
     *   GET /api/sections/{sectionId}
     *
     * @param  int  $sectionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($sectionId)
    {
        $section = Section::with('questions')->withTrashed()->findOrFail($sectionId);

        return response()->json([
            'message' => 'Section retrieved successfully.',
            'section' => $section,
        ]);
    }

    /**
     * Update the specified section in storage.
     *
     * Example API Requests:
     * - Update a Section:
     *   PUT /api/sections/{sectionId}
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $sectionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $sectionId)
    {
        $section = Section::withTrashed()->findOrFail($sectionId);

        // Dynamic validation based on exam category
        $exam = $section->exam;
        $validationRules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'subject' => $exam->category === 'SAT 2' ? 'required|string|max:255' : 'nullable|string|max:255',
            'order' => 'nullable|integer',
        ];

        $validated = $request->validate($validationRules);

        $section->update($validated);

        return response()->json([
            'message' => 'Section updated successfully.',
            'section' => $section,
        ]);
    }

    /**
     * Remove the specified section from storage (soft delete).
     *
     * Example API Requests:
     * - Delete a Section:
     *   DELETE /api/sections/{sectionId}
     *
     * @param  int  $sectionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($sectionId)
    {
        $section = Section::findOrFail($sectionId);

        $section->delete();

        return response()->json([
            'message' => 'Section deleted successfully.',
        ]);
    }

    /**
     * Restore a soft-deleted section.
     *
     * Example API Requests:
     * - Restore a Section:
     *   POST /api/sections/{sectionId}/restore
     *
     * @param  int  $sectionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore($sectionId)
    {
        $section = Section::onlyTrashed()->findOrFail($sectionId);

        $section->restore();

        return response()->json([
            'message' => 'Section restored successfully.',
            'section' => $section,
        ]);
    }
}
