<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Trainee Profile - CREAMS</title>
   <link rel="shortcut icon" href="{{ asset('assets/trainee/images/favicon.png') }}" type="image/x-icon">
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
   <!-- custom css file link  -->
   <link rel="stylesheet" href="{{ asset('assets/trainee/traineeprofile.css') }}">
   <script src="https://kit.fontawesome.com/8f0012bd95.js" crossorigin="anonymous"></script>

</head>
<body>

<header class="header">
   <section class="flex">
      <a href="/admins/dashboard" class="logo">CREAMS</a>
      </section>
</header>
<section class="user-profile">

   <h1 class="heading">Trainee Profile</h1>

   <div class="info">

      <div class="user">
         <img src={{ asset('assets/trainee/images/studprof1.jpg') }} alt="usman-dp">
         <h3>Usman Zulkifli</h3>
        <p>2312055</p>
        <p>Blind</p>
        <p>2nd Year</p>
         <a href="update.html" class="inline-btn">update profile</a>
      </div>
   
      <div class="box-container">
   
         <div class="box">
            <div class="flex">
               <i class="fas fa-book-open"></i>
               <div>
                  <span>Biography</span>
               </div>
            </div>
            <a href="#" class="inline-btn">View Details</a>
         </div>
   
         <div class="box">
            <div class="flex">
               <i class="fa-solid fa-list-check"></i>
               
               <div>
                  <span>Activity</span>
                  
               </div>
            </div>
            @if(Route::has('traineeactivity'))
                <a href="{{ route('traineeactivity') }}" class="inline-btn">View Activity</a>
            @else
                <a href="{{ route('activities.index') }}" class="inline-btn">View Activities</a>
            @endif
         </div>
   
         <div class="box">
            <div class="flex">
               <i class="fas fa-chart-line"></i>
               <div>
                  <span>Progress</span>
                  
               </div>
            </div>
            <a href="#" class="inline-btn">View Report</a>
         </div>
   
      </div>
      
   </div>
    
</section>




</body>
</html>


