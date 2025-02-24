<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ExamAttemptService;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class ExamAttemptController
 *
 * Handles all operations related to exam attempts, including creation, status updates, and time extensions.
 *
 * API Routes:
 * - POST /exam-attempts/start -> startAttempt() - Initiates a new exam attempt for a user.
 * - POST /exam-attempts/{attemptId}/submit -> submitAttempt() - Marks an attempt as completed and calculates the score.
 * - POST /exam-attempts/{attemptId}/pause -> pauseAttempt() - Pauses an in-progress attempt.
 * - POST /exam-attempts/{attemptId}/resume -> resumeAttempt() - Resumes a previously paused attempt.
 * - POST /exam-attempts/{attemptId}/extend-time -> extendAttemptTime() - Adds extra time to an active attempt.
 */
class ExamAttemptController extends Controller
{
    protected $examAttemptService;

    public function __construct(ExamAttemptService $examAttemptService)
    {
        $this->examAttemptService = $examAttemptService;
    }

    /**
     * Start a new exam attempt.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @route POST /exam-attempts/start
     */
    public function startAttempt(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'exam_id' => 'required|uuid|exists:exams,id',
            'user_id' => 'required|uuid|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $attempt = $this->examAttemptService->startAttempt($request->all());
            Log::info('Exam attempt started', ['attempt_id' => $attempt->id, 'exam_id' => $request->exam_id]);
            return response()->json(['message' => 'Exam attempt started successfully', 'attempt' => $attempt], 201);
        } catch (CustomException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Submit an ongoing exam attempt.
     *
     * @param Request $request
     * @param string $attemptId
     * @return \Illuminate\Http\JsonResponse
     *
     * @route POST /exam-attempts/{attemptId}/submit
     */
    public function submitAttempt(Request $request, $attemptId)
    {
        try {
            $this->examAttemptService->submitAttempt($attemptId);
            return response()->json(['message' => 'Exam attempt submitted successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Exam attempt not found'], 404);
        } catch (CustomException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Pause an ongoing exam attempt.
     *
     * @param string $attemptId
     * @return \Illuminate\Http\JsonResponse
     *
     * @route POST /exam-attempts/{attemptId}/pause
     */
    public function pauseAttempt($attemptId)
    {
        try {
            $this->examAttemptService->pauseAttempt($attemptId);
            return response()->json(['message' => 'Exam attempt paused successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Exam attempt not found'], 404);
        } catch (CustomException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Resume a paused exam attempt.
     *
     * @param string $attemptId
     * @return \Illuminate\Http\JsonResponse
     *
     * @route POST /exam-attempts/{attemptId}/resume
     */
    public function resumeAttempt($attemptId)
    {
        try {
            $this->examAttemptService->resumeAttempt($attemptId);
            return response()->json(['message' => 'Exam attempt resumed successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Exam attempt not found'], 404);
        } catch (CustomException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Extend the time limit for an ongoing exam attempt.
     *
     * @param Request $request
     * @param string $attemptId
     * @return \Illuminate\Http\JsonResponse
     *
     * @route POST /exam-attempts/{attemptId}/extend-time
     */
    public function extendAttemptTime(Request $request, $attemptId)
    {
        $validator = Validator::make($request->all(), [
            'extra_time' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $this->examAttemptService->extendAttemptTime($attemptId, $request->extra_time);
            return response()->json(['message' => 'Exam attempt time extended successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Exam attempt not found'], 404);
        } catch (CustomException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }
}
