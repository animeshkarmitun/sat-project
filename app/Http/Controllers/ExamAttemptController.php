<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ExamAttemptService;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Log;
use App\Models\ExamAttempt;

class ExamAttemptController extends Controller
{
    protected $examAttemptService;

    public function __construct(ExamAttemptService $examAttemptService)
    {
        $this->examAttemptService = $examAttemptService;
    }

    /**
     * Start a new exam attempt.
     */
    public function startAttempt(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'exam_id' => 'required|uuid|exists:exams,exam_id',
            'user_id' => 'required|uuid|exists:users,user_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $attempt = $this->examAttemptService->startAttempt($request->all());
            Log::info('Exam attempt started', ['attempt_id' => $attempt->attempt_id, 'exam_id' => $request->exam_id]);
            return response()->json(['message' => 'Exam attempt started successfully', 'attempt' => $attempt], 201);
        } catch (CustomException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Submit an ongoing exam attempt.
     */
    public function submitAttempt(Request $request, $attemptId)
    {
        $validator = Validator::make($request->all(), [
            'answers' => 'required|array|min:1',
            'answers.*.question_id' => 'required|uuid|exists:questions,question_id',
            'answers.*.student_answer' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $result = $this->examAttemptService->submitAttempt($attemptId, $request->answers);
            Log::info('Exam attempt submitted', ['attempt_id' => $attemptId, 'total_answers' => count($request->answers)]);
            return response()->json(['message' => 'Exam attempt submitted successfully', 'result' => $result], 200);
        } catch (CustomException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Auto-save an ongoing exam attempt.
     */
    public function autoSaveAttempt(Request $request, $attemptId)
    {
        $validator = Validator::make($request->all(), [
            'answers' => 'required|array|min:1',
            'answers.*.question_id' => 'required|uuid|exists:questions,question_id',
            'answers.*.student_answer' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $this->examAttemptService->autoSaveAttempt($attemptId, $request->answers);
            Log::info('Exam attempt auto-saved', ['attempt_id' => $attemptId, 'total_answers' => count($request->answers)]);
            return response()->json(['message' => 'Exam attempt auto-saved successfully'], 200);
        } catch (CustomException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Extend the time limit for an ongoing exam attempt.
     */
    public function extendAttemptTime(Request $request, $attemptId)
    {
        $validator = Validator::make($request->all(), [
            'extra_time' => 'required|integer|min:1', // Extra minutes to add
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $attempt = $this->examAttemptService->extendAttemptTime($attemptId, $request->extra_time);
            Log::info('Exam attempt time extended', ['attempt_id' => $attemptId, 'extra_time' => $request->extra_time]);
            return response()->json(['message' => 'Exam attempt time extended successfully', 'attempt' => $attempt], 200);
        } catch (CustomException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }
}
