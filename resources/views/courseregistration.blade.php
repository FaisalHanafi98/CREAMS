<!-- courseregistration.blade.php -->

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/registrationstyle.css') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo/favicon.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.0.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <title>Activity Registration</title>
</head>

<body>
    <div class="logo">
        <a href="{{ route('home') }}">CREAMS</a>
    </div>
    <div class="box">
        <div class="container">
            <div class="title">
                <span>Activity Registration</span>
            </div>

            <form action="{{ route('courseregistration.submit') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="field">
                    <label for="course_id">Activity ID</label>
                    <input type="text" name="course_id" id="course_id">
                </div>
                <div class="field">
                    <label for="course_type">Activity Type</label>
                    <select name="course_type" id="course_type">
                        <option value="">Select Course Type</option>
                        <option value="Occupational Therapy">Occupational Therapy</option>
                        <option value="Reading">Reading</option>
                        <option value="Speech Therapy">Speech Therapy</option>
                        <option value="Quranic Class">Quranic Class</option>
                        <option value="Independent Living">Independent Living</option>
                    </select>
                </div>

                <div class="field">
                    <label for="teacher_id">Teacher</label>
                    <select name="teacher_id" id="teacher_id">
                        <option value="">Select Teacher</option>
                        @foreach ($teachers as $teacherId => $teacherName)
                            <option value="{{ $teacherId }}">{{ $teacherName }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label for="participant_id">Participant</label>
                    <select name="participant_id" id="participant_id">
                        <option value="">Select Participant</option>
                        @foreach ($trainees as $traineeId => $traineeName)
                            <option value="{{ $traineeId }}">{{ $traineeName }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label for="course_day">Activity Day</label>
                    <select name="course_day" id="course_day">
                        <option value="">Select Course Day</option>
                        <option value="Monday">Monday</option>
                        <option value="Tuesday">Tuesday</option>
                        <option value="Wednesday">Wednesday</option>
                        <option value="Thursday">Thursday</option>
                        <option value="Friday">Friday</option>
                    </select>
                </div>

                <div class="field">
                    <label for="start_time">Activity Start Time</label>
                    <select name="start_time" id="start_time">
                        <option value="">Select Start Time</option>
                        <option value="10:00">10:00</option>
                        <option value="11:00">11:00</option>
                        <option value="12:00">12:00</option>
                        <option value="14:00">14:00</option>
                        <option value="15:00">15:00</option>
                    </select>
                </div>

                <div class="field">
                    <label for="end_time">Activity End Time</label>
                    <select name="end_time" id="end_time">
                        <option value="">Select End Time</option>
                        <option value="11:00">11:00</option>
                        <option value="12:00">12:00</option>
                        <option value="13:00">13:00</option>
                        <option value="15:00">15:00</option>
                        <option value="16:00">16:00</option>
                    </select>
                </div>

                <div class="field">
                    <label for="location_id">Location</label>
                    <select name="location_id" id="location_id">
                        <option value="">Select Location</option>
                        @foreach ($centres as $centreId => $centreName)
                            <option value="{{ $centreId }}">{{ $centreName }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Register</button>
            </form>

        </div>
    </div>
</body>

</html>
