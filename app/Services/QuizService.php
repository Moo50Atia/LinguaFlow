<?php

namespace App\Services;

use App\Models\QuizQuestion;

class QuizService
{
    public function listForCourse(int $courseId)
    {
        return QuizQuestion::where('course_id', $courseId)
            ->orWhereHas('lesson', function ($query) use ($courseId) {
                $query->where('course_id', $courseId);
            })
            ->with('lesson')
            ->orderBy('order')
            ->get();
    }

    public function create(array $data): QuizQuestion
    {
        return QuizQuestion::create([
            'lesson_id'      => $data['lesson_id'] ?? null,
            'course_id'      => $data['course_id'] ?? null,
            'type'           => $data['type'],
            'question'       => $data['question'],
            'options'        => $data['options'], // casted to json automatically by model
            'correct_answer' => $data['correct_answer'],
            'order'          => $data['order'],
        ]);
    }

    public function update(QuizQuestion $question, array $data): QuizQuestion
    {
        $question->update($data);
        return $question;
    }

    public function delete(QuizQuestion $question): void
    {
        $question->delete();
    }
}
