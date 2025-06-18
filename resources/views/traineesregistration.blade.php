<!DOCTYPE html>
<!-- Created By CodingLab - www.codinglabweb.com -->
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <title>Trainee Registration | CodingLab</title>
    <link rel="stylesheet" href="{{ asset('css/traineeregistrationstyle.css') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="{{ asset('images/logo/favicon.png') }}" type="image/x-icon">
    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css" />
</head>

<body>
    <div class="logo">
        <a href="{{ route('home') }}">CREAMS</a>
    </div>
    <div class="container">
        <div class="content">
            <div class="registration-title">Registration</div>
            <div class="underline"></div>
            <div class="image-preview" id="imagePreview"></div>
            <form action="{{ route('traineesregistrationstore') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="user-details">
                    <div class="input-box">
                        <span class="details">First Name</span>
                        <input type="text" name="trainee_first_name" placeholder="Enter your first name" required>
                    </div>
                    <div class="input-box">
                        <span class="details">Last Name</span>
                        <input type="text" name="trainee_last_name" placeholder="Enter your last name" required>
                    </div>
                    <div class="input-box">
                        <span class="details">Email</span>
                        <input type="email" name="trainee_email" placeholder="Enter your email" required>
                    </div>
                    <div class="input-box">
                        <span class="details">Phone Number</span>
                        <input type="text" name="trainee_phone_number" placeholder="Enter your phone number"
                            required>
                    </div>
                    <div class="input-box">
                        <span class="details">Date of Birth</span>
                        <input type="date" name="trainee_date_of_birth" required>
                    </div>
                    <div class="input-box">
                        <span class="details">Avatar</span>
                        <input type="file" name="trainee_avatar" id="avatar" accept="image/*"
                            onchange="previewImage(event)">
                    </div>
                    <div class="input-box">
                        <span class="details">Class</span>
                        <select name="centre_name" required>
                            <option value="">Select a class</option>
                            <option value="Gombak">Gombak</option>
                            <option value="Kuantan">Kuantan</option>
                        </select>
                    </div>
                    <div class="input-box">
                        <span class="details">Condition</span>
                        <select name="trainee_condition" required>
                            <option value="">Select a condition</option>
                            <option value="Cerebral Palsy">Cerebral Palsy</option>
                            <option value="Autism Spectrum Disorder (ASD)">Autism Spectrum Disorder (ASD)</option>
                            <option value="Down Syndrome">Down Syndrome</option>
                            <option value="Hearing Impairment">Hearing Impairment</option>
                            <option value="Visual Impairment">Visual Impairment</option>
                            <option value="Intellectual Disabilities">Intellectual Disabilities</option>
                        </select>
                    </div>
                </div>
                <div class="button">
                    <input type="submit" value="Register">
                </div>
            </form>
        </div>
    </div>

    <script>
        function previewImage(event) {
            var reader = new FileReader();
            var imagePreview = document.getElementById("imagePreview");
            imagePreview.innerHTML = ""; // Clear previous preview

            reader.onload = function() {
                var image = document.createElement("img");
                image.src = reader.result;
                image.style.maxWidth = "200px";
                image.style.maxHeight = "200px";
                image.style.borderRadius = "50%";
                imagePreview.appendChild(image);
            };

            if (event.target.files && event.target.files[0]) {
                reader.readAsDataURL(event.target.files[0]);
            }
        }
    </script>
</body>

</html>
