<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\OptionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authenticated user route
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Exam-related routes
Route::prefix('exams')->group(function () {
    Route::get('/', [ExamController::class, 'index']); // List all exams or filter by category
    Route::post('/', [ExamController::class, 'store']); // Create a new exam
    Route::get('{id}', [ExamController::class, 'show']); // Show a specific exam with sections
    Route::put('{id}', [ExamController::class, 'update']); // Update a specific exam
    Route::delete('{id}', [ExamController::class, 'destroy']); // Delete a specific exam
});


// Section-related routes for a specific exam
Route::prefix('exams/{examId}/sections')->group(function () {
    Route::get('/', [SectionController::class, 'index']); // List all sections for a specific exam (with filtering, sorting, and pagination)
    Route::post('/', [SectionController::class, 'store']); // Create a section for a specific exam
});

// General section routes
Route::prefix('sections')->group(function () {
    Route::get('{sectionId}', [SectionController::class, 'show']); // Show a specific section (includes soft-deleted sections)
    Route::put('{sectionId}', [SectionController::class, 'update']); // Update a section
    Route::delete('{sectionId}', [SectionController::class, 'destroy']); // Soft delete a section
    Route::post('{sectionId}/restore', [SectionController::class, 'restore']); // Restore a soft-deleted section
});


// Question-related routes for a specific section
Route::prefix('sections/{sectionId}/questions')->group(function () {
    Route::get('/', [QuestionController::class, 'index']); // List all questions for a specific section (with filtering, sorting, and pagination)
    Route::post('/', [QuestionController::class, 'store']); // Create a new question for a specific section
});

// General question routes
Route::prefix('questions')->group(function () {
    Route::get('{questionId}', [QuestionController::class, 'show']); // Show a specific question with options (includes soft-deleted questions)
    Route::put('{questionId}', [QuestionController::class, 'update']); // Update a specific question
    Route::delete('{questionId}', [QuestionController::class, 'destroy']); // Soft delete a specific question
    Route::post('{questionId}/restore', [QuestionController::class, 'restore']); // Restore a soft-deleted question
});




// Option-related routes for a specific question
Route::prefix('questions/{questionId}/options')->group(function () {
    Route::get('/', [OptionController::class, 'index']); // List all options for a specific question (with filtering, sorting, and pagination)
    Route::post('/', [OptionController::class, 'store']); // Create an option for a specific question
});

// General option routes
Route::prefix('options')->group(function () {
    Route::get('{optionId}', [OptionController::class, 'show']); // Show a specific option (includes soft-deleted options)
    Route::put('{optionId}', [OptionController::class, 'update']); // Update a specific option
    Route::delete('{optionId}', [OptionController::class, 'destroy']); // Soft delete a specific option
    Route::post('{optionId}/restore', [OptionController::class, 'restore']); // Restore a soft-deleted option
});
