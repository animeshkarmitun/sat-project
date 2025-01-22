<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Section;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    /**
     * Display a listing of questions for a specific section, with filtering, sorting, and pagination.
     *
     * Example API Requests:
     * - Fetch All Questions for a Section:
     *   GET /api/sections/{sectionId}/questions
     * - Fetch Questions with Custom Sorting:
     *   GET /api/sections/{sectionId}/questions?sort_by=difficulty_level&sort_order=desc
     * - Fetch Deleted Questions (Soft Deleted):
     *   GET /api/sections/{sectionId}/questions?only_trashed=true
     * - Fetch Questions with Pagination:
     *   GET /api/sections/{sectionId}/questions?per_page=5
     *
     * @param  int  $sectionId
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($sectionId, Request $request)
    {
        $section = Section::findOrFail($sectionId);

        // Initialize the query
        $query = $section->questions();

        // Include soft deleted questions if requested
        if ($request->query('with_trashed') === 'true') {
            $query->withTrashed();
        } elseif ($request->query('only_trashed') === 'true') {
            $query->onlyTrashed();
        }

        // Filtering by question type if provided
        if ($questionType = $request->query('question_type')) {
            $query->where('question_type', $questionType);
        }

        // Sorting
        $sortBy = $request->query('sort_by', 'order'); // Default sorting by 'order'
        $sortOrder = $request->query('sort_order', 'asc'); // Default to ascending order
        $allowedSortFields = ['order', 'difficulty_level', 'question_text', 'created_at', 'updated_at'];

        // Validate sort field
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'order';
        }

        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->query('per_page', 10); // Default to 10 items per page
        $questions = $query->paginate($perPage);

        return response()->json([
            'message' => 'Questions retrieved successfully.',
            'section' => $section,
            'questions' => $questions->items(),
            'pagination' => [
                'current_page' => $questions->currentPage(),
                'per_page' => $questions->perPage(),
                'total' => $questions->total(),
                'last_page' => $questions->lastPage(),
            ],
        ]);
    }

    /**
     * Store a newly created question for a specific section.
     *
     * Example API Requests:
     * - Create a Question for a Section:
     *   POST /api/sections/{sectionId}/questions
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $sectionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, $sectionId)
    {
        $section = Section::findOrFail($sectionId);

        // Dynamic validation based on question type
        $validated = $request->validate([
            'question_text' => 'required|string|max:1000',
            'question_type' => 'required|string|in:multiple-choice,true/false,short-answer', // Add other types as needed
            'difficulty_level' => 'nullable|integer|min:1|max:5',
            'is_active' => 'nullable|boolean',
            'order' => 'nullable|integer',
        ]);

        $question = $section->questions()->create($validated);

        return response()->json([
            'message' => 'Question created successfully.',
            'question' => $question,
        ], 201);
    }

    /**
     * Display the specified question along with its options.
     *
     * Example API Requests:
     * - Fetch a Specific Question:
     *   GET /api/questions/{questionId}
     *
     * @param  int  $questionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($questionId)
    {
        $question = Question::with('options')->withTrashed()->findOrFail($questionId);

        return response()->json([
            'message' => 'Question retrieved successfully.',
            'question' => $question,
        ]);
    }

    /**
     * Update the specified question in storage.
     *
     * Example API Requests:
     * - Update a Question:
     *   PUT /api/questions/{questionId}
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $questionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $questionId)
    {
        $question = Question::withTrashed()->findOrFail($questionId);

        // Dynamic validation based on question type
        $validated = $request->validate([
            'question_text' => 'required|string|max:1000',
            'question_type' => 'required|string|in:multiple-choice,true/false,short-answer', // Add other types as needed
            'difficulty_level' => 'nullable|integer|min:1|max:5',
            'is_active' => 'nullable|boolean',
            'order' => 'nullable|integer',
        ]);

        $question->update($validated);

        return response()->json([
            'message' => 'Question updated successfully.',
            'question' => $question,
        ]);
    }

    /**
     * Remove the specified question from storage (soft delete).
     *
     * Example API Requests:
     * - Delete a Question:
     *   DELETE /api/questions/{questionId}
     *
     * @param  int  $questionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($questionId)
    {
        $question = Question::findOrFail($questionId);

        $question->delete();

        return response()->json([
            'message' => 'Question deleted successfully.',
        ]);
    }

    /**
     * Restore a soft-deleted question.
     *
     * Example API Requests:
     * - Restore a Question:
     *   POST /api/questions/{questionId}/restore
     *
     * @param  int  $questionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore($questionId)
    {
        $question = Question::onlyTrashed()->findOrFail($questionId);

        $question->restore();

        return response()->json([
            'message' => 'Question restored successfully.',
            'question' => $question,
        ]);
    }
}
