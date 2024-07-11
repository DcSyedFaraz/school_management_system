<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Marks;
use App\Models\Grades;
use App\Models\Exams;
use App\Models\Regions;
use App\Imports\MarksImport;
use Session;
use Excel;

class UploadController extends Controller
{
    public function uploads()
    {
        if (Session::get('loggedin') == true) {
            $classId = 1;
            $examId = 1;
            $startDate = date('Y-m-d', strtotime('' . date('Y') . '-' . date('m') . '-01'));
            $endDate = date('Y-m-d');

            $marks = Marks::select('markId', 'examId', 'classId', 'examDate', 'gender', 'studentName', 'hisabati', 'kiswahili', 'sayansi', 'english', 'jamii', 'maadili', 'total', 'average')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0'],
                ['userId', '=', Session::get('userId')],
                ['classId', '=', $classId],
                ['examId', '=', $examId]
            ])->whereBetween('examDate', [$startDate, $endDate])->orderBy('markId', 'desc')->get();

            $classes = Grades::select('gradeId', 'gradeName')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0']
            ])->get();

            $exams = Exams::select('examId', 'examName')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0']
            ])->get();

            session(['pageTitle' => "Pandisha Faili"]);
            $url1 = url('/uploads/save');
            $url2 = url('/uploads/update');
            $url3 = url('/uploads/delete');
            $url4 = url('/uploads/file');

            $data = compact('marks', 'classes', 'exams', 'url1', 'url2', 'url3', 'url4', 'classId', 'examId', 'startDate', 'endDate');
            return view('user.uploads')->with($data);
        } else {
            return redirect('/')->with('accessDenied', 'Session Expired!');
        }
    }

    public function filterUploads(Request $req)
    {
        if (Session::get('loggedin') == true) {
            $classId = $req['class'];
            $examId = $req['exam'];

            $examCondition = ($examId == '') ? ['examId', '!=', null] : ['examId', '=', $examId];
            $classCondition = ($classId == '') ? ['classId', '!=', null] : ['classId', '=', $classId];
            $startDate = ($req['startDate'] == '') ? "2023-01-01" : $req['startDate'];
            $endDate = ($req['endDate'] == '') ? date('Y-m-d') : $req['endDate'];

            $marks = Marks::select('markId', 'examId', 'classId', 'examDate', 'gender', 'studentName', 'hisabati', 'kiswahili', 'sayansi', 'english', 'jamii', 'maadili', 'total', 'average')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0'],
                ['userId', '=', Session::get('userId')],
                $classCondition,
                $examCondition
            ])->whereBetween('examDate', [$startDate, $endDate])->orderBy('markId', 'desc')->get();

            $classes = Grades::select('gradeId', 'gradeName')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0']
            ])->get();

            $exams = Exams::select('examId', 'examName')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0']
            ])->get();

            session(['pageTitle' => "Pandisha Faili"]);
            $url1 = url('/uploads/save');
            $url2 = url('/uploads/update');
            $url3 = url('/uploads/delete');
            $url4 = url('/uploads/file');

            $data = compact('marks', 'classes', 'exams', 'url1', 'url2', 'url3', 'url4', 'classId', 'examId', 'startDate', 'endDate');
            return view('user.uploads')->with($data);
        } else {
            return redirect('/')->with('accessDenied', 'Session Expired!');
        }
    }

    public function saveUpload(Request $req)
    {
        if (Session::get('loggedin') == true) {
            $req->validate(
                [
                    'studentName' => 'required',
                    'gender' => 'required',
                    'examDate' => 'required|date',
                    'class' => 'required|integer',
                    'firstGrade' => 'required|integer',
                    'exam' => 'required|integer'
                ]
            );

            $subjectsByClass = [
                1 => ['kuhesabu', 'kusoma', 'kuandika', 'english', 'mazingira', 'michezo'],
                2 => ['kuhesabu', 'kusoma', 'kuandika', 'english', 'mazingira', 'utamaduni'],
                3 => ['hisabati', 'kiswahili', 'sayansi', 'english', 'maadili', 'jiographia', 'smichezo'],
                'default' => ['hisabati', 'kiswahili', 'sayansi', 'english', 'jamii', 'maadili']
            ];

            $classId = $req->input('class');
            $subjects = $subjectsByClass[$classId] ?? $subjectsByClass['default'];

            $validationRules = [];
            foreach ($subjects as $subject) {
                $validationRules["{$subject}Marks"] = 'required|numeric|min:0|max:50';
            }
            $req->validate($validationRules);

            $mark = new Marks;
            $mark['examDate'] = $req['examDate'];
            $mark['classId'] = $req['class'];
            $mark['studentName'] = $req['studentName'];
            $mark['gender'] = $req['gender'];
            $mark['firstGrade'] = $req['firstGrade'];

            $total = 0;
            foreach ($subjects as $subject) {
                $subjectKey = "{$subject}Marks";
                $mark[$subject] = $req[$subjectKey];
                $total += $req[$subjectKey];
            }

            $mark['total'] = $total;
            $mark['average'] = number_format(($total / count($subjects)), 2);
            $mark['examId'] = $req['exam'];
            $mark['userId'] = Session::get('userId');
            $mark['regionId'] = Session::get('userRegion');
            $mark['districtId'] = Session::get('userDistrict');
            $mark['wardId'] = Session::get('userWard');
            $mark['schoolId'] = Session::get('userSchool');
            $mark->save();

            Session::flash('success', 'Data Saved Successfully!');
            return redirect('/dashboard/uploads');
        } else {
            return redirect('/')->with('accessDenied', 'Session Expired!');
        }
    }


    public function updateUpload(Request $req)
    {
        if (Session::get('loggedin') == true) {
            $id = $req['entryId'];
            $validMark = Marks::find($id);

            if ($validMark) {
                $req->validate(
                    [
                        'updatedStudentName' => 'required',
                        'updatedGender' => 'required',
                        'updatedExamDate' => 'required|date',
                        'updatedClass' => 'required|integer',
                        'updatedFirstGrade' => 'required|integer',
                        'updatedHisabatiMarks' => 'required|numeric|min:0|max:50',
                        'updatedKiswahiliMarks' => 'required|numeric|min:0|max:50',
                        'updatedSayansiMarks' => 'required|numeric|min:0|max:50',
                        'updatedEnglishMarks' => 'required|numeric|min:0|max:50',
                        'updatedJamiiMarks' => 'required|numeric|min:0|max:50',
                        'updatedMaadiliMarks' => 'required|numeric|min:0|max:50'
                    ]
                );

                $validMark['examDate'] = $req['updatedExamDate'];
                $validMark['classId'] = $req['updatedClass'];
                $validMark['studentName'] = $req['updatedStudentName'];
                $validMark['gender'] = $req['updatedGender'];
                $validMark['firstGrade'] = $req['updatedFirstGrade'];
                $validMark['hisabati'] = $req['updatedHisabatiMarks'];
                $validMark['kiswahili'] = $req['updatedKiswahiliMarks'];
                $validMark['sayansi'] = $req['updatedSayansiMarks'];
                $validMark['english'] = $req['updatedEnglishMarks'];
                $validMark['jamii'] = $req['updatedJamiiMarks'];
                $validMark['maadili'] = $req['updatedMaadiliMarks'];
                $validMark['total'] = $req['updatedHisabatiMarks'] + $req['updatedKiswahiliMarks'] + $req['updatedSayansiMarks'] + $req['updatedEnglishMarks'] + $req['updatedJamiiMarks'] + $req['updatedMaadiliMarks'];
                $validMark['average'] = number_format((($req['updatedHisabatiMarks'] + $req['updatedKiswahiliMarks'] + $req['updatedSayansiMarks'] + $req['updatedEnglishMarks'] + $req['updatedJamiiMarks'] + $req['updatedMaadiliMarks']) / 6), 2);
                $validMark['examId'] = $req['updatedExam'];
                $validMark->save();

                Session::flash('success', 'Data Updated Successfully!');
                return redirect('/dashboard/uploads');
            } else {
                return back()->with('error', 'Entry Not Found!');
            }
        } else {
            return redirect('/')->with('accessDenied', 'Session Expired!');
        }
    }

    public function uploadInfo($id)
    {
        if (Session::get('loggedin') == true) {
            $uploadData = Marks::find($id);

            if ($uploadData) {
                $data = [];
                $data['examDate'] = $uploadData['examDate'];
                $data['classId'] = $uploadData['classId'];
                $data['studentName'] = $uploadData['studentName'];
                $data['gender'] = $uploadData['gender'];
                $data['hisabati'] = $uploadData['hisabati'];
                $data['kiswahili'] = $uploadData['kiswahili'];
                $data['sayansi'] = $uploadData['sayansi'];
                $data['english'] = $uploadData['english'];
                $data['jamii'] = $uploadData['jamii'];
                $data['maadili'] = $uploadData['maadili'];
                $data['examId'] = $uploadData['examId'];
                $data['firstGrade'] = $uploadData['firstGrade'];

                return response()->json([
                    'status' => 200,
                    'data' => $data
                ]);
            } else {
                return response()->json([
                    'status' => 404,
                    'data' => "No Data Found"
                ]);
            }
        } else {
            return redirect('/')->with('accessDenied', 'Session Expired!');
        }
    }

    public function deleteUpload(Request $req)
    {
        if (Session::get('loggedin') == true) {
            $id = $req['delEntryId'];
            $validMark = Marks::find($id);

            if ($validMark) {
                $validMark['isDeleted'] = 1;
                $validMark->save();

                Session::flash('success', 'Data Deleted Successfully!');
                return redirect('/dashboard/uploads');
            } else {
                return back()->with('error', 'Entry Not Found!');
            }
        } else {
            return redirect('/')->with('accessDenied', 'Session Expired!');
        }
    }

    public function deleteBulkUpload(Request $req)
    {
        if (Session::get('loggedin') == true) {
            $idArray = $req['delId'];

            if (isset($req['delId']) && count($idArray) > 0) {
                foreach ($idArray as $id) {
                    $validMark = Marks::find($id);

                    if ($validMark) {
                        $validMark['isDeleted'] = 1;
                        $validMark->save();
                    }
                }

                Session::flash('success', 'Data Deleted Successfully!');
                return redirect('/dashboard/uploads');
            } else {
                Session::flash('error', 'Entry Not Selected!');
                return redirect('/dashboard/uploads');
            }
        } else {
            return redirect('/')->with('accessDenied', 'Session Expired!');
        }
    }

    public function fileUpload(Request $req)
    {
        if (Session::get('loggedin') == true) {
            $req->validate(
                [
                    'exam' => 'required|integer|min:1',
                    'class' => 'required|integer|min:1',
                    'examDate' => 'required|date',
                    'excelFile' => 'required|mimes:xls,xlsx'
                ]
            );

            $userId = Session::get('userId');
            $userRegion = Session::get('userRegion');
            $userDistrict = Session::get('userDistrict');
            $userWard = Session::get('userWard');
            $userSchool = Session::get('userSchool');

            Excel::import(new MarksImport($req->all(), $userId, $userRegion, $userDistrict, $userWard, $userSchool), $req['excelFile']);

            Session::flash('success', 'Data Saved Successfully!');
            return redirect('/dashboard/uploads');
        } else {
            return redirect('/')->with('accessDenied', 'Session Expired!');
        }
    }
}
