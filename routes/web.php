<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\admin\DashboardController;
use App\Http\Controllers\admin\UserController;
use App\Http\Controllers\admin\RegionController;
use App\Http\Controllers\admin\DistrictController;
use App\Http\Controllers\admin\WardController;
use App\Http\Controllers\admin\SchoolController;
use App\Http\Controllers\admin\ReportController;
use App\Http\Controllers\admin\DetailedReportController;
use App\Http\Controllers\admin\SubjectReportController;

use App\Http\Controllers\user\UserDashboardController;
use App\Http\Controllers\user\UserReportController;
use App\Http\Controllers\user\UserDetailedReportController;
use App\Http\Controllers\user\UserSubjectReportController;
use App\Http\Controllers\user\UploadController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [DashboardController::class, 'signIn']);
Route::post('/signIn', [DashboardController::class, 'login']);
Route::get('/admin-dashboard', [DashboardController::class, 'adminDashboard']);
Route::post('/admin-dashboard/filter', [DashboardController::class, 'adminDashboardFilter']);
Route::get('/forgotPassword', [DashboardController::class, 'forgotPassword']);
Route::post('/forgotPassword', [DashboardController::class, 'forgotEmail']);
Route::get('/resetPassword/{id}', [DashboardController::class, 'resetPasswordPage']);
Route::post('/resetPassword', [DashboardController::class, 'resetPassword']);
Route::post('/changePassword', [DashboardController::class, 'changePassword']);
Route::get('/dashboard', [DashboardController::class, 'dashboard']);
Route::get('/changeLang/{lang}', [DashboardController::class, 'changeLang']);
Route::post('/logout', [DashboardController::class, 'logout']);
Route::get('/query', [DashboardController::class, 'query']);

Route::get('/admin-dashboard/teachers', [UserController::class, 'teachers']);
Route::get('/admin-dashboard/admins', [UserController::class, 'admins']);
Route::post('/user/save', [UserController::class, 'saveAdmin']);
Route::post('/user/update', [UserController::class, 'updateAdmin']);
Route::get('/userInfo/{id}', [UserController::class, 'adminInfo']);
Route::post('/changeAdminActivity', [UserController::class, 'adminActivity']);

Route::get('/admin-dashboard/regions', [RegionController::class, 'regions']);
Route::post('/region/save', [RegionController::class, 'saveRegion']);
Route::post('/region/update', [RegionController::class, 'updateRegion']);
Route::post('/region/delete', [RegionController::class, 'deleteRegion']);
Route::get('/regionInfo/{id}', [RegionController::class, 'regionInfo']);

Route::get('/admin-dashboard/districts', [DistrictController::class, 'districts']);
Route::post('/district/save', [DistrictController::class, 'saveDistrict']);
Route::post('/district/update', [DistrictController::class, 'updateDistrict']);
Route::post('/district/delete', [DistrictController::class, 'deleteDistrict']);
Route::get('/districtInfo/{id}', [DistrictController::class, 'districtInfo']);

Route::get('/admin-dashboard/wards', [WardController::class, 'wards']);
Route::post('/ward/save', [WardController::class, 'saveWard']);
Route::post('/ward/update', [WardController::class, 'updateWard']);
Route::post('/ward/delete', [WardController::class, 'deleteWard']);
Route::get('/wardInfo/{id}', [WardController::class, 'wardInfo']);

Route::get('/admin-dashboard/schools', [SchoolController::class, 'schools']);
Route::post('/school/save', [SchoolController::class, 'saveSchool']);
Route::post('/school/update', [SchoolController::class, 'updateSchool']);
Route::post('/school/delete', [SchoolController::class, 'deleteSchool']);
Route::get('/schoolInfo/{id}', [SchoolController::class, 'schoolInfo']);

Route::get('/admin-dashboard/reports', [ReportController::class, 'reports']);
Route::post('/filterReport', [ReportController::class, 'filterReport']);
Route::post('/downloadReport', [ReportController::class, 'downloadReport']);
Route::get('/admin-dashboard/student-data', [ReportController::class, 'studentData']);
Route::post('/filterStudentData', [ReportController::class, 'studentDataFilter']);
Route::post('/downloadStudentData', [ReportController::class, 'downloadStudentData']);

Route::get('/dashboard', [UserDashboardController::class, 'adminDashboard']);
Route::post('/dashboard/filter', [UserDashboardController::class, 'adminDashboardFilter']);

Route::get('/dashboard/reports', [UserReportController::class, 'reports']);
Route::post('/filterUserReport', [UserReportController::class, 'filterReport']);
Route::post('/downloadTeacherReport', [UserReportController::class, 'downloadTeacherReport']);

Route::get('/dashboard/teacher-detailed-report', [UserDetailedReportController::class, 'reports']);
Route::post('/filterTeacherDetailedReport', [UserDetailedReportController::class, 'filterReport']);
Route::post('/downloadTeacherDetailedReport', [UserDetailedReportController::class, 'downloadTeacherReport']);

Route::get('/dashboard/teacher-subject-report', [UserSubjectReportController::class, 'reports']);
Route::post('/filterTeacherSubjectReport', [UserSubjectReportController::class, 'filterReport']);
Route::post('/downloadTeacherSubjectReport', [UserSubjectReportController::class, 'downloadTeacherSubjectReport']);

Route::get('/admin-dashboard/subject-report', [SubjectReportController::class, 'reports']);
Route::post('/filterSubjectReport', [SubjectReportController::class, 'filterReport']);
Route::post('/downloadSubjectReport', [SubjectReportController::class, 'downloadSubjectReport']);

Route::get('/dashboard/detailed-report', [DetailedReportController::class, 'reports']);
Route::post('/filterDetailedReport', [DetailedReportController::class, 'filterReport']);
Route::post('/downloadDetailedReport', [DetailedReportController::class, 'downloadAdminReport']);

Route::get('/dashboard/uploads', [UploadController::class, 'uploads']);
Route::post('/uploads/save', [UploadController::class, 'saveUpload']);
Route::get('/uploadInfo/{id}', [UploadController::class, 'uploadInfo']);
Route::post('/uploads/delete', [UploadController::class, 'deleteUpload']);
Route::post('/uploads/bulkDelete', [UploadController::class, 'deleteBulkUpload']);
Route::post('/filterUploads', [UploadController::class, 'filterUploads']);
Route::post('/uploads/update', [UploadController::class, 'updateUpload']);
Route::post('/uploads/file', [UploadController::class, 'fileUpload']);