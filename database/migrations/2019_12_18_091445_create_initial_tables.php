<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInitialTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('profile', function (Blueprint $table) {
            $table->increments('profileId');
            $table->string('primaryEmail', 225)->unique();
            $table->string('secondaryEmail', 225)->unique();
            $table->integer('age');
            $table->string('gender', 15);
            $table->string('primaryMobileNo', 15);
            $table->string('secondaryMobileNo', 15)->nullable();
            $table->unsignedInteger('countryId');
            $table->unsignedInteger('stateId');
            $table->unsignedInteger('cityId');
            $table->text('address');
            $table->integer('pincode');
            $table->unsignedInteger('createdBy');
            $table->dateTime('createdAt');
            $table->unsignedInteger('updatedBy')->nullable();
            $table->dateTime('updatedAt')->nullable();
        });
        
        Schema::create('role', function (Blueprint $table) {
            $table->increments('roleId');
            $table->string('name', 100)->unique();
            $table->text('description');
            $table->tinyInteger('status')->default('1');
            $table->unsignedInteger('createdBy');
            $table->dateTime('createdAt');
            $table->unsignedInteger('updatedBy')->nullable();
            $table->dateTime('updatedAt')->nullable();

            $table->foreign('createdBy')->references('profileId')->on('profile')->onUpdate('cascade');
            $table->foreign('updatedBy')->references('profileId')->on('profile')->onUpdate('cascade');
        });
        
        Schema::create('content', function (Blueprint $table) {
            $table->increments('contentId');
            $table->string('name', 50);
            $table->string('code', 20);
            $table->tinyInteger('status')->default('1');
            $table->unsignedInteger('createdBy');
            $table->dateTime('createdAt');
            $table->unsignedInteger('updatedBy')->nullable();
            $table->dateTime('updatedAt')->nullable();

            $table->foreign('createdBy')->references('profileId')->on('profile')->onUpdate('cascade');
            $table->foreign('updatedBy')->references('profileId')->on('profile')->onUpdate('cascade');
        });
        
        Schema::create('permission', function (Blueprint $table) {
            $table->increments('permissionId');
            $table->unsignedInteger('roleId');
            $table->unsignedInteger('contentId');
            $table->tinyInteger('view');
            $table->tinyInteger('add');
            $table->tinyInteger('edit');
            $table->tinyInteger('delete');
            $table->tinyInteger('download');
            $table->tinyInteger('status')->default('1');
            $table->unsignedInteger('createdBy');
            $table->dateTime('createdAt');
            $table->unsignedInteger('updatedBy')->nullable();
            $table->dateTime('updatedAt')->nullable();

            $table->foreign('roleId')->references('roleId')->on('role')->onUpdate('cascade');
            $table->foreign('contentId')->references('contentId')->on('content')->onUpdate('cascade');
            $table->foreign('createdBy')->references('profileId')->on('profile')->onUpdate('cascade');
            $table->foreign('updatedBy')->references('profileId')->on('profile')->onUpdate('cascade');
        });
        
        Schema::create('user_login', function (Blueprint $table) {
            $table->increments('userLoginId');
            $table->unsignedInteger('profileId');
            $table->string('name', 50);
            $table->string('email', 225)->unique();
            $table->dateTime('emailVerifiedAt')->nullable();
            $table->string('password', 225);
            $table->string('rememberToken', 100)->nullable();
            $table->unsignedInteger('roleId')->nullable();
            $table->unsignedTinyInteger('status')->default(1);
            $table->unsignedInteger('createdBy');
            $table->dateTime('createdAt');
            $table->unsignedInteger('updatedBy')->nullable();
            $table->dateTime('updatedAt')->nullable();

            $table->foreign('profileId')->references('profileId')->on('profile')->onUpdate('cascade');
            $table->foreign('roleId')->references('roleId')->on('role')->onUpdate('cascade');
            $table->foreign('createdBy')->references('profileId')->on('profile')->onUpdate('cascade');
            $table->foreign('updatedBy')->references('profileId')->on('profile')->onUpdate('cascade');
        });
        
        Schema::create('workflow', function (Blueprint $table) {
            $table->tinyIncrements('workflowId');
            $table->string('name', 50);
            $table->tinyInteger('status')->default('1');
            $table->unsignedInteger('createdBy');
            $table->dateTime('createdAt');
            $table->unsignedInteger('updatedBy')->nullable();
            $table->dateTime('updatedAt')->nullable();

            $table->foreign('createdBy')->references('profileId')->on('profile')->onUpdate('cascade');
            $table->foreign('updatedBy')->references('profileId')->on('profile')->onUpdate('cascade');
        });
        
        Schema::create('transition', function (Blueprint $table) {
            $table->tinyIncrements('transitionId');
            $table->string('name', 50);
            $table->unsignedTinyInteger('workflowId');
            $table->tinyInteger('status')->default('1');
            $table->unsignedInteger('createdBy');
            $table->dateTime('createdAt');
            $table->unsignedInteger('updatedBy')->nullable();
            $table->dateTime('updatedAt')->nullable();

            $table->foreign('workflowId')->references('workflowId')->on('workflow')->onUpdate('cascade');
            $table->foreign('createdBy')->references('profileId')->on('profile')->onUpdate('cascade');
            $table->foreign('updatedBy')->references('profileId')->on('profile')->onUpdate('cascade');
        });
        
        Schema::create('transition_state', function (Blueprint $table) {
            $table->tinyIncrements('transitionStateId');
            $table->string('name', 50);
            $table->unsignedTinyInteger('workflowId');
            $table->tinyInteger('status')->default('1');
            $table->unsignedInteger('createdBy');
            $table->dateTime('createdAt');
            $table->unsignedInteger('updatedBy')->nullable();
            $table->dateTime('updatedAt')->nullable();

            $table->foreign('workflowId')->references('workflowId')->on('workflow')->onUpdate('cascade');
            $table->foreign('createdBy')->references('profileId')->on('profile')->onUpdate('cascade');
            $table->foreign('updatedBy')->references('profileId')->on('profile')->onUpdate('cascade');
        });
        
        Schema::create('transition_assignment', function (Blueprint $table) {
            $table->tinyIncrements('transitionAssignmentId');
            $table->unsignedTinyInteger('currentStateId');
            $table->unsignedTinyInteger('nextStateId');
            $table->unsignedTinyInteger('transitionId');
            $table->string('currentStateStatus', 10); 
            $table->unsignedInteger('createdBy');
            $table->dateTime('createdAt');
            $table->unsignedInteger('updatedBy')->nullable();
            $table->dateTime('updatedAt')->nullable();

            $table->foreign('currentStateId')->references('transitionStateId')->on('transition_state')->onUpdate('cascade');
            $table->foreign('nextStateId')->references('transitionStateId')->on('transition_state')->onUpdate('cascade');
            $table->foreign('transitionId')->references('transitionId')->on('transition')->onUpdate('cascade');
            $table->foreign('createdBy')->references('profileId')->on('profile')->onUpdate('cascade');
            $table->foreign('updatedBy')->references('profileId')->on('profile')->onUpdate('cascade');
        });
        
        Schema::create('guideline', function (Blueprint $table) {
            $table->increments('guidelineId');
            $table->unsignedInteger('minDuration');
            $table->unsignedInteger('maxDuration');
            $table->unsignedTinyInteger('frameGap'); 
            $table->unsignedTinyInteger('maxLinePerSubtitle'); 
            $table->unsignedTinyInteger('maxCharsPerLine'); 
            $table->unsignedTinyInteger('maxCharsPerSecond');
            $table->unsignedTinyInteger('subtitleSyncAccuracy');
            $table->unsignedInteger('languageId');

            $table->unsignedInteger('createdBy');
            $table->dateTime('createdAt');
            $table->unsignedInteger('updatedBy')->nullable();
            $table->dateTime('updatedAt')->nullable();

            $table->foreign('createdBy')->references('profileId')->on('profile')->onUpdate('cascade');
            $table->foreign('updatedBy')->references('profileId')->on('profile')->onUpdate('cascade');
        });
        
        Schema::create('project', function (Blueprint $table) {
            $table->increments('projectId');
            $table->string('name', 50);
            $table->unsignedTinyInteger('workflowId');
            $table->unsignedTinyInteger('status')->default(1);
            $table->unsignedInteger('createdBy');
            $table->dateTime('createdAt');
            $table->unsignedInteger('updatedBy')->nullable();
            $table->dateTime('updatedAt')->nullable();

            $table->foreign('workflowId')->references('workflowId')->on('workflow')->onUpdate('cascade');
            $table->foreign('createdBy')->references('profileId')->on('profile')->onUpdate('cascade');
            $table->foreign('updatedBy')->references('profileId')->on('profile')->onUpdate('cascade');
        });
        
        Schema::create('media', function (Blueprint $table) {
            $table->increments('mediaId');
            $table->string('name');
            $table->string('videoPath');
            $table->string('audioPath');
            $table->unsignedInteger('languageId');
            $table->unsignedInteger('duration');
            $table->decimal('videoFrameRate', 7, 5);
            $table->unsignedMediumInteger('videoSampleRate');
            $table->unsignedSmallInteger('videoBitRate');
            $table->unsignedTinyInteger('workflowId')->nullable();
            $table->unsignedInteger('projectId');
            $table->unsignedTinyInteger('status')->default(1);
            $table->unsignedInteger('createdBy');
            $table->dateTime('createdAt');
            $table->unsignedInteger('updatedBy')->nullable();
            $table->dateTime('updatedAt')->nullable();

            $table->foreign('workflowId')->references('workflowId')->on('workflow')->onUpdate('cascade');
            $table->foreign('projectId')->references('projectId')->on('project')->onUpdate('cascade');
            $table->foreign('createdBy')->references('profileId')->on('profile')->onUpdate('cascade');
            $table->foreign('updatedBy')->references('profileId')->on('profile')->onUpdate('cascade');
        });

        Schema::create('media_transcript', function (Blueprint $table) {
            $table->increments('mediaTranscriptId');
            $table->unsignedInteger('mediaId');
            $table->unsignedInteger('languageId');
            $table->unsignedInteger('minDuration');
            $table->unsignedInteger('maxDuration');
            $table->unsignedTinyInteger('frameGap'); 
            $table->unsignedTinyInteger('maxLinePerSubtitle'); 
            $table->unsignedTinyInteger('maxCharsPerLine'); 
            $table->unsignedTinyInteger('maxCharsPerSecond');
            $table->unsignedTinyInteger('subtitleSyncAccuracy');
            $table->tinyInteger('autoTranscriptionStatus')->default('0');
            $table->unsignedTinyInteger('transitionStateId');
            $table->decimal('cost', 9, 2);
            $table->unsignedInteger('currencyId');
            $table->string('unit', 15);
            $table->unsignedTinyInteger('workflowId');
            $table->unsignedInteger('linguistId');
            $table->string('transitionStatus', 20);
            $table->tinyInteger('pmApprovalStatus')->default('0');
            
            $table->unsignedInteger('createdBy');
            $table->dateTime('createdAt');
            $table->unsignedInteger('updatedBy')->nullable();
            $table->dateTime('updatedAt')->nullable();

            $table->foreign('mediaId')->references('mediaId')->on('media')->onUpdate('cascade');
            $table->foreign('transitionStateId')->references('transitionStateId')->on('transition_state')->onUpdate('cascade');
            $table->foreign('workflowId')->references('workflowId')->on('workflow')->onUpdate('cascade');
            $table->foreign('linguistId')->references('profileId')->on('profile')->onUpdate('cascade');
            $table->foreign('createdBy')->references('profileId')->on('profile')->onUpdate('cascade');
            $table->foreign('updatedBy')->references('profileId')->on('profile')->onUpdate('cascade');
        });
        
        Schema::create('media_caption', function (Blueprint $table) {
            $table->increments('mediaCaptionId');
            $table->unsignedInteger('mediaTranscriptId');
            $table->integer('startTime');
            $table->integer('endTime');
            $table->text('sourceText')->nullable();
            $table->text('targetText')->nullable();
            $table->tinyInteger('completeStatus')->default('0');
            
            $table->unsignedInteger('createdBy');
            $table->dateTime('createdAt');
            $table->unsignedInteger('updatedBy')->nullable();
            $table->dateTime('updatedAt')->nullable();

            $table->foreign('mediaTranscriptId')->references('mediaTranscriptId')->on('media_transcript')->onUpdate('cascade');
            $table->foreign('createdBy')->references('profileId')->on('profile')->onUpdate('cascade');
            $table->foreign('updatedBy')->references('profileId')->on('profile')->onUpdate('cascade');
        });

        Schema::create('third_party_api_call', function (Blueprint $table) {
            $table->bigIncrements('thirdPartyAPICallId');
            $table->unsignedInteger('mediaTranscriptId');
            $table->string('url');
            $table->string('provider', 20);
            $table->text('request')->nullable();
            $table->text('response')->nullable();
            $table->text('words')->nullable();
            $table->decimal('confidence', 6, 5)->nullable();
            $table->dateTime('requestTime');
            $table->dateTime('responseTime')->nullable();
            $table->smallInteger('responseStatus')->nullable();
            $table->string('clientIp', 25);
            $table->string('callingUrl');
            $table->tinyInteger('status')->default('0');

            $table->foreign('mediaTranscriptId')->references('mediaTranscriptId')->on('media_transcript')->onUpdate('cascade');
        });

        Schema::dropIfExists('cache');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('third_party_api_call');
        Schema::dropIfExists('media_caption');
        Schema::dropIfExists('media_transcript');
        Schema::dropIfExists('media');
        Schema::dropIfExists('project');
        Schema::dropIfExists('guideline');
        Schema::dropIfExists('transition_assignment');
        Schema::dropIfExists('transition_state');
        Schema::dropIfExists('transition');
        Schema::dropIfExists('workflow');
        Schema::dropIfExists('user_login');
        Schema::dropIfExists('permission');
        Schema::dropIfExists('content');
        Schema::dropIfExists('role');
        Schema::dropIfExists('profile');
    }
}
