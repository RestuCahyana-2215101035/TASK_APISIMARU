<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\ClassSchedule;
use App\Utilities\TimeMappings;
use App\Http\Controllers\Controller;
use App\Http\Resources\ClassScheduleResource;

class ClassScheduleController extends Controller
{
    public function index()
    {
        $schedules = ClassSchedule::all();

        return new ClassScheduleResource(true, "success get schedule data", $schedules);
    }

    public function store(Request $request)
    {
        $this->validate($request, ClassSchedule::$rules);

        $isLabAvailable = ClassSchedule::isLabAvailable($request->lab_id, $request->day, $request->start_time, $request->end_time)->count() == 0;

        if ($isLabAvailable) {
            $data = [
                "lab_id" => $request->lab_id,
                "day" => $request->day,
                "start_time" => TimeMappings::getMapping($request->start_time)[0],
                "end_time" => TimeMappings::getMapping($request->end_time)[1],
                "subject" => $request->subject,
                "lecturer" => $request->lecturer,
                "class" => $request->class
            ];

            ClassSchedule::create($data);
            return new ClassScheduleResource(true, "schedule success to addded", $data);
        } else {
            return response()->json([
                'message' => 'Lab is not available',
            ], 409);
        }
    }

    public function update(Request $request, ClassSchedule $classSchedule)
    {
        $this->validate($request, ClassSchedule::$rules);

        $newLab = $request->lab_id;
        $newDay = $request->day;
        $newStartTime = TimeMappings::getMapping($request->start_time)[0];
        $newEndTime = TimeMappings::getMapping($request->end_time)[1];

        if (
            $newLab != $classSchedule->lab_id ||
            $newDay != $classSchedule->day ||
            $newStartTime != $classSchedule->start_time ||
            $newEndTime != $classSchedule->end_time
        ) {
            $isLabAvailable = ClassSchedule::isLabAvailable($newLab, $newDay, $request->start_time, $request->end_time)->count() == 0;
            if ($isLabAvailable) {
                $data = [
                    "lab_id" => $newLab,
                    "day" => $newDay,
                    "start_time" => $newStartTime,
                    "end_time" => $newEndTime,
                    "subject" => $request->subject,
                    "lecturer" => $request->lecturer,
                    "class" => $request->class
                ];

                $classSchedule->update($data);
                return new ClassScheduleResource(true, "Schedule success to Edited", $data);
            } else {
                return response()->json([
                    'message' => 'Lab is not available',
                ], 409);
            }
        } else {
            $data = $classSchedule->update($request->only(['subject', 'lecturer', 'class']));
            return new ClassScheduleResource(true, "Schedule Success to Edited", $data);
        }
    }

    public function destroy($id)
    {
        $data = ClassSchedule::find($id);
        $data->delete();
        return new ClassScheduleResource(true, "Schedule success to Deleted", $data);
    }


}
