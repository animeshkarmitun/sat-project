<?php

/**
 * Class QuestionService
 *
 * Manages question creation, retrieval, updates, and deletion for exams.
 *
 * Function Descriptions:
 * - createQuestion(array $data) - Creates a new question with validation and database transaction handling.
 * - validateQuestionData(array $data) - Validates question data to ensure required fields are provided.
 * - getQuestionsByExam(int $examId) - Retrieves all questions associated with a specific exam.
 * - getActiveQuestionsByExam(int $examId) - Retrieves only active questions for a given exam.
 * - getQuestionById(int $questionId) - Retrieves a specific question by its ID.
 * - updateQuestion(int $questionId, array $data) - Updates a specific question's details.
 * - deleteQuestion(int $questionId) - Deletes a specific question and removes it from cache.
 */




namespace App\Services;

use App\Models\Question;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class QuestionService
{
    /**
     * Create a new question.
     *
     * @param array $data
     * @return Question
     * @throws CustomException
     */
    public function createQuestion(array $data): Question
    {
        $this->validateQuestionData($data);

        return DB::transaction(function () use ($data) {
            $question = Question::create([
                'exam_id' => $data['exam_id'],
                'section_id' => $data['section_id'] ?? null,
                'question_text' => $data['question_text'],
                'question_type' => $data['question_type'],
                'options' => $data['options'] ?? null,
                'correct_answer' => $data['correct_answer'],
                'difficulty' => $data['difficulty'] ?? 'medium',
                'tags' => $data['tags'] ?? null,
                'explanation' => $data['explanation'] ?? null,
                'image_url' => $data['image_url'] ?? null,
                'video_url' => $data['video_url'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'version' => $data['version'] ?? 1,
                'hint' => $data['hint'] ?? null,
                'time_limit' => $data['time_limit'] ?? null,
            ]);

            Log::info('Question created', ['question_id' => $question->id]);
            return $question;
        });
    }

    /**
     * Validate question data.
     *
     * @param array $data
     * @throws CustomException
     */
    private function validateQuestionData(array $data): void
    {
        if (empty($data['question_text'])) {
            throw new CustomException('question.text_required', [], 400);
        }

        if (empty($data['correct_answer'])) {
            throw new CustomException('question.correct_answer_required', [], 400);
        }
    }

    /**
     * Retrieve questions for a specific exam.
     *
     * @param int $examId
     * @return array
     */
    public function getQuestionsByExam(int $examId): array
    {
        return Cache::remember("exam_questions_{$examId}", 3600, function () use ($examId) {
            return Question::where('exam_id', $examId)
                ->orderBy('created_at', 'asc')
                ->get()
                ->toArray();
        });
    }

    /**
     * Retrieve active questions for an exam.
     *
     * @param int $examId
     * @return array
     */
    public function getActiveQuestionsByExam(int $examId): array
    {
        return Question::where('exam_id', $examId)
            ->where('is_active', true)
            ->orderBy('created_at', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * Retrieve a specific question by ID.
     *
     * @param int $questionId
     * @return Question|null
     */
    public function getQuestionById(int $questionId): ?Question
    {
        return Cache::remember("question_{$questionId}", 3600, function () use ($questionId) {
            return Question::find($questionId);
        });
    }

    /**
     * Update a question.
     *
     * @param int $questionId
     * @param array $data
     * @throws CustomException
     */
    public function updateQuestion(int $questionId, array $data): void
    {
        $question = Question::findOrFail($questionId);
        $this->validateQuestionData($data);
        
        $question->update($data);
        Cache::forget("question_{$questionId}");
        Log::info('Question updated', ['question_id' => $questionId]);
    }

    /**
     * Delete a question.
     *
     * @param int $questionId
     * @throws CustomException
     */
    public function deleteQuestion(int $questionId): void
    {
        $question = Question::findOrFail($questionId);
        $question->delete();
        Cache::forget("question_{$questionId}");
        Log::info('Question deleted', ['question_id' => $questionId]);
    }
}
