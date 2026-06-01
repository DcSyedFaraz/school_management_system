<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use Config;
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
            $classId = '1';
            $examId = 1;
            $startDate = date('Y-m-d', strtotime('' . date('Y') . '-' . date('m') . '-01'));
            $endDate = date('Y-m-d');

            $subjects = Config::get("subjects.$classId", Config::get("subjects.class_default"));

            $columns = array_merge(
                ['markId', 'gender', 'studentName', 'classId', 'examId', 'schoolId', 'regionId', 'districtId', 'wardId'],
                $subjects,
                ['total', 'average','examDate']
            );

            $marks = Marks::select($columns)->where([
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

            $data = compact('marks', 'classes', 'exams', 'url1', 'url2', 'url3', 'url4', 'classId', 'examId', 'startDate', 'endDate', 'subjects');
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

            $subjects = Config::get("subjects.$classId", Config::get("subjects.class_default"));

            $columns = array_merge(
                ['markId', 'gender', 'studentName', 'classId', 'examId', 'schoolId', 'regionId', 'districtId', 'wardId'],
                $subjects,
                ['total', 'average','examDate']
            );

            $marks = Marks::select($columns)->where([
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

            $data = compact('marks', 'classes', 'exams', 'url1', 'url2', 'url3', 'url4', 'classId', 'examId', 'startDate', 'endDate', 'subjects');
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
                    'exam' => 'required|integer'
                ]
            );
// dd($req->all());
            // $subjectsByClass = [
            //     1 => ['kuhesabu', 'kusoma', 'kuandika', 'english', 'mazingira', 'michezo'],
            //     2 => ['kuhesabu', 'kusoma', 'kuandika', 'english', 'mazingira', 'utamaduni'],
            //     3 => ['hisabati', 'kiswahili', 'sayansi', 'english', 'maadili', 'jiographia', 'smichezo'],
            //     'default' => ['hisabati', 'kiswahili', 'sayansi', 'english', 'jamii', 'maadili']
            // ];

            $classId = $req->input('class');
            $subjects = config("subjects.$classId") ?: config('subjects.class_default');

            $validationRules = [];
            foreach ($subjects as $subject) {
                $validationRules["{$subject}Marks"] = 'nullable|numeric|min:0|max:50';
            }
            $req->validate($validationRules);

            $mark = new Marks;
            $mark['examDate'] = $req['examDate'];
            $mark['classId'] = $req['class'];
            $mark['studentName'] = $req['studentName'];
            $mark['gender'] = $req['gender'];

            $total = 0;
            $subjectCount = 0;
            foreach ($subjects as $subject) {
                $subjectKey = "{$subject}Marks";
                $val = ($req[$subjectKey] !== null && $req[$subjectKey] !== '') ? (int) $req[$subjectKey] : null;
                $mark[$subject] = $val;
                if ($val !== null) {
                    $total += $val;
                    $subjectCount++;
                }
            }

            $mark['total'] = $total;
            $mark['average'] = $subjectCount > 0 ? number_format($total / $subjectCount, 2) : null;
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
                $classId = $req['updatedClass'];
                $subjects = $this->getSubjectsForClass($classId);

                $validationRules = [
                    'updatedStudentName' => 'required',
                    'updatedGender' => 'required',
                    'updatedExamDate' => 'required|date',
                    'updatedClass' => 'required|integer',
                    'updatedExam' => 'required|integer',

                ];

                foreach ($subjects as $subject) {
                    $subjectKey = strtolower($subject);
                    $validationRules["updated{$subjectKey}Marks"] = 'nullable|numeric|min:0|max:50';
                }

                $req->validate($validationRules);

                $validMark['examDate'] = $req['updatedExamDate'];
                $validMark['classId'] = $req['updatedClass'];
                $validMark['studentName'] = $req['updatedStudentName'];
                $validMark['gender'] = $req['updatedGender'];
                $validMark['examId'] = $req['updatedExam'];

                $totalMarks = 0;
                $subjectCount = 0;
                foreach ($subjects as $subject) {
                    $subjectKey = strtolower($subject);
                    $rawVal = $req["updated{$subjectKey}Marks"];
                    $val = ($rawVal !== null && $rawVal !== '') ? (int) $rawVal : null;
                    $validMark[$subjectKey] = $val;
                    if ($val !== null) {
                        $totalMarks += $val;
                        $subjectCount++;
                    }
                }

                $validMark['total'] = $totalMarks;
                $validMark['average'] = $subjectCount > 0 ? number_format($totalMarks / $subjectCount, 2) : null;
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
        $mark = Marks::find($id);
        $subjects = $this->getSubjectsForClass($mark->classId);

        return response()->json([
            'status' => 200,
            'data' => $mark,
            'subjects' => $subjects
        ]);
    }
    public function getSubjectsForClass($classId)
    {
        $subjects = Config::get('subjects');

        return $subjects[$classId] ?? $subjects['class_default'];
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
            // dd($idArray);
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

            $exists = Marks::where('classId', $req['class'])
                ->where('examDate', $req['examDate'])
                ->where('examId', $req['exam'])
                ->where('schoolId', Session::get('userSchool'))
                ->where('isDeleted', 0)
                ->exists();

            if ($exists) {
                return redirect('/dashboard/uploads')
                    ->with('error', 'Matokeo ya darasa hili kwa tarehe hii tayari yameshapakiwa! Nenda kwenye Ukurasa wa Matokeo Kavute Matokeo!');
            }

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
