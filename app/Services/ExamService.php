<?php
/**
 * Class ExamService
 *
 * Handles exam management, including creation, publication, retrieval, and filtering.
 *
 * Function Descriptions:
 * - createExam(array $data) - Creates a new exam and stores it in the database.
 * - publishExam(int $examId) - Publishes an exam, making it available for users.
 * - getPublishedExams() - Retrieves all published exams.
 * - validateExamData(array $data) - Validates exam data before creation.
 * - getUpcomingExams() - Retrieves upcoming exams that have not yet started.
 * - getPastExams() - Retrieves past exams that have already ended.
 * - getExamsWithMultipleAttempts() - Retrieves exams that allow multiple attempts.
 * - getActiveExams() - Retrieves active exams that are published and not expired.
 * - getExpiredExams() - Retrieves exams that have passed their end date.
 */



namespace App\Services;

use App\Models\Exam;
use App\Models\User;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ExamService
{
    /**
     * Create a new exam.
     *
     * @param array $data
     * @return Exam
     * @throws CustomException
     */
    public function createExam(array $data): Exam
    {
        $this->validateExamData($data);

        return DB::transaction(function () use ($data) {
            $exam = Exam::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'duration' => $data['duration'] ?? null,
                'total_marks' => $data['total_marks'] ?? null,
                'status' => $data['status'] ?? 'draft',
                'created_by' => $data['created_by'],
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'max_attempts' => $data['max_attempts'] ?? 1,
                'passing_marks' => $data['passing_marks'] ?? null,
                'is_published' => $data['is_published'] ?? false,
                'exam_code' => strtoupper(uniqid('EXM_')),
                'exam_type' => $data['exam_type'] ?? 'general',
                'allowed_attempts' => $data['allowed_attempts'] ?? 1,
            ]);

            Log::info('Exam created', ['exam_id' => $exam->id, 'title' => $exam->title]);
            return $exam;
        });
    }

    /**
     * Publish an exam.
     *
     * @param int $examId
     * @throws CustomException
     */
    public function publishExam(int $examId): void
    {
        $exam = Exam::findOrFail($examId);
        $exam->update(['is_published' => true]);
        Cache::forget("exam_{$examId}");
        Log::info('Exam published', ['exam_id' => $examId]);
    }

    /**
     * Get published exams.
     *
     * @return array
     */
    public function getPublishedExams(): array
    {
        return Exam::where('is_published', true)
            ->orderBy('start_date', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * Validate exam data.
     *
     * @param array $data
     * @throws CustomException
     */
    private function validateExamData(array $data): void
    {
        if (empty($data['title'])) {
            throw new CustomException('exam.title_required', [], 400);
        }

        if (isset($data['duration']) && $data['duration'] <= 0) {
            throw new CustomException('exam.invalid_duration', [], 400);
        }

        if (isset($data['passing_marks']) && isset($data['total_marks']) && $data['passing_marks'] > $data['total_marks']) {
            throw new CustomException('exam.invalid_passing_marks', [], 400);
        }

        if (isset($data['start_date']) && isset($data['end_date']) && Carbon::parse($data['start_date'])->greaterThan(Carbon::parse($data['end_date']))) {
            throw new CustomException('exam.invalid_date_range', [], 400);
        }
    }

    /**
     * Get upcoming exams.
     *
     * @return array
     */
    public function getUpcomingExams(): array
    {
        return Exam::where('start_date', '>', now())
            ->orderBy('start_date', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * Get past exams.
     *
     * @return array
     */
    public function getPastExams(): array
    {
        return Exam::where('end_date', '<', now())
            ->orderBy('end_date', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Get exams that allow multiple attempts.
     *
     * @return array
     */
    public function getExamsWithMultipleAttempts(): array
    {
        return Exam::where('max_attempts', '>', 1)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Get active exams (Published and not expired).
     *
     * @return array
     */
    public function getActiveExams(): array
    {
        return Exam::where('is_published', true)
            ->whereDate('end_date', '>=', now())
            ->orderBy('start_date', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * Get expired exams.
     *
     * @return array
     */
    public function getExpiredExams(): array
    {
        return Exam::whereDate('end_date', '<', now())
            ->orderBy('end_date', 'desc')
            ->get()
            ->toArray();
    }
}
