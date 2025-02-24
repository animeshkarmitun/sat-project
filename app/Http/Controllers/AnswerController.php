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
 * Handles answer submissions, retrievals, and evaluations.
 *
 * API Routes:
 * - POST /answers/submit -> submitAnswer() - Stores a user's answer for a given question.
 * - GET /answers/attempt/{attemptId} -> getAnswersByAttempt() - Retrieves all answers for a given exam attempt.
 * - GET /answers/{answerId}/evaluate -> evaluateAnswer() - Evaluates a specific answer and returns feedback.
 * - GET /answers/user/{userId} -> getUserAnswers() - Retrieves all answers submitted by a specific user.
 * - GET /answers/stats/{attemptId} -> getAttemptStats() - Retrieves statistical data for a given exam attempt.
 * - GET /answers/score/{attemptId} -> getAttemptScorePercentage() - Retrieves percentage score for a test attempt.
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
            'student_answer' => 'required|string|max:5000',
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
     * Evaluate a specific answer and return feedback.
     *
     * @param string $answerId
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @route GET /answers/{answerId}/evaluate
     */
    public function evaluateAnswer(Request $request, string $answerId)
    {
        $validator = Validator::make($request->all(), [
            'student_answer' => 'required|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $evaluation = $this->answerService->evaluateAnswer($answerId, $request->student_answer);
            return response()->json(['evaluation' => $evaluation], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Answer not found'], 404);
        }
    }
}
