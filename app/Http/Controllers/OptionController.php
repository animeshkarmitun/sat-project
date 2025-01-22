<?php

namespace App\Http\Controllers;

use App\Models\Option;
use App\Models\Question;
use Illuminate\Http\Request;

class OptionController extends Controller
{
    /**
     * Display a listing of options for a specific question, with filtering, sorting, and pagination.
     *
     * Example API Requests:
     * - Fetch All Options for a Question:
     *   GET /api/questions/{questionId}/options
     * - Fetch Options with Custom Sorting:
     *   GET /api/questions/{questionId}/options?sort_by=order&sort_order=asc
     * - Fetch Deleted Options (Soft Deleted):
     *   GET /api/questions/{questionId}/options?only_trashed=true
     * - Fetch Options with Pagination:
     *   GET /api/questions/{questionId}/options?per_page=5
     *
     * @param  int  $questionId
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($questionId, Request $request)
    {
        $question = Question::findOrFail($questionId);

        // Initialize the query
        $query = $question->options();

        // Include soft deleted options if requested
        if ($request->query('with_trashed') === 'true') {
            $query->withTrashed();
        } elseif ($request->query('only_trashed') === 'true') {
            $query->onlyTrashed();
        }

        // Sorting
        $sortBy = $request->query('sort_by', 'order'); // Default sorting by 'order'
        $sortOrder = $request->query('sort_order', 'asc'); // Default to ascending order
        $allowedSortFields = ['order', 'option_text', 'is_correct', 'created_at', 'updated_at'];

        // Validate sort field
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'order';
        }

        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->query('per_page', 10); // Default to 10 items per page
        $options = $query->paginate($perPage);

        return response()->json([
            'message' => 'Options retrieved successfully.',
            'question' => $question,
            'options' => $options->items(),
            'pagination' => [
                'current_page' => $options->currentPage(),
                'per_page' => $options->perPage(),
                'total' => $options->total(),
                'last_page' => $options->lastPage(),
            ],
        ]);
    }

    /**
     * Store a newly created option for a specific question.
     *
     * Example API Requests:
     * - Create an Option for a Question:
     *   POST /api/questions/{questionId}/options
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $questionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, $questionId)
    {
        $question = Question::findOrFail($questionId);

        // Validation
        $validated = $request->validate([
            'option_text' => 'required|string|max:255',
            'is_correct' => 'required|boolean',
            'is_active' => 'nullable|boolean',
            'order' => 'nullable|integer',
        ]);

        $option = $question->options()->create($validated);

        return response()->json([
            'message' => 'Option created successfully.',
            'option' => $option,
        ], 201);
    }

    /**
     * Display the specified option.
     *
     * Example API Requests:
     * - Fetch a Specific Option:
     *   GET /api/options/{optionId}
     *
     * @param  int  $optionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($optionId)
    {
        $option = Option::withTrashed()->findOrFail($optionId);

        return response()->json([
            'message' => 'Option retrieved successfully.',
            'option' => $option,
        ]);
    }

    /**
     * Update the specified option in storage.
     *
     * Example API Requests:
     * - Update an Option:
     *   PUT /api/options/{optionId}
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $optionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $optionId)
    {
        $option = Option::withTrashed()->findOrFail($optionId);

        // Validation
        $validated = $request->validate([
            'option_text' => 'required|string|max:255',
            'is_correct' => 'required|boolean',
            'is_active' => 'nullable|boolean',
            'order' => 'nullable|integer',
        ]);

        $option->update($validated);

        return response()->json([
            'message' => 'Option updated successfully.',
            'option' => $option,
        ]);
    }

    /**
     * Remove the specified option from storage (soft delete).
     *
     * Example API Requests:
     * - Delete an Option:
     *   DELETE /api/options/{optionId}
     *
     * @param  int  $optionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($optionId)
    {
        $option = Option::findOrFail($optionId);

        $option->delete();

        return response()->json([
            'message' => 'Option deleted successfully.',
        ]);
    }

    /**
     * Restore a soft-deleted option.
     *
     * Example API Requests:
     * - Restore an Option:
     *   POST /api/options/{optionId}/restore
     *
     * @param  int  $optionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore($optionId)
    {
        $option = Option::onlyTrashed()->findOrFail($optionId);

        $option->restore();

        return response()->json([
            'message' => 'Option restored successfully.',
            'option' => $option,
        ]);
    }
}
