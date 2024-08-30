<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Marks;
use App\Models\Grades;
use App\Models\Exams;
use App\Models\Regions;
use App\Models\Districts;
use App\Models\Ranks;
use App\Models\Schools;
use App\Mail\ForgotMail;
use DB;
use Hash;
use Cookie;
use Session;
use Mail;
use App;

class DashboardController extends Controller
{
    public function signIn()
    {
        return view('admin.index');
    }

    public function forgotPassword()
    {
        return view('admin.forgot');
    }

    public function changeLang($lang)
    {
        App::setLocale($lang);
        session(['langSelected' => $lang]);

        $data = [];
        return response()->json([
            'status' => 200,
            'data' => $data
        ]);
    }

    public function forgotEmail(Request $req)
    {
        $req->validate(
            [
                'email' => 'required|email',
            ]
        );

        $validUser = User::where([
            ['email', '=', $req['email']]
        ])->first();

        if ($validUser) {
            $mailData = [
                "username" => $validUser['userName'],
                "token" => $validUser['token']
            ];

            // Mail::to($validUser['email'])->send(new ForgotMail($mailData));

            return back()->with('success', 'Reset Password Mail Sent Successfully!');
        } else {
            return back()->with('accessDenied', 'User Not Registered!');
        }
    }

    public function resetPasswordPage($id)
    {
        $validUser = User::where([
            ['token', '=', $id],
            ['isDeleted', '=', '0']
        ])->first();

        if ($validUser) {
            $userToken = $validUser['token'];
            $data = compact('userToken');
            return view('admin.resetPassword')->with($data);
        } else {
            return redirect('/')->with('accessDenied', 'Token Expired!');
        }
    }

    public function resetPassword(Request $req)
    {
        $req->validate(
            [
                'password' => 'required|confirmed',
                'password_confirmation' => 'required'
            ]
        );

        $validUser = User::where([
            ['token', '=', $req['userToken']],
            ['isDeleted', '=', '0']
        ])->first();

        if ($validUser) {
            $validUser['password'] = Hash::make($req['password']);
            $validUser->save();

            return redirect('/')->with('success', 'Password Reset Successfully!');
        } else {
            return redirect('/')->with('accessDenied', 'Token Expired!');
        }
    }

