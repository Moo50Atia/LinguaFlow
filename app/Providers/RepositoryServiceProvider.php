<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(\App\Repositories\Interfaces\BookingRepositoryInterface::class, \App\Repositories\BookingRepository::class);
        $this->app->bind(\App\Repositories\Interfaces\CertificateRepositoryInterface::class, \App\Repositories\CertificateRepository::class);
        $this->app->bind(\App\Repositories\Interfaces\ChatRepositoryInterface::class, \App\Repositories\ChatRepository::class);
        $this->app->bind(\App\Repositories\Interfaces\ChatMemberRepositoryInterface::class, \App\Repositories\ChatMemberRepository::class);
        $this->app->bind(\App\Repositories\Interfaces\CourseRepositoryInterface::class, \App\Repositories\CourseRepository::class);
        $this->app->bind(\App\Repositories\Interfaces\EnrollmentRepositoryInterface::class, \App\Repositories\EnrollmentRepository::class);
        $this->app->bind(\App\Repositories\Interfaces\InstructorRepositoryInterface::class, \App\Repositories\InstructorRepository::class);
        $this->app->bind(\App\Repositories\Interfaces\InstructorSlotRepositoryInterface::class, \App\Repositories\InstructorSlotRepository::class);
        $this->app->bind(\App\Repositories\Interfaces\LessonRepositoryInterface::class, \App\Repositories\LessonRepository::class);
        $this->app->bind(\App\Repositories\Interfaces\LessonCompletionRepositoryInterface::class, \App\Repositories\LessonCompletionRepository::class);
        $this->app->bind(\App\Repositories\Interfaces\LessonMaterialRepositoryInterface::class, \App\Repositories\LessonMaterialRepository::class);
        $this->app->bind(\App\Repositories\Interfaces\MessageRepositoryInterface::class, \App\Repositories\MessageRepository::class);
        $this->app->bind(\App\Repositories\Interfaces\MomentRepositoryInterface::class, \App\Repositories\MomentRepository::class);
        $this->app->bind(\App\Repositories\Interfaces\MomentCommentRepositoryInterface::class, \App\Repositories\MomentCommentRepository::class);
        $this->app->bind(\App\Repositories\Interfaces\MomentCorrectionRepositoryInterface::class, \App\Repositories\MomentCorrectionRepository::class);
        $this->app->bind(\App\Repositories\Interfaces\MomentLikeRepositoryInterface::class, \App\Repositories\MomentLikeRepository::class);
        $this->app->bind(\App\Repositories\Interfaces\NotificationRepositoryInterface::class, \App\Repositories\NotificationRepository::class);
        $this->app->bind(\App\Repositories\Interfaces\PodcastRepositoryInterface::class, \App\Repositories\PodcastRepository::class);
        $this->app->bind(\App\Repositories\Interfaces\QuizQuestionRepositoryInterface::class, \App\Repositories\QuizQuestionRepository::class);
        $this->app->bind(\App\Repositories\Interfaces\QuizResultRepositoryInterface::class, \App\Repositories\QuizResultRepository::class);
        $this->app->bind(\App\Repositories\Interfaces\ReviewRepositoryInterface::class, \App\Repositories\ReviewRepository::class);
        $this->app->bind(\App\Repositories\Interfaces\StudyDayRepositoryInterface::class, \App\Repositories\StudyDayRepository::class);
        $this->app->bind(\App\Repositories\Interfaces\SubscriptionRepositoryInterface::class, \App\Repositories\SubscriptionRepository::class);
        $this->app->bind(\App\Repositories\Interfaces\UserRepositoryInterface::class, \App\Repositories\UserRepository::class);
        $this->app->bind(\App\Repositories\Interfaces\UserInterestRepositoryInterface::class, \App\Repositories\UserInterestRepository::class);
        $this->app->bind(\App\Repositories\Interfaces\UserLanguageRepositoryInterface::class, \App\Repositories\UserLanguageRepository::class);
    }

    public function boot()
    {
        //
    }
}
