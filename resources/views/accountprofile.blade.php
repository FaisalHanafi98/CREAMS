<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Account Profile - CREAMS</title>
   <link rel="shortcut icon" href="{{ asset('assets/account/images/favicon.png') }}" type="image/x-icon">
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
   <!-- custom css file link  -->
   <link rel="stylesheet" href="{{ asset('assets/account/accountprofilestyle.css') }}">
   <script src="https://kit.fontawesome.com/8f0012bd95.js" crossorigin="anonymous"></script>

</head>
<body>

<header class="header">
   <section class="flex">
      <a href="/admins/dashboard" class="logo">CREAMS</a>
      </section>
</header>
<section class="user-profile">

   <h1 class="heading">User Profile</h1>

   <div class="info">

      <div class="user">
         <img src={{ asset('assets/account/images/dashboard1.jpg') }} alt="usman-dp">
         <h3>Faisal Hanafi</h3>
        <p>831340</p>
        <p>Speech Therapist</p>
        <p>asbourne1998@gmail.com</p>
         <a href="/updateaccount" class="inline-btn">update profile</a>
      </div>
      
   </div>
    
</section>




</body>
</html>


<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - CREAMS</title>
    <link rel="stylesheet" href="{{ asset('css/accountprofilestyle.css') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo/favicon.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.0.2/css/bootstrap.min.css">
</head>

<body>
    <div class="header">
        <a href="{{ route('home') }}" class="logo">CREAMS</a>
    </div>
    

    <div class="super-container">
        <div class="container">
            <div class="user-profile">
                <h1 class="title">User Profile</h1>
                <div class="avatar">
                    <img src={{ $user->user_avatar }} alt="User Avatar">
                </div>
                <div class="info">
                    <h3>{{ $user->user_first_name }} {{ $user->user_last_name }}</h3>
                    <p>Email: {{ $user->email }}</p>
                    <p>Role: {{ $user->role }}</p>
                </div>
            </div>
            <div class="update-profile">
                <h1 class="title">Update Credentials</h1>
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
                <form method="POST" action="{{ route('profileupdate') }}" class="form"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                            readonly required>
                        @error('email')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" readonly required>
                        @error('password')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation">Confirm Password:</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" readonly
                            required>
                    </div>
                    <div class="form-group">
                        <label for="user_avatar">Profile Image</label>
                        <input type="file" name="user_avatar" id="user_avatar" value="Your Photo">

                        @error('user_avatar')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <input type="submit" id="update-button" value="Update Credentials">
                        <input type="submit" id="save-button" value="Save" style="display: none">
                        <button type="button" id="cancel-button" style="display: none">Cancel</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
    <script>
        const updateButton = document.getElementById('update-button');
        const saveButton = document.getElementById('save-button');
        const cancelButton = document.getElementById('cancel-button');
        const formInputs = Array.from(document.querySelectorAll('.form-group input:not([type=submit])'));

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
            this.style.display = 'none';

            formInputs.forEach(input => {
                input.setAttribute('readonly', true);
                input.disabled = true; // Disable the input field
            });
        });
    </script>

</body>

</html>
 
