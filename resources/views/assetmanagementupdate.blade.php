<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - CREAMS</title>
    <link rel="stylesheet" href="{{ asset('css/assetmanagementupdatestyle.css') }}">
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
                <h1 class="title">Item Information</h1>
                <div class="avatar">
                    @if ($asset->asset_avatar)
                        <img src="{{ asset($asset->asset_avatar) }}" class="img-fluid rounded avatar-50 mr-3"
                            alt="image">
                    @endif
                </div>
                <div class="info">
                    <h3>{{ $asset->asset_name }}</h3>
                    <p>Brand: {{ $asset->asset_brand }}</p>
                    <p>Price: RM{{ $asset->asset_price }}</p>
                </div>
            </div>
            <div class="update-profile">
                <h1 class="title">Edit Item</h1>
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
                <form method="POST" action="{{ route('assetupdatepage.process') }}" class="form"
                    enctype="multipart/form-data">
                    @csrf

                    <div class="form-group">
                        <label for="asset_id">Asset ID:</label>
                        <input type="text" id="asset_id" name="asset_id" required
                            value="{{ old('asset_id', $asset->asset_id) }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="asset_name">Asset Name:</label>
                        <input type="text" id="asset_name" name="asset_name" required readonly
                            value="{{ old('asset_name', $asset->asset_name) }}" >
                    </div>
                    
                    <div class="form-group">
                        <label for="asset_type">Asset Type:</label>
                        <input type="text" id="asset_type" name="asset_type" required
                            value="{{ old('asset_type', $asset->asset_type) }}"readonly>
                    </div>
                    <div class="form-group">
                        <label for="asset_quantity">Asset Quantity:</label>
                        <input type="number" id="asset_quantity" name="asset_quantity" required
                            value="{{ old('asset_quantity', $asset->asset_quantity) }}"readonly>
                    </div>
                    <div class="form-group">
                        <label for="asset_price">Asset Price:</label>
                        <input type="number" id="asset_price" name="asset_price" required
                            value="{{ old('asset_price', $asset->asset_price) }}"readonly>
                    </div>
                    <div class="form-group">
                        <label for="center_name">Centre Name:</label>
                        <select id="center_name" name="center_name" required readonly>
                            <option value="Gombak"
                                {{ old('center_name', $asset->center_name) == 'Gombak' ? 'selected' : '' }}>Gombak
                            </option>
                            <option value="Kuantan"
                                {{ old('center_name', $asset->center_name) == 'Kuantan' ? 'selected' : '' }}>Kuantan
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="asset_brand">Asset Brand:</label>
                        <input type="text" id="asset_brand" name="asset_brand" required
                            value="{{ old('asset_brand', $asset->asset_brand) }}"readonly>
                    </div>
                    <div class="form-group">
                        <label for="asset_note">Asset Note:</label>
                        <input type="text" id="asset_note" name="asset_note" required
                            value="{{ old('asset_note', $asset->asset_note) }}"readonly>
                    </div>
                    <div class="form-group">
                        <label for="asset_avatar">Asset Avatar:</label>
                        <input type="file" name="asset_avatar" id="asset_avatar" value="Asset Avatar">
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
