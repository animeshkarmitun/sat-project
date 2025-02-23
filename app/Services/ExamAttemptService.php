<?php

namespace App\Services;

use App\Models\ExamAttempt;
use App\Models\Exam;
use App\Models\User;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ExamAttemptService
{
    /**
     * Start a new exam attempt for a user.
     *
     * @param array $data
     * @return ExamAttempt
     * @throws CustomException
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
                'remaining_time' => $data['remaining_time'] ?? null,
            ]);

            Log::info('Exam attempt started', ['attempt_id' => $examAttempt->id, 'user_id' => $data['user_id']]);
            return $examAttempt;
        });
    }

    /**
     * Validate exam attempt data.
     *
     * @param array $data
     * @throws CustomException
     */
    private function validateAttemptData(array $data): void
    {
        if (empty($data['user_id']) || empty($data['exam_id'])) {
            throw new CustomException('exam_attempt.missing_required_fields', [], 400);
        }
    }

    /**
     * Submit an exam attempt.
     *
     * @param int $attemptId
     * @return void
     * @throws CustomException
     */
    public function submitAttempt(int $attemptId): void
    {
        $examAttempt = ExamAttempt::findOrFail($attemptId);

        if ($examAttempt->status !== 'in_progress') {
            throw new CustomException('exam_attempt.already_submitted_or_invalid', [], 400);
        }

        $examAttempt->update([
            'end_time' => Carbon::now(),
            'status' => 'completed',
        ]);

        Log::info('Exam attempt submitted', ['attempt_id' => $attemptId]);
    }

    /**
     * Pause an exam attempt.
     *
     * @param int $attemptId
     * @throws CustomException
     */
    public function pauseAttempt(int $attemptId): void
    {
        $examAttempt = ExamAttempt::findOrFail($attemptId);

        if ($examAttempt->status !== 'in_progress') {
            throw new CustomException('exam_attempt.cannot_pause_invalid_status', [], 400);
        }

        $examAttempt->update(['status' => 'paused']);
        Log::info('Exam attempt paused', ['attempt_id' => $attemptId]);
    }

    /**
     * Resume a paused exam attempt.
     *
     * @param int $attemptId
     * @throws CustomException
     */
    public function resumeAttempt(int $attemptId): void
    {
        $examAttempt = ExamAttempt::findOrFail($attemptId);

        if ($examAttempt->status !== 'paused') {
            throw new CustomException('exam_attempt.cannot_resume_invalid_status', [], 400);
        }

        $examAttempt->update(['status' => 'in_progress']);
        Log::info('Exam attempt resumed', ['attempt_id' => $attemptId]);
    }

    /**
     * Retrieve active exam attempts for a user.
     *
     * @param int $userId
     * @return array
     */
    public function getActiveAttempts(int $userId): array
    {
        return ExamAttempt::where('user_id', $userId)
            ->where('status', 'in_progress')
            ->get()
            ->toArray();
    }

    /**
     * Retrieve completed exam attempts for a user.
     *
     * @param int $userId
     * @return array
     */
    public function getCompletedAttempts(int $userId): array
    {
        return ExamAttempt::where('user_id', $userId)
            ->where('status', 'completed')
            ->orderBy('end_time', 'desc')
            ->get()
            ->toArray();
    }
}
