<?php

/**
 * ExamAttemptService
 * 
 * This service handles the lifecycle of exam attempts, including creation, status updates, scoring, and retrieval. 
 * 
 * Functions:
 * - startAttempt(array $data): ExamAttempt
 *   Initiates a new exam attempt, ensuring necessary validation and transaction safety.
 * 
 * - submitAttempt(string $attemptId): void
 *   Marks an ongoing attempt as completed, logs submission, and calculates the final score.
 * 
 * - pauseAttempt(string $attemptId): void
 *   Pauses an active exam attempt while preserving remaining time.
 * 
 * - resumeAttempt(string $attemptId): void
 *   Resumes a paused exam attempt, resetting the start time.
 * 
 * - extendAttemptTime(string $attemptId, int $extraMinutes): void
 *   Grants additional time to an ongoing attempt by modifying remaining time.
 * 
 * - getActiveAttempts(string $userId)
 *   Retrieves all active (in-progress or paused) attempts for a user.
 * 
 * - getCompletedAttempts(string $userId)
 *   Fetches completed attempts sorted by end time in descending order.
 * 
 * - calculateScore(ExamAttempt $examAttempt): float
 *   Computes the final score of an attempt based on correct and incorrect answers.
 */

namespace App\Services;

use App\Models\ExamAttempt;
use App\Models\Exam;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Exceptions\CustomException;

class ExamAttemptService
{
    /**
     * Start a new exam attempt for a user.
     */
    public function startAttempt(array $data): ExamAttempt
    {
        $this->validateAttemptData($data);

        return DB::transaction(function () use ($data) {
            $examAttempt = ExamAttempt::create([
                'user_id' => $data['user_id'],
                'exam_id' => $data['exam_id'],
                'start_time' => Carbon::now(),
                'status' => 'in_progress',
                'remaining_time' => $data['remaining_time'] ?? 3600, // Default 1 hour
            ]);

            Log::info('Exam attempt started', ['attempt_id' => $examAttempt->id, 'user_id' => $data['user_id']]);
            return $examAttempt;
        });
    }

    /**
     * Validate exam attempt data.
     */
    private function validateAttemptData(array $data): void
    {
        if (empty($data['user_id']) || empty($data['exam_id'])) {
            throw new CustomException('exam_attempt.missing_required_fields', [], 400);
        }
    }

    /**
     * Submit an exam attempt.
     */
    public function submitAttempt(string $attemptId): void
    {
        $examAttempt = ExamAttempt::findOrFail($attemptId);

        if ($examAttempt->status !== 'in_progress') {
            throw new CustomException('exam_attempt.already_submitted_or_invalid', [], 400);
        }

        $score = $this->calculateScore($examAttempt);
        $examAttempt->update([
            'end_time' => Carbon::now(),
            'status' => 'completed',
            'score' => $score,
        ]);

        Log::info('Exam attempt submitted', ['attempt_id' => $attemptId]);
    }

    /**
     * Pause an exam attempt.
     */
    public function pauseAttempt(string $attemptId): void
    {
        $examAttempt = ExamAttempt::findOrFail($attemptId);

        if ($examAttempt->status !== 'in_progress') {
            throw new CustomException('Cannot pause attempt in current state', [], 400);
        }

        $examAttempt->update([
            'status' => 'paused',
            'remaining_time' => max(0, $examAttempt->remaining_time - Carbon::now()->diffInSeconds($examAttempt->start_time)),
            'start_time' => null,
        ]);

        Log::info('Exam attempt paused', ['attempt_id' => $attemptId]);
    }

    /**
     * Resume a paused exam attempt.
     */
    public function resumeAttempt(string $attemptId): void
    {
        $examAttempt = ExamAttempt::findOrFail($attemptId);

        if ($examAttempt->status !== 'paused') {
            throw new CustomException('Cannot resume attempt in current state', [], 400);
        }

        $examAttempt->update([
            'status' => 'in_progress',
            'start_time' => Carbon::now(),
        ]);

        Log::info('Exam attempt resumed', ['attempt_id' => $attemptId]);
    }

    /**
     * Extend the time limit for an ongoing exam attempt.
     */
    public function extendAttemptTime(string $attemptId, int $extraMinutes): void
    {
        $examAttempt = ExamAttempt::findOrFail($attemptId);
        $examAttempt->update([
            'remaining_time' => $examAttempt->remaining_time + ($extraMinutes * 60),
        ]);
        Log::info('Extra time added', ['attempt_id' => $attemptId, 'extra_time' => $extraMinutes]);
    }

    /**
     * Calculate the score of an exam attempt.
     */
    private function calculateScore(ExamAttempt $examAttempt): float
    {
        $correctAnswers = $examAttempt->correct_answers ?? 0;
        $totalQuestions = $correctAnswers + ($examAttempt->wrong_answers ?? 0);
        return $totalQuestions > 0 ? ($correctAnswers / $totalQuestions) * 100 : 0;
    }
}
