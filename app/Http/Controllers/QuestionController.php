<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\QuestionService;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Log;
use App\Models\Question;

class QuestionController extends Controller
{
    protected $questionService;

    public function __construct(QuestionService $questionService)
    {
        $this->questionService = $questionService;
    }

    /**
     * Create a new question.
     */
    public function createQuestion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'section_id' => 'required|uuid|exists:sections,section_id',
            'subject_id' => 'required|uuid|exists:subjects,subject_id',
            'question_text' => 'required|string',
            'question_type' => 'required|in:MCQ,Grid-In',
            'options' => 'nullable|json',
            'correct_answer' => 'required|string|max:255',
            'difficulty' => 'required|in:Easy,Medium,Hard',
            'tags' => 'nullable|json',
            'explanation' => 'nullable|string',
            'image_urls' => 'nullable|json',
            'video_urls' => 'nullable|json',
            'created_by' => 'required|uuid|exists:users,user_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $question = $this->questionService->createQuestion($request->all());
            Log::info('Question created', ['question_id' => $question->question_id, 'section_id' => $request->section_id]);
            return response()->json(['message' => 'Question created successfully', 'question' => $question], 201);
        } catch (CustomException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Bulk upload multiple questions.
     */
    public function bulkUploadQuestions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'questions' => 'required|array|min:1',
            'questions.*.section_id' => 'required|uuid|exists:sections,section_id',
            'questions.*.subject_id' => 'required|uuid|exists:subjects,subject_id',
            'questions.*.question_text' => 'required|string',
            'questions.*.correct_answer' => 'required|string|max:255',
            'questions.*.difficulty' => 'required|in:Easy,Medium,Hard',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $questions = $this->questionService->bulkUploadQuestions($request->questions);
            Log::info('Bulk questions uploaded', ['count' => count($questions)]);
            return response()->json(['message' => 'Questions uploaded successfully', 'questions' => $questions], 201);
        } catch (CustomException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Get all questions for a section with pagination and filtering.
     */
    public function getSectionQuestions(Request $request, $sectionId)
    {
        $perPage = $request->input('per_page', 10);
        $difficulty = $request->input('difficulty');
        
        $questions = $this->questionService->getQuestionsBySection($sectionId, $perPage, $difficulty);
        return response()->json(['questions' => $questions], 200);
    }

    /**
     * Get random questions for a section.
     */
    public function getRandomQuestions(Request $request, $sectionId)
    {
        $perPage = $request->input('per_page', 10);
        $questions = $this->questionService->getRandomQuestions($sectionId, $perPage);
        return response()->json(['questions' => $questions], 200);
    }

    /**
     * Get details of a specific question.
     */
    public function getQuestionDetails($questionId)
    {
        $question = $this->questionService->getQuestionById($questionId);
        return response()->json(['question' => $question], 200);
    }

    /**
     * Update a question.
     */
    public function updateQuestion(Request $request, $questionId)
    {
        $validator = Validator::make($request->all(), [
            'question_text' => 'sometimes|string',
            'question_type' => 'sometimes|in:MCQ,Grid-In',
            'options' => 'nullable|json',
            'correct_answer' => 'sometimes|string|max:255',
            'difficulty' => 'sometimes|in:Easy,Medium,Hard',
            'tags' => 'nullable|json',
            'explanation' => 'nullable|string',
            'image_urls' => 'nullable|json',
            'video_urls' => 'nullable|json',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $question = $this->questionService->updateQuestion($questionId, $request->all());
            Log::info('Question updated', ['question_id' => $question->question_id]);
            return response()->json(['message' => 'Question updated successfully', 'question' => $question], 200);
        } catch (CustomException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Delete a question.
     */
    public function deleteQuestion($questionId)
    {
        try {
            $this->questionService->deleteQuestion($questionId);
            Log::info('Question deleted', ['question_id' => $questionId]);
            return response()->json(['message' => 'Question deleted successfully'], 200);
        } catch (CustomException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }
}
