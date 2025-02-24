<?php
/**
 * Class AnswerService
 *
 * Handles user answer submissions, retrieval, and evaluation for test attempts.
 *
 * Function Descriptions:
 * - submitAnswer(array $data): Answer - Stores a user's answer for a given question with validation.
 * - validateAnswerData(array $data): void - Ensures the necessary data fields are provided for answer submission.
 * - evaluateAnswer(int $questionId, ?string $studentAnswer): bool - Determines if a submitted answer is correct.
 * - calculateScore(int $questionId, ?string $studentAnswer): float - Calculates the score of a submitted answer.
 * - getUserAnswers(int $userId): array - Retrieves all answers submitted by a specific user.
 * - getAnswersByAttempt(int $attemptId): array - Retrieves all answers submitted for a specific test attempt.
 * - getAttemptStats(int $attemptId): array - Retrieves the count of correct and incorrect answers for a test attempt.
 * - getAttemptScorePercentage(int $attemptId): float - Computes the percentage score of a test attempt.
 */

namespace App\Services;

use App\Models\Answer;
use App\Models\Question;
use App\Models\TestAttempt;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AnswerService
{
    /**
     * Submit an answer for a question.
     */
    public function submitAnswer(array $data): Answer
    {
        $this->validateAnswerData($data);

        return DB::transaction(function () use ($data) {
            $answer = Answer::create([
                'user_id' => $data['user_id'],
                'question_id' => $data['question_id'],
                'attempt_id' => $data['attempt_id'] ?? null,
                'student_answer' => $data['student_answer'] ?? null,
                'is_correct' => $this->evaluateAnswer($data['question_id'], $data['student_answer']),
                'time_spent' => $data['time_spent'] ?? null,
                'image_url' => $data['image_url'] ?? null,
                'video_url' => $data['video_url'] ?? null,
                'score' => $this->calculateScore($data['question_id'], $data['student_answer']),
            ]);

            Log::info('Answer submitted', ['answer_id' => $answer->id, 'user_id' => $data['user_id']]);
            return $answer;
        });
    }

    /**
     * Validate answer submission data.
     */
    private function validateAnswerData(array $data): void
    {
        if (empty($data['user_id']) || empty($data['question_id'])) {
            throw new CustomException('answer.user_question_required', [], 400);
        }
    }

    /**
     * Evaluate the answer correctness.
     */
    public function evaluateAnswer(int $questionId, ?string $studentAnswer): bool
    {
        $question = Question::findOrFail($questionId);
        return strtolower(trim($question->correct_answer)) === strtolower(trim($studentAnswer));
    }

    /**
     * Calculate the score for the answer.
     */
    public function calculateScore(int $questionId, ?string $studentAnswer): float
    {
        $question = Question::findOrFail($questionId);
        return $this->evaluateAnswer($questionId, $studentAnswer) ? $question->score_weight : 0.0;
    }

    /**
     * Retrieve answers for a specific user.
     */
    public function getUserAnswers(int $userId): array
    {
        return Cache::remember("user_answers_{$userId}", 3600, function () use ($userId) {
            return Answer::where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get()
                ->toArray();
        });
    }

    /**
     * Retrieve answers for a specific exam attempt.
     */
    public function getAnswersByAttempt(int $attemptId): array
    {
        return Answer::where('attempt_id', $attemptId)
            ->orderBy('created_at', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * Retrieve correct and incorrect answers count for a test attempt.
     */
    public function getAttemptStats(int $attemptId): array
    {
        return [
            'correct' => Answer::where('attempt_id', $attemptId)->where('is_correct', true)->count(),
            'incorrect' => Answer::where('attempt_id', $attemptId)->where('is_correct', false)->count(),
            'total' => Answer::where('attempt_id', $attemptId)->count(),
            'score' => Answer::where('attempt_id', $attemptId)->sum('score'),
        ];
    }

    /**
     * Get the percentage score of a test attempt.
     */
    public function getAttemptScorePercentage(int $attemptId): float
    {
        $totalScore = Answer::where('attempt_id', $attemptId)->sum('score');
        $totalQuestions = Answer::where('attempt_id', $attemptId)->count();

        return $totalQuestions > 0 ? ($totalScore / ($totalQuestions * 1.0)) * 100 : 0.0;
    }
}
