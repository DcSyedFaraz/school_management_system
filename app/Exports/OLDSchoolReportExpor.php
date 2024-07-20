<?php

namespace App\Exports;

use App\Models\Marks;
use App\Models\Schools;
use App\Models\Regions;
use App\Models\Districts;
use App\Models\Wards;
use App\Models\Ranks;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class SchoolReportExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths
{
    protected $examId;
    protected $classId;
    protected $regionId;
    protected $districtId;
    protected $wardId;
    protected $startDate;
    protected $endDate;

    public function __construct($examId, $classId, $regionId, $districtId, $wardId, $startDate, $endDate){
        $this->examId = $examId;
        $this->classId = $classId;
        $this->regionId = $regionId;
        $this->districtId = $districtId;
        $this->wardId = $wardId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->rank=Ranks::select('rankName','rankRangeMin','rankRangeMax')->where([
            ['isActive','=','1'],
            ['isDeleted','=','0']
        ])->orderBy('rankName','asc')->get();
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $examCondition=($this->examId=='')?['examId','!=',null]:['examId','=',$this->examId];
        $regionCondition=($this->regionId=='')?['regionId','!=',null]:['regionId','=',$this->regionId];
        $districtCondition=($this->districtId=='')?['districtId','!=',null]:['districtId','=',$this->districtId];
        $wardCondition=($this->wardId=='')?['wardId','!=',null]:['wardId','=',$this->wardId];

        $marks = Marks::selectRaw('schoolId, regionId, districtId, wardId,
            ROUND((ROUND(SUM(hisabati), 2) + ROUND(SUM(kiswahili), 2) + ROUND(SUM(sayansi), 2) + ROUND(SUM(english), 2) + ROUND(SUM(jamii), 2) + ROUND(SUM(maadili), 2)), 2) as averageMarks')
        ->where([
            ['isActive','=','1'],
            ['isDeleted','=','0'],
            ['classId','=',$this->classId],
            $examCondition,
            $regionCondition,
            $districtCondition,
            $wardCondition
        ])
        ->whereBetween('examDate', [$this->startDate, $this->endDate])
        ->groupBy('schoolId','regionId','districtId','wardId')
        ->orderBy('averageMarks', 'desc')
        ->get();

        return $marks;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 20,
            'C' => 20,
            'D' => 20,
            'E' => 20,
            'F' => 20,
            'G' => 20,
            'H' => 20,
            'I' => 20,
            'J' => 20,
            'K' => 20,
            'L' => 20,
            'M' => 20,
            'N' => 20,
            'O' => 20,
            'P' => 20,
            'Q' => 20,
            'R' => 20,
            'S' => 20,
            'T' => 20,
            'U' => 20,
            'V' => 20,
            'W' => 20,
            'X' => 20,
            'Y' => 20,
            'Z' => 20,
            'AA' => 20,
            'AB' => 20,
            'AC' => 20,
            'AD' => 20,
            'AE' => 20,
            'AF' => 20,
            'AG' => 20,
            'AH' => 20,
            'AI' => 20,
            'AJ' => 20,
            'AK' => 20,
            'AL' => 20,
            'AM' => 20,
            'AN' => 20,
            'AO' => 20,
            'AP' => 20,
            'AQ' => 20,
        ];
    }

    public function headings(): array
    {
        $headings = [
            'Sr.No',
            'Mkoa',
            'Wilaya',
            'Kata',
            'Shule',
            'Wav('.(date('Y')-$this->classId).')',
            'Was('.(date('Y')-$this->classId).')',
            'Jml',
            'Wav(Waliosaijliwa)',
            'Was(Waliosaijliwa)',
            'Jml',
            'Wav(WaliofanyaMtihani)',
            'Was(WaliofanyaMtihani)',
            'Jml',
            'Percent',
            'Wav(Waliofanya)',
            'Was(Waliofanya)',
            'Jml',
            'Percent',
            'Wav(Grade A)',
            'Was(Grade A)',
            'Jml',
            'Wav(Grade B)',
            'Was(Grade B)',
            'Jml',
            'Wav(Grade C)',
            'Was(Grade C)',
            'Jml'
        ];
    
        if ($this->classId > 4) {
            $headings = array_merge($headings, [
                'Wav(Grade A-C)',
                'Was(Grade A-C)',
                'Jml',
                'Percent',
                'Wav(Grade D)',
                'Was(Grade D)',
                'Jml',
                'Wav(Grade E)',
                'Was(Grade E)',
                'Jml',
                'Wav(Grade D-E)',
                'Was(Grade D-E)',
                'Jml',
                'Percent',
            ]);
        } else {
            $headings = array_merge($headings, [
                'Wav(Grade D)',
                'Was(Grade D)',
                'Jml',
                'Wav(Grade A-D)',
                'Was(Grade A-D)',
                'Jml',
                'Percent',
                'Wav(Grade E)',
                'Was(Grade E)',
                'Jml',
                'Wav(Grade E)',
                'Was(Grade E)',
                'Jml',
                'Percent',
            ]);
        }
    
        $headings = array_merge($headings, [
            'Wastani ya ufaulu',
            'Daraja'
        ]);
    
        return $headings;
    }

    public function map($marks): array
    {
        $schoolData=Schools::find($marks['schoolId']);
        $schoolName=($schoolData)?$schoolData['schoolName']:"Not Found";

        $regionData=Regions::find($marks['regionId']);
        $regionName=($regionData)?$regionData['regionName']:'Not Found';

        $districtData=Districts::find($marks['districtId']);
        $districtName=($districtData)?$districtData['districtName']:'Not Found';

        $wardData=Wards::find($marks['wardId']);
        $wardName=($wardData)?$wardData['wardName']:'Not Found';

        $examCondition=($this->examId=='')?['examId','!=',null]:['examId','=',$this->examId];
        $regionCondition=($this->regionId=='')?['regionId','!=',null]:['regionId','=',$this->regionId];
        $districtCondition=($this->districtId=='')?['districtId','!=',null]:['districtId','=',$this->districtId];
        $wardCondition=($this->wardId=='')?['wardId','!=',null]:['wardId','=',$this->wardId];

        $fgMale=Marks::where([
            ['isActive','=','1'],
            ['isDeleted','=','0'],
            ['classId','=',$this->classId],
            ['firstGrade','=','1'],
            ['gender','=','M'],
            ['schoolId','=',$marks['schoolId']],
            $examCondition,
            $regionCondition,
            $districtCondition,
            $wardCondition
        ])->whereBetween('examDate', [$this->startDate, $this->endDate])->count();

        $fgFemale=Marks::where([
            ['isActive','=','1'],
            ['isDeleted','=','0'],
            ['classId','=',$this->classId],
            ['firstGrade','=','1'],
            ['gender','=','F'],
            ['schoolId','=',$marks['schoolId']],
            $examCondition,
            $regionCondition,
            $districtCondition,
            $wardCondition
        ])->whereBetween('examDate', [$this->startDate, $this->endDate])->count();

        $avgMarks=Marks::selectRaw('gender, ROUND((hisabati + kiswahili + sayansi + english + jamii + maadili) / 6, 2) as averageMarks')->where([
            ['isActive','=','1'],
            ['isDeleted','=','0'],
            ['classId','=',$this->classId],
            ['schoolId','=',$marks['schoolId']],
            $examCondition,
            $regionCondition,
            $districtCondition,
            $wardCondition
        ])->whereBetween('examDate', [$this->startDate, $this->endDate])->get();

        $totalMale=0;
        $totalFemale=0;
        $aGradeMale=0;
        $aGradeFemale=0;
        $bGradeMale=0;
        $bGradeFemale=0;
        $cGradeMale=0;
        $cGradeFemale=0;
        $dGradeMale=0;
        $dGradeFemale=0;
        $eGradeMale=0;
        $eGradeFemale=0;
        $maleAbsent=0;
        $femaleAbsent=0;

        foreach ($avgMarks as $avg) {
            ($avg['gender']=='M')?$totalMale++:$totalFemale++;

            if($avg['averageMarks']==0){
                if($avg['gender']=='M'){
                    $maleAbsent++;
                }
                else{
                    $femaleAbsent++;
                }
            }
            else{
                if($this->assignGrade($avg['averageMarks'])=='A'){
                    ($avg['gender']=='M')?$aGradeMale++:$aGradeFemale++;
                }
                else if($this->assignGrade($avg['averageMarks'])=='B'){
                    ($avg['gender']=='M')?$bGradeMale++:$bGradeFemale++;
                }
                else if($this->assignGrade($avg['averageMarks'])=='C'){
                    ($avg['gender']=='M')?$cGradeMale++:$cGradeFemale++;
                }
                else if($this->assignGrade($avg['averageMarks'])=='D'){
                    ($avg['gender']=='M')?$dGradeMale++:$dGradeFemale++;
                }
                else{
                    ($avg['gender']=='M')?$eGradeMale++:$eGradeFemale++;
                }
            }
        }

        if ($this->classId>4) {
            $totalPassMale=$aGradeMale+$bGradeMale+$cGradeMale;
            $totalPassFemale=$aGradeFemale+$bGradeFemale+$cGradeFemale;
            $totalFailMale=$dGradeMale+$eGradeMale;
            $totalFailFemale=$dGradeFemale+$eGradeFemale;
            $totalPass=$totalPassMale+$totalPassFemale;
            $totalFail=$totalFailMale+$totalFailFemale;
        } else {
            $totalPassMale=$aGradeMale+$bGradeMale+$cGradeMale+$dGradeMale;
            $totalPassFemale=$aGradeFemale+$bGradeFemale+$cGradeFemale+$dGradeFemale;
            $totalFailMale=$eGradeMale;
            $totalFailFemale=$eGradeFemale;
            $totalPass=$totalPassMale+$totalPassFemale;
            $totalFail=$totalFailMale+$totalFailFemale; 
        }

        static $serialNumber = 0;
        $serialNumber++;

        if($this->classId>4){
            return [
                $serialNumber,
                $regionName,
                $districtName,
                $wardName,
                $schoolName,
                ($fgMale==0)?"0":$fgMale,
                ($fgFemale==0)?"0":$fgFemale,
                (($fgMale+$fgFemale)==0)?"0":($fgMale+$fgFemale),
                ($totalMale==0)?"0":$totalMale,
                ($totalFemale==0)?"0":$totalFemale,
                (($totalMale+$totalFemale)==0)?"0":($totalMale+$totalFemale),
                (($totalPassMale+$totalFailMale)==0)?"0":($totalPassMale+$totalFailMale),
                (($totalPassFemale+$totalFailFemale)==0)?"0":($totalPassFemale+$totalFailFemale),
                (($totalPassMale+$totalPassFemale+$totalFailMale+$totalFailFemale)==0)?"0":($totalPassMale+$totalPassFemale+$totalFailMale+$totalFailFemale),
                number_format(((($totalPassMale+$totalPassFemale+$totalFailMale+$totalFailFemale)/($totalMale+$totalFemale))*100), 2),
                ($maleAbsent==0)?"0":$maleAbsent,
                ($femaleAbsent==0)?"0":$femaleAbsent,
                (($maleAbsent+$femaleAbsent)==0)?"0":($maleAbsent+$femaleAbsent),
                number_format(((($maleAbsent+$femaleAbsent)/($totalMale+$totalFemale))*100), 2),
                ($aGradeMale==0)?"0":$aGradeMale,
                ($aGradeFemale==0)?"0":$aGradeFemale,
                (($aGradeMale+$aGradeFemale)==0)?"0":($aGradeMale+$aGradeFemale),
                ($bGradeMale==0)?"0":$bGradeMale,
                ($bGradeFemale==0)?"0":$bGradeFemale,
                (($bGradeMale+$bGradeFemale)==0)?"0":($bGradeMale+$bGradeFemale),
                ($cGradeMale==0)?"0":$cGradeMale,
                ($cGradeFemale==0)?"0":$cGradeFemale,
                (($cGradeMale+$cGradeFemale)==0)?"0":($cGradeMale+$cGradeFemale),
                (($aGradeMale+$bGradeMale+$cGradeMale)==0)?"0":($aGradeMale+$bGradeMale+$cGradeMale),
                (($aGradeFemale+$bGradeFemale+$cGradeFemale)=="0")?"0":($aGradeFemale+$bGradeFemale+$cGradeFemale),
                (($aGradeMale+$bGradeMale+$cGradeMale+$aGradeFemale+$bGradeFemale+$cGradeFemale)==0)?"0":($aGradeMale+$bGradeMale+$cGradeMale+$aGradeFemale+$bGradeFemale+$cGradeFemale),
                number_format(((($aGradeMale+$bGradeMale+$cGradeMale+$aGradeFemale+$bGradeFemale+$cGradeFemale)/($totalMale+$totalFemale))*100), 2),
                ($dGradeMale==0)?"0":$dGradeMale,
                ($dGradeFemale==0)?"0":$dGradeFemale,
                (($dGradeMale+$dGradeFemale)==0)?"0":($dGradeMale+$dGradeFemale),
                ($eGradeMale==0)?"0":$eGradeMale,
                ($eGradeFemale==0)?"0":$eGradeFemale,
                (($eGradeMale+$eGradeFemale)==0)?"0":($eGradeMale+$eGradeFemale),
                (($eGradeMale+$dGradeMale)==0)?"0":($eGradeMale+$dGradeMale),
                (($eGradeFemale+$dGradeFemale)==0)?"0":($eGradeFemale+$dGradeFemale),
                (($eGradeMale+$dGradeMale+$eGradeFemale+$dGradeFemale)==0)?"0":($eGradeMale+$dGradeMale+$eGradeFemale+$dGradeFemale),
                number_format(((($eGradeMale+$dGradeMale+$eGradeFemale+$dGradeFemale)/($totalMale+$totalFemale))*100), 2),
                ($marks['averageMarks']==0)?"0":number_format(($marks['averageMarks']/(count($avgMarks)-$maleAbsent-$femaleAbsent)), 5),
                $this->assignGrade(number_format(($marks['averageMarks']/(count($avgMarks)-$maleAbsent-$femaleAbsent)), 5)/6)
            ];
        }
        else{
            return [
                $serialNumber,
                $regionName,
                $districtName,
                $wardName,
                $schoolName,
                ($fgMale==0)?"0":$fgMale,
                ($fgFemale==0)?"0":$fgFemale,
                (($fgMale+$fgFemale)==0)?"0":($fgMale+$fgFemale),
                ($totalMale==0)?"0":$totalMale,
                ($totalFemale==0)?"0":$totalFemale,
                (($totalMale+$totalFemale)==0)?"0":($totalMale+$totalFemale),
                ($totalPassMale==0)?"0":$totalPassMale,
                ($totalPassFemale==0)?"0":$totalPassFemale,
                (($totalPassMale+$totalPassFemale)==0)?"0":($totalPassMale+$totalPassFemale),
                number_format(((($totalPassMale+$totalPassFemale)/($totalMale+$totalFemale))*100), 2),
                ($maleAbsent==0)?"0":$maleAbsent,
                ($femaleAbsent==0)?"0":$femaleAbsent,
                (($maleAbsent+$femaleAbsent)==0)?"0":($maleAbsent+$femaleAbsent),
                number_format(((($maleAbsent+$femaleAbsent)/($totalMale+$totalFemale))*100), 2),
                ($aGradeMale==0)?"0":$aGradeMale,
                ($aGradeFemale==0)?"0":$aGradeFemale,
                (($aGradeMale+$aGradeFemale)==0)?"0":($aGradeMale+$aGradeFemale),
                ($bGradeMale==0)?"0":$bGradeMale,
                ($bGradeFemale==0)?"0":$bGradeFemale,
                (($bGradeMale+$bGradeFemale)==0)?"0":($bGradeMale+$bGradeFemale),
                ($cGradeMale==0)?"0":$cGradeMale,
                ($cGradeFemale==0)?"0":$cGradeFemale,
                (($cGradeMale+$cGradeFemale)==0)?"0":($cGradeMale+$cGradeFemale),
                ($dGradeMale==0)?"0":$dGradeMale,
                ($dGradeFemale==0)?"0":$dGradeFemale,
                (($dGradeMale+$dGradeFemale)==0)?"0":($dGradeMale+$dGradeFemale),
                (($aGradeMale+$bGradeMale+$cGradeMale+$dGradeMale)==0)?"0":($aGradeMale+$bGradeMale+$cGradeMale+$dGradeMale),
                (($aGradeFemale+$bGradeFemale+$cGradeFemale+$dGradeFemale)=="0")?"0":($aGradeFemale+$bGradeFemale+$cGradeFemale+$dGradeFemale),
                (($aGradeMale+$bGradeMale+$cGradeMale+$dGradeMale+$aGradeFemale+$bGradeFemale+$cGradeFemale+$dGradeFemale)==0)?"0":($aGradeMale+$bGradeMale+$cGradeMale+$dGradeMale+$aGradeFemale+$bGradeFemale+$cGradeFemale+$dGradeFemale),
                number_format(((($aGradeMale+$bGradeMale+$cGradeMale+$dGradeMale+$aGradeFemale+$bGradeFemale+$cGradeFemale+$dGradeFemale)/($totalMale+$totalFemale))*100), 2),
                ($eGradeMale==0)?"0":$eGradeMale,
                ($eGradeFemale==0)?"0":$eGradeFemale,
                (($eGradeMale+$eGradeFemale)==0)?"0":($eGradeMale+$eGradeFemale),
                (($eGradeMale)==0)?"0":($eGradeMale),
                (($eGradeFemale)==0)?"0":($eGradeFemale),
                (($eGradeMale+$eGradeFemale)==0)?"0":($eGradeMale+$eGradeFemale),
                number_format(((($eGradeMale+$eGradeFemale)/($totalMale+$totalFemale))*100), 2),
                ($marks['averageMarks']==0)?"0":number_format(($marks['averageMarks']/(count($avgMarks)-$maleAbsent-$femaleAbsent)), 5),
                $this->assignGrade(number_format(($marks['averageMarks']/(count($avgMarks)-$maleAbsent-$femaleAbsent)), 5)/6)
            ];
        }
    }

    function assignGrade($marks){
        if($this->rank){
            if($this->rank[0]['rankRangeMin']<$marks && $this->rank[0]['rankRangeMax']>=$marks){
                return $this->rank[0]['rankName'];
            }
            else if($this->rank[1]['rankRangeMin']<$marks && $this->rank[1]['rankRangeMax']>=$marks){
                return $this->rank[1]['rankName'];
            }
            else if($this->rank[2]['rankRangeMin']<$marks && $this->rank[2]['rankRangeMax']>=$marks){
                return $this->rank[2]['rankName'];
            }
            else if($this->rank[3]['rankRangeMin']<$marks && $this->rank[3]['rankRangeMax']>=$marks){
                return $this->rank[3]['rankName'];
            }
            else{
                return $this->rank[4]['rankName'];
            }
        }
        else{
            return "Null";
        }
    } 
}
