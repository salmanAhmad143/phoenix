<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

Route::middleware('auth:api')->get('/user/detail', function (Request $request) {
    return $request->profile();
});

Route::group(['namespace' => 'Api\v1', 'prefix' => 'v1'], function () {
    Route::post('/users/registration', 'Auth\RegistrationController@registration');
    Route::post('/users/email/verification', 'Auth\RegistrationController@emailVerification');
    Route::post('/users/password/recovery-token', 'Auth\PasswordRecoveryController@passwordRecoveryToken');
    Route::post('/users/password/update', 'Auth\PasswordRecoveryController@passwordUpdate');
    Route::post('/users/login', 'Auth\AuthController@login')->name('login');

    Route::get('/media', 'MediaController@getMedia');
    Route::get('/media-image', 'MediaController@getMediaImage');
    Route::get('/media-pcm', 'MediaController@getMediaPcm');
    Route::any('/tus/{any?}', 'MediaController@tusUpload')->where('any', '.*');

    Route::group(['middleware' => 'auth:api'], function () {
        Route::group(['prefix' => '/users'], function () {
            Route::post('/logout', 'Auth\AuthController@logout');
            Route::post('/password/change', 'Auth\ChangePasswordController@change');
            Route::get('/', 'UserController@index')->middleware(['permissions:member,canView']);
            Route::put('/update', 'UserController@update')->middleware(['permissions:member,canEdit']);
            Route::put('/update/status', 'UserController@updateUserStatus')->middleware(['permissions:member,canEdit']);
            Route::put('/profile/update', 'UserController@updateProfile')->middleware(['permissions:member,canEdit']);
            Route::delete('/delete', 'UserController@destroy')->middleware(['permissions:member,canDelete']);
        });
        Route::get('/language', 'LanguageController@index');
        Route::group(['prefix' => '/projects'], function () {
            Route::get('/', 'ProjectController@index')->middleware(['permissions:project,canView']);
            Route::get('/details', 'ProjectController@show')->middleware(['permissions:project,canView']);
            Route::post('/create', 'ProjectController@store')->middleware(['permissions:project,canAdd']);
            Route::put('/update', 'ProjectController@update')->middleware(['permissions:project,canEdit']);
            Route::delete('/delete', 'ProjectController@destroy')->middleware(['permissions:project,canDelete']);
            Route::get('/media', 'ProjectController@media')->middleware(['permissions:media,canView']);
        });
        Route::group(['prefix' => '/media'], function () {
            Route::post('/', 'MediaController@store')->middleware(['permissions:media,canAdd']);
            Route::post('/extract-info', 'MediaController@extractInfo')->middleware(['permissions:media,canAdd']);
            Route::get('/details', 'MediaController@show')->middleware(['permissions:media,canView']);
            Route::put('/update', 'MediaController@update')->middleware(['permissions:media,canEdit']);
            Route::delete('/delete', 'MediaController@destroy')->middleware(['permissions:media,canDelete']);
            Route::group(['prefix' => '/user'], function () {
                Route::get('/', 'MediaUserController@index')->middleware(['permissions:media_user,canView']);
                Route::post('/add', 'MediaUserController@store')->middleware(['permissions:media_user,canAdd']);
                Route::delete('/remove', 'MediaUserController@destroy')->middleware(['permissions:media_user,canDelete']);
            });
            Route::group(['prefix' => '/team'], function () {
                Route::get('/', 'MediaTeamController@index')->middleware(['permissions:media_team,canView']);
                Route::post('/add', 'MediaTeamController@store')->middleware(['permissions:media_team,canAdd']);
                Route::delete('/remove', 'MediaTeamController@destroy')->middleware(['permissions:media_team,canDelete']);
            });
            Route::group(['prefix' => '/captions'], function () {
                Route::post('/original/create', 'TranscriptCaptionController@genearteTranscription')->middleware(['permissions:transcription,canAdd']);
                Route::get('/original/list', 'TranscriptCaptionController@transcriptionList')->middleware(['permissions:transcription,canView']);
                Route::get('/original', 'TranscriptCaptionController@captions')->middleware(['permissions:caption,canView']);
                Route::put('/original/update', 'TranscriptCaptionController@transcriptionListUpdate')->middleware(['permissions:transcription,canEdit']);
                Route::put('/original/complete', 'TranscriptCaptionController@markAsComplete')->middleware(['permissions:mark_complete,canEdit']);
                Route::put('/original/approved', 'TranscriptCaptionController@markAsApproved')->middleware(['permissions:transcription,canEdit']);
                Route::delete('/original/delete', 'TranscriptCaptionController@transcriptionListDelete')->middleware(['permissions:transcription,canDelete']);

                Route::post('/translation/create', 'TranslateCaptionController@genearteTranslation')->middleware(['permissions:translation,canAdd']);
                Route::get('/translation/list', 'TranslateCaptionController@translationList')->middleware(['permissions:translation,canView']);
                Route::get('/translation', 'TranslateCaptionController@captions')->middleware(['permissions:caption,canView']);
                Route::put('/translation/update', 'TranslateCaptionController@translationListUpdate')->middleware(['permissions:translation,canEdit']);
                Route::put('/translation/complete', 'TranslateCaptionController@markAsComplete')->middleware(['permissions:mark_complete,canEdit']);
                Route::delete('/translation/delete', 'TranslateCaptionController@translationListDelete')->middleware(['permissions:translation,canDelete']);
            });

            Route::post('transcript/assignment', 'TranscriptAssignmentController@transcriptAssignment')->middleware(['permissions:assignment,canAdd']);
            Route::get('transcript/assignment/workflow-transition', 'TranscriptAssignmentController@getTranscriptTransition')->middleware(['permissions:assignment,canView']);
        });
        Route::group(['prefix' => '/caption'], function () {
            Route::post('/create', 'CaptionController@store')->middleware(['permissions:caption,canAdd']);
            Route::put('/update', 'CaptionController@update')->middleware(['permissions:caption,canEdit']);
            Route::delete('/delete', 'CaptionController@destroy')->middleware(['permissions:caption,canDelete']);
            Route::get('/export', 'CaptionController@export')->middleware(['permissions:caption,canDownload']);
        });
        Route::group(['prefix' => '/roles'], function () {
            Route::get('/', 'RoleController@index')->middleware(['permissions:role,canView']);
            Route::post('/create', 'RoleController@store')->middleware(['permissions:role,canAdd']);
            Route::get('/details', 'RoleController@show')->middleware(['permissions:role,canView']);
            Route::put('/update', 'RoleController@update')->middleware(['permissions:role,canEdit']);
            Route::delete('/delete', 'RoleController@destroy')->middleware(['permissions:role,canDelete']);
        });
        Route::get('/content', 'ContentController@index');
        Route::get('/user/roles/details', 'RoleController@userRolesDetails');
        Route::group(['prefix' => '/team'], function () {
            Route::get('/', 'TeamController@index')->middleware(['permissions:team,canView']);
            Route::post('/create', 'TeamController@store')->middleware(['permissions:team,canAdd']);
            Route::put('/update', 'TeamController@update')->middleware(['permissions:team,canEdit']);
            Route::delete('/delete', 'TeamController@destroy')->middleware(['permissions:team,canDelete']);
            Route::group(['prefix' => '/member'], function () {
                Route::get('/', 'TeamMemberController@index')->middleware(['permissions:member,canView']);
                Route::post('/add', 'TeamMemberController@store')->middleware(['permissions:member,canAdd']);
                Route::delete('/remove', 'TeamMemberController@destroy')->middleware(['permissions:member,canDelete']);
            });
        });
        Route::group(['prefix' => '/guideline'], function () {
            Route::get('/', 'GuidelineController@index')->middleware(['permissions:guideline,canView']);
            Route::post('/create', 'GuidelineController@store')->middleware(['permissions:guideline,canAdd']);
            Route::put('/update', 'GuidelineController@update')->middleware(['permissions:guideline,canEdit']);
            Route::delete('/delete', 'GuidelineController@destroy')->middleware(['permissions:guideline,canDelete']);
        });
        Route::group(['prefix' => '/project'], function () {
            Route::group(['prefix' => '/user'], function () {
                Route::get('/', 'ProjectUserController@index')->middleware(['permissions:project_user,canView']);
                Route::post('/add', 'ProjectUserController@store')->middleware(['permissions:project_user,canAdd']);
                Route::delete('/remove', 'ProjectUserController@destroy')->middleware(['permissions:project_user,canDelete']);
            });
            Route::group(['prefix' => '/team'], function () {
                Route::get('/', 'ProjectTeamController@index')->middleware(['permissions:project_team,canView']);
                Route::post('/add', 'ProjectTeamController@store')->middleware(['permissions:project_team,canAdd']);
                Route::delete('/remove', 'ProjectTeamController@destroy')->middleware(['permissions:project_team,canDelete']);
            });
        });
        Route::group(['prefix' => '/client'], function () {
            Route::get('/', 'ClientController@index')->middleware(['permissions:client,canView']);
            Route::post('/create', 'ClientController@store')->middleware(['permissions:client,canAdd']);
            Route::put('/update', 'ClientController@update')->middleware(['permissions:client,canEdit']);
            Route::delete('/delete', 'ClientController@destroy')->middleware(['permissions:client,canDelete']);
            Route::get('/details', 'ClientController@show')->middleware(['permissions:client,canView']);
        });
        Route::get('/workflow', 'WorkFlowController@index');
        Route::group(['prefix' => '/codemaster'], function () {
            Route::get('/', 'CodeMasterController@index');
        });
        Route::group(['prefix' => '/tag'], function () {
            Route::get('/', 'TagController@index');
        });
    });
});


Route::fallback(function () {
    return response()->json([
        "success" => false,
        "message" => "Page not found",
        "errorCode" => '404',
    ]);
});
