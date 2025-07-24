<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Teachers - CREAMS</title>
    <link rel="shortcut icon" href="{{ asset('images/logo/favicon.png') }}" type="image/x-icon">
    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css" />
    <link rel="stylesheet" href="{{ asset('css/teachershomestyle.css') }}">
    <!-- custom css file link  -->
    <style>
        .teachers-subsection {
            display: none;
        }

        .teachers-subsection.active {
            display: block;
        }

        .navigation-buttons {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
        }
    </style>
</head>

<body>


    <header class="header">
        <section class="flex">
            <a href="/" class="logo">CREAMS</a>
        </section>
    </header>



    <section class="teachers">
        <h1 class="heading">Staff</h1>

        @php
            // Get all distinct activity_1 values
            $activity_1Values = collect($users)
                ->pluck('user_activity_1')
                ->unique();
        @endphp
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

        <form action="" method="post" class="search-tutor">
            <input type="text" id="searchBox" placeholder="Search Teacher" required maxlength="100" />

        </form>

        <div class="teachers-section">
            @foreach ($activity_1Values as $index => $activity_1)
                <div class="teachers-subsection{{ $index === 0 ? ' active' : '' }}">
                    <h2>{{ $activity_1 }}</h2>
                    <div class="box-container">

                        <div class="box offer">
                            <h3>Edit</h3>
                            <p>(Administrator Only)</p>
                            <p>
                                Click the 'Edit' button to make changes to this activity section.
                            </p>
                            <a href="{{ route('register') }}" class="inline-btn">Edit</a>
                        </div>

                        @foreach ($usersInActivity1 as $index => $userItem)
                            @if ($userItem->role === 'Teacher')
                                @if ($index === 0)
                                    <div class="box offer">
                                        <div class="tutor">
                                            <img src="{{ asset($userItem->user_avatar) }}" alt="teacher dp">
                                            <div>
                                                <h3>{{ $userItem->user_name }}</h3>
                                                <span>{{ $userItem->role }}</span>
                                            </div>
                                        </div>
                                        <p>ID: <span>{{ $userItem->id }}</span></p>
                                        <p>Major: <span>{{ $userItem->user_activity_1 }}</span></p>
                                        <p>Minor: <span>{{ $userItem->user_activity_2 }}</span></p>
                                        
                                    </div>
                                @else
                                    <div class="box">
                                        <div class="tutor">
                                            <img src="{{ asset($userItem->user_avatar) }}" alt="teacher dp">
                                            <div>
                                                <h3>{{ $userItem->user_name }}</h3>
                                                <span>{{ $userItem->role }}</span>
                                            </div>
                                        </div>
                                        <p>ID: <span>{{ $userItem->id }}</span></p>
                                        <p>Major: <span>{{ $userItem->user_activity_1 }}</span></p>
                                        <p>Minor: <span>{{ $userItem->user_activity_2 }}</span></p>
                                        <a href="{{ route('updateuser', ['id' => $userItem->id]) }}"
                                            class="inline-btn">View Profile</a>
                                    </div>
                                @endif
                            @endif
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="navigation-buttons">
            <button class="prev-button" disabled>Previous</button>
            <button class="next-button">Next</button>
        </div>

    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const prevButton = document.querySelector('.prev-button');
            const nextButton = document.querySelector('.next-button');
            const subsections = document.querySelectorAll('.teachers-subsection');
            let activeSubsectionIndex = 0;

            function showSubsection(index) {
                subsections.forEach(function(subsection, i) {
                    if (i === index) {
                        subsection.classList.add('active');
                    } else {
                        subsection.classList.remove('active');
                    }
                });
            }

            function updateButtonState() {
                prevButton.disabled = activeSubsectionIndex === 0;
                nextButton.disabled = activeSubsectionIndex === subsections.length - 1;
            }

            prevButton.addEventListener('click', function() {
                if (activeSubsectionIndex > 0) {
                    activeSubsectionIndex--;
                    showSubsection(activeSubsectionIndex);
                    updateButtonState();
                }
            });

            nextButton.addEventListener('click', function() {
                if (activeSubsectionIndex < subsections.length - 1) {
                    activeSubsectionIndex++;
                    showSubsection(activeSubsectionIndex);
                    updateButtonState();
                }
            });

            updateButtonState();
        });
    </script>

    <script>
        const searchBox = document.getElementById('searchBox');
        const teachersSection = document.querySelector('.teachers-section');
        const teacherBoxes = teachersSection.querySelectorAll('.box');

        searchBox.addEventListener('input', function(event) {
            const searchTerm = event.target.value.toLowerCase();

            teacherBoxes.forEach(function(teacherBox) {
                const teacherName = teacherBox.querySelector('h3').textContent.toLowerCase();

                if (teacherName.includes(searchTerm)) {
                    teacherBox.style.display = 'block';
                } else {
                    teacherBox.style.display = 'none';
                }
            });
        });
    </script>

</body>

</html>
