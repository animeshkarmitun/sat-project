<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AnswerService;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Log;
use App\Models\Answer;

class AnswerController extends Controller
{
    protected $answerService;

    public function __construct(AnswerService $answerService)
    {
        $this->answerService = $answerService;
    }

    /**
     * Submit an answer for a question.
     */
    public function submitAnswer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'attempt_id' => 'required|uuid|exists:exam_attempts,attempt_id',
            'question_id' => 'required|uuid|exists:questions,question_id',
            'student_answer' => 'nullable|string|max:255',
            'time_spent' => 'nullable|integer|min:1',
            'image_url' => 'nullable|url|max:2083',
            'video_url' => 'nullable|url|max:2083',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $answer = $this->answerService->submitAnswer($request->all());
            Log::info('Answer submitted', ['answer_id' => $answer->answer_id, 'attempt_id' => $request->attempt_id]);
            return response()->json(['message' => 'Answer submitted successfully', 'answer' => $answer], 201);
        } catch (CustomException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Get all answers for an attempt with pagination.
     */
    public function getAttemptAnswers(Request $request, $attemptId)
    {
        $perPage = $request->input('per_page', 10);
        $answers = $this->answerService->getAnswersByAttempt($attemptId, $perPage);
        return response()->json(['answers' => $answers], 200);
    }

    /**
     * Get details of a specific answer.
     */
    public function getAnswerDetails($answerId)
    {
        $answer = $this->answerService->getAnswerById($answerId);
        return response()->json(['answer' => $answer], 200);
    }

    /**
     * Update an answer (if applicable).
     */
    public function updateAnswer(Request $request, $answerId)
    {
        $validator = Validator::make($request->all(), [
            'student_answer' => 'nullable|string|max:255',
            'time_spent' => 'nullable|integer|min:1',
            'image_url' => 'nullable|url|max:2083',
            'video_url' => 'nullable|url|max:2083',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $answer = $this->answerService->updateAnswer($answerId, $request->all());
            Log::info('Answer updated', ['answer_id' => $answer->answer_id]);
            return response()->json(['message' => 'Answer updated successfully', 'answer' => $answer], 200);
        } catch (CustomException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Delete an answer (soft delete).
     */
    public function deleteAnswer($answerId)
    {
        try {
            $this->answerService->deleteAnswer($answerId);
            Log::info('Answer deleted', ['answer_id' => $answerId]);
            return response()->json(['message' => 'Answer deleted successfully'], 200);
        } catch (CustomException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Retrieve flagged answers for review.
     */
    public function getFlaggedAnswers(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $flaggedAnswers = $this->answerService->getFlaggedAnswers($perPage);
        return response()->json(['flagged_answers' => $flaggedAnswers], 200);
    }

    /**
     * Flag an answer for review.
     */
    public function flagAnswer($answerId)
    {
        try {
            $this->answerService->flagAnswer($answerId);
            Log::info('Answer flagged', ['answer_id' => $answerId]);
            return response()->json(['message' => 'Answer flagged for review'], 200);
        } catch (CustomException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Unflag an answer after review.
     */
    public function unflagAnswer($answerId)
    {
        try {
            $this->answerService->unflagAnswer($answerId);
            Log::info('Answer unflagged', ['answer_id' => $answerId]);
            return response()->json(['message' => 'Answer unflagged'], 200);
        } catch (CustomException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Bulk delete answers.
     */
    public function bulkDeleteAnswers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'answer_ids' => 'required|array|min:1',
            'answer_ids.*' => 'uuid|exists:answers,answer_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $this->answerService->bulkDeleteAnswers($request->answer_ids);
            Log::info('Bulk answers deleted', ['answer_ids' => $request->answer_ids]);
            return response()->json(['message' => 'Answers deleted successfully'], 200);
        } catch (CustomException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }
}
