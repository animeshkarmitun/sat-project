<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ExamService;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Log;
use App\Models\Exam;

class ExamController extends Controller
{
    protected $examService;

    public function __construct(ExamService $examService)
    {
        $this->examService = $examService;
    }

    /**
     * Create a new exam.
     */
    public function createExam(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'exam_name' => 'required|string|max:255',
            'exam_type' => 'required|in:SAT1,SAT2,Personalized',
            'duration' => 'required|integer|min:30',
            'max_attempts' => 'nullable|integer|min:1',
            'created_by' => 'required|uuid|exists:users,user_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $exam = $this->examService->createExam($request->all());
            Log::info('Exam created', ['exam_id' => $exam->exam_id, 'created_by' => $request->created_by]);
            return response()->json(['message' => 'Exam created successfully', 'exam' => $exam], 201);
        } catch (CustomException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Get all exams with optional filters.
     */
    public function getAllExams(Request $request)
    {
        $filters = $request->only(['exam_type', 'created_by']);
        $exams = $this->examService->getAllExams($filters);
        return response()->json(['exams' => $exams], 200);
    }

    /**
     * Get details of a specific exam.
     */
    public function getExamDetails($examId)
    {
        $exam = $this->examService->getExamById($examId);
        return response()->json(['exam' => $exam], 200);
    }

    /**
     * Update an exam.
     */
    public function updateExam(Request $request, $examId)
    {
        $validator = Validator::make($request->all(), [
            'exam_name' => 'sometimes|string|max:255',
            'exam_type' => 'sometimes|in:SAT1,SAT2,Personalized',
            'duration' => 'sometimes|integer|min:30',
            'max_attempts' => 'sometimes|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $exam = $this->examService->updateExam($examId, $request->all());
            Log::info('Exam updated', ['exam_id' => $exam->exam_id]);
            return response()->json(['message' => 'Exam updated successfully', 'exam' => $exam], 200);
        } catch (CustomException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Soft delete an exam.
     */
    public function deleteExam($examId)
    {
        try {
            $this->examService->deleteExam($examId);
            Log::info('Exam deleted', ['exam_id' => $examId]);
            return response()->json(['message' => 'Exam deleted successfully'], 200);
        } catch (CustomException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Restore a soft-deleted exam.
     */
    public function restoreExam($examId)
    {
        try {
            $exam = $this->examService->restoreExam($examId);
            Log::info('Exam restored', ['exam_id' => $exam->exam_id]);
            return response()->json(['message' => 'Exam restored successfully', 'exam' => $exam], 200);
        } catch (CustomException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Duplicate an existing exam.
     */
    public function duplicateExam($examId)
    {
        try {
            $exam = $this->examService->duplicateExam($examId);
            Log::info('Exam duplicated', ['original_exam_id' => $examId, 'new_exam_id' => $exam->exam_id]);
            return response()->json(['message' => 'Exam duplicated successfully', 'exam' => $exam], 201);
        } catch (CustomException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Get paginated list of exams.
     */
    public function getPaginatedExams(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $exams = $this->examService->getPaginatedExams($perPage);
        return response()->json(['exams' => $exams], 200);
    }
}
