<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AnswerService;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class AnswerController
 *
 * Manages user answers, including submission, retrieval, and evaluation.
 *
 * API Routes:
 * - POST /answers/submit -> submitAnswer() - Stores a user's answer for a given question.
 * - GET /answers/attempt/{attemptId} -> getAnswersByAttempt() - Retrieves all answers for a given exam attempt.
 * - GET /answers/{answerId}/evaluate -> evaluateAnswer() - Evaluates a specific answer and returns feedback.
 * - DELETE /answers/{answerId} -> deleteAnswer() - Deletes a specific answer.
 * - PATCH /answers/{answerId} -> updateAnswer() - Updates an existing answer.
 */
class AnswerController extends Controller
{
    protected $answerService;

    public function __construct(AnswerService $answerService)
    {
        $this->answerService = $answerService;
    }

    /**
     * Submit an answer for a given question.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @route POST /answers/submit
     */
    public function submitAnswer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'attempt_id' => 'required|uuid|exists:exam_attempts,id',
            'question_id' => 'required|uuid|exists:questions,id',
            'user_id' => 'required|uuid|exists:users,id',
            'answer' => 'required|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $answer = $this->answerService->submitAnswer($request->all());
            Log::info('Answer submitted successfully', ['answer_id' => $answer->id, 'attempt_id' => $request->attempt_id, 'question_id' => $request->question_id]);
            return response()->json(['message' => 'Answer submitted successfully', 'answer' => $answer], 201);
        } catch (CustomException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Retrieve answers for a given exam attempt.
     *
     * @param string $attemptId
     * @return \Illuminate\Http\JsonResponse
     *
     * @route GET /answers/attempt/{attemptId}
     */
    public function getAnswersByAttempt(string $attemptId)
    {
        try {
            $answers = $this->answerService->getAnswersByAttempt($attemptId);
            return response()->json(['answers' => $answers], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Exam attempt not found'], 404);
        }
    }

    /**
     * Evaluate a specific answer and return feedback.
     *
     * @param string $answerId
     * @return \Illuminate\Http\JsonResponse
     *
     * @route GET /answers/{answerId}/evaluate
     */
    public function evaluateAnswer(string $answerId)
    {
        try {
            $evaluation = $this->answerService->evaluateAnswer($answerId);
            return response()->json(['evaluation' => $evaluation], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Answer not found'], 404);
        }
    }

    /**
     * Update an existing answer.
     *
     * @param Request $request
     * @param string $answerId
     * @return \Illuminate\Http\JsonResponse
     *
     * @route PATCH /answers/{answerId}
     */
    public function updateAnswer(Request $request, string $answerId)
    {
        $validator = Validator::make($request->all(), [
            'answer' => 'required|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $updatedAnswer = $this->answerService->updateAnswer($answerId, $request->answer);
            return response()->json(['message' => 'Answer updated successfully', 'answer' => $updatedAnswer], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Answer not found'], 404);
        }
    }

    /**
     * Delete a specific answer.
     *
     * @param string $answerId
     * @return \Illuminate\Http\JsonResponse
     *
     * @route DELETE /answers/{answerId}
     */
    public function deleteAnswer(string $answerId)
    {
        try {
            $this->answerService->deleteAnswer($answerId);
            return response()->json(['message' => 'Answer deleted successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Answer not found'], 404);
        }
    }
}