<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <script src="https://kit.fontawesome.com/8f0012bd95.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="{{ asset('css/traineeprofile.css') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo/favicon.png') }}" type="image/x-icon">
</head>

<body>
    <div class="container">
        <nav>
            <ul>
                <li>
                    <a href="{{ route('accountprofile') }}" class="logo">
                        <img src="{{ asset(Auth::user()->user_avatar) }}">

                        <div class="user-details">
                            <span class="nav-item">{{ Auth::user()->user_first_name }}</span>
                            <span class="nav-item role">{{ Auth::user()->role }}</span>
                        </div>
                    </a>
                </li>
                @php
                    $currentRoute = Request::url();
                @endphp

                <li class="{{ strpos($currentRoute, route('register')) !== false ? 'active' : '' }}">
                    <a href="{{ route('register') }}">
                        <i class="fas fa-exclamation"></i>
                        <span class="nav-item">Register</span>
                    </a>
                </li>
                <li class="{{ strpos($currentRoute, route('teachershome')) !== false ? 'active' : '' }}">
                    <a href="{{ route('teachershome') }}">
                        <i class="fas fa-person-chalkboard"></i>
                        <span class="nav-item">Staff</span>
                    </a>
                </li>
                <li class="{{ strpos($currentRoute, route('traineeshome')) !== false ? 'active' : '' }}">
                    <a href="{{ route('traineeshome') }}">
                        <i class="fas fa-address-card"></i>
                        <span class="nav-item">Trainee</span>
                    </a>
                </li>
                <li class="{{ strpos($currentRoute, route('schedulehomepage')) !== false ? 'active' : '' }}">
                    <a href="{{ route('schedulehomepage') }}">
                        <i class="fas fa-calendar-days"></i>
                        <span class="nav-item">Schedule</span>
                    </a>
                </li>
                <li class="{{ strpos($currentRoute, route('assetmanagementpage')) !== false ? 'active' : '' }}">
                    <a href="{{ route('assetmanagementpage') }}">
                        <i class="fas fa-chair"></i>
                        <span class="nav-item">Asset</span>
                    </a>
                </li>
                <li class="{{ strpos($currentRoute, route('aboutus')) !== false ? 'active' : '' }}">
                    <a href="{{ route('aboutus') }}">
                        <i class="fas fa-info"></i>
                        <span class="nav-item">About</span>
                    </a>
                </li>
                <li>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                    <a href="{{ route('logout') }}" class="logout"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt"></i>
                        <span class="nav-item">Log out</span>
                    </a>
                </li>
            </ul>
        </nav>
        <section class="main">
            <a href="{{ route('home') }}" class="main-top">
                <h1>CREAMS</h1>
            </a>
            
            <span class="small-text">Community-based REhAbilitation Management System</span>

            <div class="container emp-profile">
                <form action="{{ route('updatetraineeprofile', ['id' => $trainee->id]) }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-4">
                            <div class="profile-img">
                              <img src="{{ asset($trainee->trainee_avatar) }}" alt="trainee dp">
                                


                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="profile-head">
                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                @if (session('success'))
                                    <div class="alert alert-success">
                                        {{ session('success') }}
                                    </div>
                                @endif

                                <h5>{{ $trainee->trainee_name }}</h5>
                                <h6>{{ $trainee->condition }}</h6>
                                <button id="update-account-btn" class="btn btn-primary">Update Account</button>
                                <button id="save-profile-btn" class="btn btn-success" style="display: none;">Save
                                    Profile</button>
                                <button id="cancel-profile-btn" class="btn btn-secondary"
                                    style="display: none;">Cancel</button>
                                <ul class="nav nav-tabs" id="myTab" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home"
                                            role="tab" aria-controls="home" aria-selected="true">Biography</a>
                                    </li>

                                </ul>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <input type="submit" class="profile-edit-btn" name="btnSaveProfile" value="Save Profile"
                                style="display: none;">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="profile-work">
                                <p>CLASS</p>
                                <a href="">Website Link</a><br />
                                <a href="">Bootsnipp Profile</a><br />
                                <a href="">Bootply Profile</a>
                            </div>
                        </div>


                        <div class="col-md-8">
                            <div class="tab-content profile-tab" id="myTabContent">
                                <div class="tab-pane fade show active" id="home" role="tabpanel"
                                    aria-labelledby="home-tab">

                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="trainee_avatar">Profile Photo</label>
                                        </div>

                                        <div class="col-md-6">
                                            <input type="file" id="trainee_avatar" name="trainee_avatar" readonly
                                                accept="image/*" onchange="previewProfileImage(event)">
                                            <small>Upload a new avatar image.</small>
                                        </div>

                                    </div>                    

                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="id">Trainee ID</label>
                                        </div>
                                        <div class="col-md-6">
                                            <p>{{ $trainee->id }}</p>
                                        </div>
                                    </div>


                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="trainee_first_name">First Name</label>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="text" id="trainee_first_name" name="trainee_first_name"
                                                value="{{ $trainee->trainee_first_name }}" required readonly>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="trainee_last_name">Last Name</label>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="text" id="trainee_last_name" name="trainee_last_name"
                                                value="{{ $trainee->trainee_last_name }}" required readonly>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="trainee_email">Email</label>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="trainee_email" id="trainee_email" name="trainee_email"
                                                value="{{ $trainee->trainee_email }}" readonly required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="trainee_date_of_birth">Date of Birth</label>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="date" id="trainee_date_of_birth"
                                                name="trainee_date_of_birth"
                                                value="{{ $trainee->trainee_date_of_birth }}" readonly>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="centre_name">Centre</label>
                                        </div>
                                        <div class="col-md-6">
                                            <select class="form-control" id="centre_name" name="centre_name"
                                                readonly>
                                                <option value="">Select Condition</option>
                                                <option value="Gombak"
                                                    {{ old('centre_name') === 'Gombak' ? 'selected' : '' }}>
                                                    Gombak</option>
                                                <option value="Kuantan"
                                                    {{ old('centre_name') === 'Kuantan' ? 'selected' : '' }}>
                                                    Kuantan</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="trainee_condition">Condition</label>
                                        </div>
                                        <div class="col-md-6">
                                            <select class="form-control" id="trainee_condition"
                                                name="trainee_condition" readonly>
                                                <option value="">Select Condition</option>
                                                <option value="Cerebral Palsy"
                                                    {{ old('trainee_condition') === 'Cerebral Palsy' ? 'selected' : '' }}>
                                                    Cerebral Palsy</option>
                                                <option value="Autism Spectrum Disorder (ASD)"
                                                    {{ old('trainee_condition') === 'Autism Spectrum Disorder (ASD)' ? 'selected' : '' }}>
                                                    Autism Spectrum Disorder (ASD)</option>
                                                <option value="Down Syndrome"
                                                    {{ old('trainee_condition') === 'Down Syndrome' ? 'selected' : '' }}>
                                                    Down Syndrome</option>
                                                <option value="Hearing Impairment"
                                                    {{ old('trainee_condition') === 'Hearing Impairment' ? 'selected' : '' }}>
                                                    Hearing Impairment</option>
                                                <option value="Visual Impairment"
                                                    {{ old('trainee_condition') === 'Visual Impairment' ? 'selected' : '' }}>
                                                    Visual Impairment</option>
                                                <option value="Intellectual Disabilities"
                                                    {{ old('trainee_condition') === 'Intellectual Disabilities' ? 'selected' : '' }}>
                                                    Intellectual Disabilities</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </form>

            </div>

            <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.0.2/js/bootstrap.min.js"></script>
            <script>
                const updateButton = document.getElementById('update-account-btn');
                const saveButton = document.getElementById('save-profile-btn');
                const cancelButton = document.getElementById('cancel-profile-btn');
                const formInputs = document.querySelectorAll('.profile-tab input, .profile-tab textarea');

                updateButton.addEventListener('click', function(e) {
                    e.preventDefault();

                    this.style.display = 'none';
                    saveButton.style.display = 'inline-block';
                    cancelButton.style.display = 'inline-block';

                    formInputs.forEach(input => {
                        input.removeAttribute('readonly');
                        input.disabled = false; // Enable the input field
                    });
                });

                cancelButton.addEventListener('click', function(e) {
                    e.preventDefault();

                    updateButton.style.display = 'inline-block';
                    saveButton.style.display = 'none';
                    cancelButton.style.display = 'none';

                    formInputs.forEach(input => {
                        input.setAttribute('readonly', true);
                        input.disabled = true; // Disable the input field
                    });
                });
            </script>

</body>

</html
 