    public function changePassword(Request $req)
    {
        if (Session::get('loggedin') == true || Session::get('adminLoggedin') == true) {
            $id = $req['userId'];

            $validUser = User::find($id);
            if ($validUser) {
                if ($req['newPassword'] != $req['confirmPassword']) {
                    return back()->with('error', 'Password Mismatch!');
                }

                if (Hash::check($req['currentPassword'], $validUser['password'])) {
                    $validUser['password'] = Hash::make($req['newPassword']);
                    $validUser->save();

                    return back()->with('success', 'Password Changed Successfully!');
                } else {
                    return back()->with('error', 'Wrong Current Password!');
                }
            } else {
                return back()->with('error', 'User Not Found!');
            }
        } else {
            return redirect('/')->with('accessDenied', 'Session Expired!');
        }
    }
    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:6|same:confirmPassword',
        ]);

        // Update the password
        $id = $request->session()->get('userId');
        $user = User::findOrFail($id);
        $user->password = Hash::make($request->password);
        $user->otp = null; // Optional: Clear OTP after password is changed
        $user->save();

        return redirect()->route('login')->with('success', 'Password successfully changed.');
    }
    // Login with userName
    public function login(Request $req)
    {
        // Validate input with more specific rules
        $validatedData = $req->validate([
            'email' => 'required',
            'password' => 'required',
            'rememberMe' => 'nullable|boolean'
        ]);

        try {
            $user = User::where('user_name', $validatedData['email'])
                ->where('isDeleted', '0')
                ->first();

            if (!$user) {
                return back()->with(['accessDenied' => 'Invalid Credential(s)']);
            }

            if ($user->isActive == 0) {
                return back()->with(['accessDenied' => 'Akaunti imefungwa. Lipia Tsh 300 kwa mtoto kwa mwaka. NMB: 52910003854']);
            }

            if ($validatedData['password'] == $user->otp) {
                // Redirect to change password page if password matches OTP
                session(['userId' => $user['userId']]);
                return redirect()->route('password.change')->with('status', 'Please change your password.');
            }

            if (!Hash::check($validatedData['password'], $user->password)) {
                return back()->with(['accessDenied' => 'Invalid Credential(s)']);
            }

            if ($req->filled('rememberMe')) {
                Auth::login($user, true);
            } else {
                Auth::login($user);
            }

            session(['userType' => $user['userType']]);
            session(['userRegion' => $user['regionId']]);
            session(['userDistrict' => $user['districtId']]);
            session(['userWard' => $user['wardId']]);
            session(['userSchool' => $user['schoolId']]);
            session(['userName' => $user['userName']]);
            session(['userEmail' => $user['email']]);
            session(['userId' => $user['userId']]);
            // Set session data based on user type
            if ($user->userType != 'A') {
                session(['loggedin' => true]);
                return redirect('/dashboard');
            } else {
                session(['adminLoggedin' => true]);
                return redirect('/admin-dashboard');
            }

        } catch (\Exception $e) {
            \Log::error('Login error: ' . $e->getMessage());
            return back()->with(['accessDenied' => 'An error occurred. Please try again later.']);
        }
    }

    // Login with Email

    // public function login(Request $req)
    // {
    //     $req->validate(
    //         [
    //             'email' => 'required',
    //             'password' => 'required'
    //         ]
    //     );

    //     $users = User::where([
    //         ['email', '=', $req['email']],
    //         ['isDeleted', '=', '0']
    //     ])->first();

    //     if ($users) {
    //         if ($users['isActive'] == 0) {
    //             return back()->with('accessDenied', 'Akaunti imefungwa. Lipia Tsh 300 kwa mtoto kwa mwaka. NMB: 52910003854');
    //         } else {
    //             if (Hash::check($req['password'], $users['password'])) {
    //                 session(['userType' => $users['userType']]);
    //                 session(['userRegion' => $users['regionId']]);
    //                 session(['userDistrict' => $users['districtId']]);
    //                 session(['userWard' => $users['wardId']]);
    //                 session(['userSchool' => $users['schoolId']]);
    //                 session(['userName' => $users['userName']]);
    //                 session(['userEmail' => $users['email']]);
    //                 session(['userId' => $users['userId']]);

    //                 if ($req['rememberMe']) {
    //                     Cookie::queue('email', $req['email'], (86400 * 7));
    //                     Cookie::queue('password', $req['password'], (86400 * 7));
    //                 } else {
    //                     Cookie::queue('email', "", (86400 * 7));
    //                     Cookie::queue('password', "", (86400 * 7));
    //                 }

    //                 if ($users['userType'] != 'A') {
    //                     session(['loggedin' => true]);
    //                     return redirect('/dashboard');
    //                 } else {
    //                     session(['adminLoggedin' => true]);
    //                     return redirect('/admin-dashboard');
    //                 }
    //             } else {
    //                 return back()->with('accessDenied', 'Invalid Credential(s)');
    //             }
    //         }
    //     } else {
    //         return back()->with('accessDenied', 'Invalid Credential(s)');
    //     }
    // }

    public function adminDashboard()
    {
        if (Session::get('adminLoggedin') == true) {
            $classId = 1;
            $regionId = 1;
            $districtId = 1;
            $examId = 1;
            $startDate = date('Y-m-d', strtotime('' . date('Y') . '-' . date('m') . '-01'));
            $endDate = date('Y-m-d');

            $classes = Grades::select('gradeId', 'gradeName')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0']
            ])->get();

            $exams = Exams::select('examId', 'examName')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0']
            ])->get();

            $regions = Regions::select('regionId', 'regionName', 'regionCode')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0']
            ])->orderBy('regionName', 'asc')->get();

            $districts = Districts::select('districtId', 'districtName', 'districtCode')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0']
            ])->orderBy('districtName', 'asc')->get();

            $dates = Marks::select('examDate')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0']
            ])->orderBy('examDate', 'desc')->distinct()->pluck('examDate');

            $rank = Ranks::select('rankRangeMin', 'rankRangeMax')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0']
            ])->orderBy('rankName', 'asc')->get();

            $maleAveargeMarks = Marks::select('average')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0'],
                ['gender', '=', 'M'],
                ['classId', '=', $classId],
                ['regionId', '=', $regionId],
                ['districtId', '=', $districtId],
                ['examId', '=', $examId]
            ])->whereBetween('examDate', [$startDate, $endDate])->get();

            $femaleAveargeMarks = Marks::select('average')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0'],
                ['gender', '=', 'F'],
                ['classId', '=', $classId],
                ['regionId', '=', $regionId],
                ['districtId', '=', $districtId],
                ['examId', '=', $examId]
            ])->whereBetween('examDate', [$startDate, $endDate])->get();

            $maleRanks = [0, 0, 0, 0, 0];
            $femaleRanks = [0, 0, 0, 0, 0];

            foreach ($maleAveargeMarks as $average) {
                if ($average['average'] != 0) {
                    if ($rank[0]['rankRangeMin'] < $average['average'] && $rank[0]['rankRangeMax'] >= $average['average']) {
                        $maleRanks[0] = $maleRanks[0] + 1;
                    } else if ($rank[1]['rankRangeMin'] < $average['average'] && $rank[1]['rankRangeMax'] >= $average['average']) {
                        $maleRanks[1] = $maleRanks[1] + 1;
                    } else if ($rank[2]['rankRangeMin'] < $average['average'] && $rank[2]['rankRangeMax'] >= $average['average']) {
                        $maleRanks[2] = $maleRanks[2] + 1;
                    } else if ($rank[3]['rankRangeMin'] < $average['average'] && $rank[3]['rankRangeMax'] >= $average['average']) {
                        $maleRanks[3] = $maleRanks[3] + 1;
                    } else {
                        $maleRanks[4] = $maleRanks[4] + 1;
                    }
                }
            }

            foreach ($femaleAveargeMarks as $average) {
                if ($average['average'] != 0) {
                    if ($rank[0]['rankRangeMin'] < $average['average'] && $rank[0]['rankRangeMax'] >= $average['average']) {
                        $femaleRanks[0] = $femaleRanks[0] + 1;
                    } else if ($rank[1]['rankRangeMin'] < $average['average'] && $rank[1]['rankRangeMax'] >= $average['average']) {
                        $femaleRanks[1] = $femaleRanks[1] + 1;
                    } else if ($rank[2]['rankRangeMin'] < $average['average'] && $rank[2]['rankRangeMax'] >= $average['average']) {
                        $femaleRanks[2] = $femaleRanks[2] + 1;
                    } else if ($rank[3]['rankRangeMin'] < $average['average'] && $rank[3]['rankRangeMax'] >= $average['average']) {
                        $femaleRanks[3] = $femaleRanks[3] + 1;
                    } else {
                        $femaleRanks[4] = $femaleRanks[4] + 1;
                    }
                }
            }

            $schoolRanks = Marks::selectRaw('schools.schoolId, schools.schoolName, ROUND(AVG(average), 2) as average')
                ->join('schools', 'schools.schoolId', '=', 'marks.schoolId')->where([
                        ['marks.isActive', '=', '1'],
                        ['marks.isDeleted', '=', '0'],
                        ['marks.classId', '=', $classId],
                        ['marks.regionId', '=', $regionId],
                        ['marks.districtId', '=', $districtId],
                        ['marks.examId', '=', $examId]
                    ])->groupBy('schools.schoolId', 'schools.schoolName')
                ->whereBetween('marks.examDate', [$startDate, $endDate])->orderBy('average', 'desc')
                ->get();

            session(['pageTitle' => "Ubao"]);
            $borderLine = $rank[3]['rankRangeMin'];

            $data = compact('classes', 'exams', 'regions', 'districts', 'dates', 'classId', 'regionId', 'districtId', 'examId', 'startDate', 'endDate', 'maleRanks', 'femaleRanks', 'schoolRanks', 'borderLine');
            return view('admin.dashboard')->with($data);
        } else {
            return redirect('/')->with('accessDenied', 'Session Expired!');
        }
    }

    public function adminDashboardFilter(Request $req)
    {
        if (Session::get('adminLoggedin') == true) {
            $classId = $req['class'];
            $regionId = $req['region'];
            $districtId = $req['district'];
            $examId = $req['exam'];

            $classCondition = ($req['class'] == '') ? ['classId', '!=', null] : ['classId', '=', $classId];
            $regionCondition = ($req['region'] == '') ? ['regionId', '!=', null] : ['regionId', '=', $regionId];
            $districtCondition = ($req['district'] == '') ? ['districtId', '!=', null] : ['districtId', '=', $districtId];
            $examCondition = ($req['exam'] == '') ? ['examId', '!=', null] : ['examId', '=', $examId];
            $startDate = ($req['startDate'] == '') ? date('Y-m-d', strtotime("2023-01-01")) : $req['startDate'];
            $endDate = ($req['endDate'] == '') ? date('Y-m-d') : $req['endDate'];

            $classCondition2 = ($req['class'] == '') ? ['marks.classId', '!=', null] : ['marks.classId', '=', $classId];
            $regionCondition2 = ($req['region'] == '') ? ['marks.regionId', '!=', null] : ['marks.regionId', '=', $regionId];
            $districtCondition2 = ($req['district'] == '') ? ['marks.districtId', '!=', null] : ['marks.districtId', '=', $districtId];
            $examCondition2 = ($req['exam'] == '') ? ['marks.examId', '!=', null] : ['marks.examId', '=', $examId];

            $classes = Grades::select('gradeId', 'gradeName')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0']
            ])->get();

            $exams = Exams::select('examId', 'examName')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0']
            ])->get();

            $regions = Regions::select('regionId', 'regionName', 'regionCode')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0']
            ])->orderBy('regionName', 'asc')->get();

            $districts = Districts::select('districtId', 'districtName', 'districtCode')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0']
            ])->orderBy('districtName', 'asc')->get();

            $dates = Marks::select('examDate')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0']
            ])->orderBy('examDate', 'desc')->distinct()->pluck('examDate');

            $rank = Ranks::select('rankRangeMin', 'rankRangeMax')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0']
            ])->orderBy('rankName', 'asc')->get();

            $maleAveargeMarks = Marks::select('average')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0'],
                ['gender', '=', 'M'],
                $classCondition,
                $regionCondition,
                $districtCondition,
                $examCondition
            ])->whereBetween('examDate', [$startDate, $endDate])->get();

            $femaleAveargeMarks = Marks::select('average')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0'],
                ['gender', '=', 'F'],
                $classCondition,
                $regionCondition,
                $districtCondition,
                $examCondition
            ])->whereBetween('examDate', [$startDate, $endDate])->get();

            $maleRanks = [0, 0, 0, 0, 0];
            $femaleRanks = [0, 0, 0, 0, 0];

            foreach ($maleAveargeMarks as $average) {
                if ($average['average'] != 0) {
                    if ($rank[0]['rankRangeMin'] < $average['average'] && $rank[0]['rankRangeMax'] >= $average['average']) {
                        $maleRanks[0] = $maleRanks[0] + 1;
                    } else if ($rank[1]['rankRangeMin'] < $average['average'] && $rank[1]['rankRangeMax'] >= $average['average']) {
                        $maleRanks[1] = $maleRanks[1] + 1;
                    } else if ($rank[2]['rankRangeMin'] < $average['average'] && $rank[2]['rankRangeMax'] >= $average['average']) {
                        $maleRanks[2] = $maleRanks[2] + 1;
                    } else if ($rank[3]['rankRangeMin'] < $average['average'] && $rank[3]['rankRangeMax'] >= $average['average']) {
                        $maleRanks[3] = $maleRanks[3] + 1;
                    } else {
                        $maleRanks[4] = $maleRanks[4] + 1;
                    }
                }
            }

            foreach ($femaleAveargeMarks as $average) {
                if ($average['average'] != 0) {
                    if ($rank[0]['rankRangeMin'] < $average['average'] && $rank[0]['rankRangeMax'] >= $average['average']) {
                        $femaleRanks[0] = $femaleRanks[0] + 1;
                    } else if ($rank[1]['rankRangeMin'] < $average['average'] && $rank[1]['rankRangeMax'] >= $average['average']) {
                        $femaleRanks[1] = $femaleRanks[1] + 1;
                    } else if ($rank[2]['rankRangeMin'] < $average['average'] && $rank[2]['rankRangeMax'] >= $average['average']) {
                        $femaleRanks[2] = $femaleRanks[2] + 1;
                    } else if ($rank[3]['rankRangeMin'] < $average['average'] && $rank[3]['rankRangeMax'] >= $average['average']) {
                        $femaleRanks[3] = $femaleRanks[3] + 1;
                    } else {
                        $femaleRanks[4] = $femaleRanks[4] + 1;
                    }
                }
            }

            $schoolRanks = Marks::selectRaw('schools.schoolId, schools.schoolName, ROUND(AVG(average), 2) as average')
                ->join('schools', 'schools.schoolId', '=', 'marks.schoolId')->where([
                        ['marks.isActive', '=', '1'],
                        ['marks.isDeleted', '=', '0'],
                        $classCondition2,
                        $regionCondition2,
                        $districtCondition2,
                        $examCondition2
                    ])->whereBetween('marks.examDate', [$startDate, $endDate])->groupBy('schools.schoolId', 'schools.schoolName')
                ->orderBy('average', 'desc')
                ->get();

            session(['pageTitle' => "Ubao"]);

            if ($classId > 4) {
                $borderLine = $rank[2]['rankRangeMin'];
            } else {
                $borderLine = $rank[3]['rankRangeMin'];
            }

            $data = compact('classes', 'exams', 'regions', 'districts', 'dates', 'classId', 'regionId', 'districtId', 'examId', 'startDate', 'endDate', 'maleRanks', 'femaleRanks', 'schoolRanks', 'borderLine');
            return view('admin.dashboard')->with($data);
        } else {
            return redirect('/')->with('accessDenied', 'Session Expired!');
        }
    }

    public function logout()
    {
        session()->flush();
        return redirect('/');
    }

    public function query()
    {
        $marks = Marks::where([
            ['isActive', '=', '1'],
            ['isDeleted', '=', '0']
        ])->get();

        foreach ($marks as $mark) {
            $total = $mark['hisabati'] + $mark['kiswahili'] + $mark['sayansi'] + $mark['english'] + $mark['jamii'] + $mark['maadili'];
            $averageMarks = number_format(($total / 6), 2);

            $mark['total'] = $total;
            $mark['average'] = $averageMarks;
            $mark->save();
        }
    }
}
