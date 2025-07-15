<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/assetmanagementregisterstyle.css') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo/favicon.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.0.2/css/bootstrap.min.css">
    <title>Registration - CREAMS</title>
</head>

<body>
    <div class="logo">
        <a href="{{ route('home') }}">CREAMS</a>
    </div>
    <div class="box">
        <div class="container">
            <div class="title">
                <span>New Item</span>
            </div>
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
            <form action="{{ route('assetregisterpage.process') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="image-preview-container">
                    <div class="image-preview">
                        <img id="avatar-preview" src="{{ asset('images/default-avatar.png') }}" alt="Avatar Preview">
                    </div>
                </div>

                <div class="field">
                    <input type="text" name="asset_id" id="asset_id" required value="{{ old('asset_id') }}">
                    <label for="asset_id">Asset ID</label>
                </div>

                <div class="field">
                    <input type="text" name="asset_name" id="asset_name" required value="{{ old('asset_name') }}">
                    <label for="asset_name">Asset Name</label>
                </div>

                <div class="field">
                    <input type="text" name="asset_type" id="asset_type" required value="{{ old('asset_type') }}">
                    <label for="asset_type">Asset Type</label>
                </div>

                <div class="field">
                    <input type="number" name="asset_quantity" id="asset_quantity" required
                        value="{{ old('asset_quantity') }}">
                    <label for="asset_quantity">Asset Quantity</label>
                </div>

                <div class="field">
                    <input type="number" name="asset_price" id="asset_price" required
                        value="{{ old('asset_price') }}">
                    <label for="asset_price">Asset Price</label>
                </div>

                
                
                <div class="field">
                    <label for="center_name"></label>
                    <select name="center_name" id="center_name" required>
                        <option value="Gombak" {{ old('center_name') == 'Gombak' ?  : '' }}>Gombak
                        </option>
                        <option value="Kuantan" {{ old('center_name') == 'Kuantan' ?  : '' }}>
                            Kuantan</option>
                    </select>
                </div>

                <div class="field">
                    <input type="text" name="asset_brand" id="asset_brand" required value="{{ old('asset_brand') }}">
                    <label for="asset_brand">Asset Brand</label>
                </div>

                <div class="field">
                    <input type="text" name="asset_note" id="asset_note" required value="{{ old('asset_note') }}">
                    <label for="asset_note">Asset Note</label>
                </div>

                <div class="field">
                    <input type="file" name="asset_avatar" id="asset_avatar" value="Asset Image">
                    <label for="asset_avatar">Asset Image</label>
                </div>

                <div class="field">
                    <input type="submit" value="Add Item">
                </div>
            </form>

        </div>
    </div>
    <script>
        // JavaScript code for image preview
        const avatarInput = document.getElementById('asset_avatar');
        const imagePreviewContainer = document.querySelector('.image-preview-container');
        const avatarPreview = document.getElementById('avatar-preview');

        // Hide the image preview container initially
        imagePreviewContainer.style.display = 'none';

        avatarInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.addEventListener('load', function() {
                    avatarPreview.setAttribute('src', reader.result);
                    imagePreviewContainer.style.display = 'block'; // Show the image preview container
                });
                reader.readAsDataURL(file);
            } else {
                avatarPreview.setAttribute('src', '');
                imagePreviewContainer.style.display = 'none'; // Hide the image preview container
            }
        });
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.0.2/js/bootstrap.bundle.min.js"></script>
</body>

</html>
