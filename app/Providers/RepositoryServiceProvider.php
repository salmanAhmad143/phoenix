<?php

namespace App\Providers;

use App\Repositories\Interfaces\LanguageRepositoryInterface;
use App\Repositories\Interfaces\ContentRepositoryInterface;
use App\Repositories\Interfaces\RoleRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\TeamRepositoryInterface;
use App\Repositories\Interfaces\GuidelineRepositoryInterface;
use App\Repositories\Interfaces\ProjectRepositoryInterface;
use App\Repositories\Interfaces\MediaRepositoryInterface;
use App\Repositories\Interfaces\TranscriptCaptionRepositoryInterface;
use App\Repositories\Interfaces\TranslateCaptionRepositoryInterface;
use App\Repositories\Interfaces\CaptionRepositoryInterface;
use App\Repositories\Interfaces\TranscriptAssignmentRepositoryInterface;
use App\Repositories\Interfaces\WorkflowProcessRepositoryInterface;
use App\Repositories\Interfaces\WorkflowRepositoryInterface;
use App\Repositories\Interfaces\ClientRepositoryInterface;
use App\Repositories\Interfaces\ClientPocRepositoryInterface;
use App\Repositories\Interfaces\ClientOperationalPocRepositoryInterface;
use App\Repositories\Interfaces\AdditionalReportingRepositoryInterface;
use App\Repositories\Interfaces\CodeMasterRepositoryInterface;
use App\Repositories\Interfaces\TagRepositoryInterface;
use App\Repositories\Interfaces\ProjectTagRepositoryInterface;
use App\Repositories\Interfaces\WorkflowTransitionRepositoryInterface;
use App\Repositories\LanguageRepository;
use App\Repositories\ContentRepository;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use App\Repositories\TeamRepository;
use App\Repositories\GuidelineRepository;
use App\Repositories\ProjectRepository;
use App\Repositories\MediaRepository;
use App\Repositories\TranscriptCaptionRepository;
use App\Repositories\TranslateCaptionRepository;
use App\Repositories\CaptionRepository;
use App\Repositories\TranscriptAssignmentRepository;
use App\Repositories\WorkflowProcessRepository;
use App\Repositories\WorkflowRepository;
use App\Repositories\ClientRepository;
use App\Repositories\ClientPocRepository;
use App\Repositories\ClientOperationalPocRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\AdditionalReportingRepository;
use App\Repositories\TagRepository;
use App\Repositories\ProjectTagRepository;
use App\Repositories\CodeMasterRepository;
use App\Repositories\WorkflowTransitionRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            UserRepositoryInterface::class,
            UserRepository::class
        );
        $this->app->bind(
            TeamRepositoryInterface::class,
            TeamRepository::class
        );
        $this->app->bind(
            LanguageRepositoryInterface::class,
            LanguageRepository::class
        );
        $this->app->bind(
            GuidelineRepositoryInterface::class,
            GuidelineRepository::class
        );
        $this->app->bind(
            ContentRepositoryInterface::class,
            ContentRepository::class
        );
        $this->app->bind(
            RoleRepositoryInterface::class,
            RoleRepository::class
        );
        $this->app->bind(
            ProjectRepositoryInterface::class,
            ProjectRepository::class
        );
        $this->app->bind(
            MediaRepositoryInterface::class,
            MediaRepository::class
        );
        $this->app->bind(
            TranscriptCaptionRepositoryInterface::class,
            TranscriptCaptionRepository::class
        );
        $this->app->bind(
            TranslateCaptionRepositoryInterface::class,
            TranslateCaptionRepository::class
        );
        $this->app->bind(
            CaptionRepositoryInterface::class,
            CaptionRepository::class
        );
        $this->app->bind(
            TranscriptAssignmentRepositoryInterface::class,
            TranscriptAssignmentRepository::class
        );
        $this->app->bind(
            WorkflowProcessRepositoryInterface::class,
            WorkflowProcessRepository::class
        );
        $this->app->bind(
            WorkflowRepositoryInterface::class,
            WorkflowRepository::class
        );
        $this->app->bind(
            ClientRepositoryInterface::class,
            ClientRepository::class
        );
        $this->app->bind(
            ClientPocRepositoryInterface::class,
            ClientPocRepository::class
        );
        $this->app->bind(
            ClientOperationalPocRepositoryInterface::class,
            ClientOperationalPocRepository::class
        );
        $this->app->bind(
            AdditionalReportingRepositoryInterface::class,
            AdditionalReportingRepository::class
        );
        $this->app->bind(
            CodeMasterRepositoryInterface::class,
            CodeMasterRepository::class
        );
        $this->app->bind(
            TagRepositoryInterface::class,
            TagRepository::class
        );
        $this->app->bind(
            ProjectTagRepositoryInterface::class,
            ProjectTagRepository::class
        );
        $this->app->bind(
            WorkflowTransitionRepositoryInterface::class,
            WorkflowTransitionRepository::class
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
