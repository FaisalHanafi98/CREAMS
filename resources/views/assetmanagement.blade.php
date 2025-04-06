<!doctype html>
<html lang="en">

  <head>
    <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <title>Asset Management</title>
      
      <!-- Favicon -->
      <link rel="shortcut icon" href="{{ asset('assets/asset/images/favicon.ico') }}" />
      <link rel="stylesheet" href="{{ asset('assets/asset/css/backend-plugin.min.css') }}">
      <link rel="stylesheet" href="{{ asset('assets/asset/css/line-awesome/dist/line-awesome/css/line-awesome.min.css') }}">
      <link rel="stylesheet" href="{{ asset('assets/asset/css/remixicon/fonts/remixicon.css') }}">  
    </head>

    <!-- Wrapper Start -->
    <div class="wrapper">
        <div class="iq-top-navbar">
          <div class="iq-navbar-custom">
              <nav class="navbar navbar-expand-lg navbar-light p-0">
                  <div class="iq-navbar-logo d-flex align-items-centre justify-content-between">             
                    <a href="/admins/dashboard" class="header-logo">
                          <h5 class="logo-title ml-3">CREAMS</h5>
                      </a>
                  </div>
              </nav>
          </div>
      </div>
     <div class="content-page">
     <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="d-flex flex-wrap flex-wrap align-items-centre justify-content-between mb-4">
                    <div>
                        <h4 class="mb-3">Inventory</h4>
                        <p class="mb-0">The asset list effectively outlines the current situation of the centre's inventory situation, ensuring that they are being used effectively and efficiently.</p>
                    </div>
                    <a href="page-add-product.html" class="btn btn-primary add-list"><i class="las la-plus mr-3"></i>Add Item</a>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="table-responsive rounded mb-3">
                <table class="data-tables table mb-0 tbl-server-info">
                    <thead class="bg-white text-uppercase">
                        <tr class="ligth ligth-data">
                            <th>
                                <div class="checkbox d-inline-block">
                                    <input type="checkbox" class="checkbox-input" id="checkbox1">
                                    <label for="checkbox1" class="mb-0"></label>
                                </div>
                            </th>
                            <th>Product</th>
                            <th>Code</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Brand Name</th>
                            <th>Quantity</th>
                            <th>Note</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody class="ligth-body">
                        <tr>
                            <td>
                                <div class="checkbox d-inline-block">
                                    <input type="checkbox" class="checkbox-input" id="checkbox2">
                                    <label for="checkbox2" class="mb-0"></label>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-centre">
                                    <img src={{ asset('assets/asset/images/assetpage1.jpg') }} class="img-fluid rounded avatar-50 mr-3" alt="image">
                                    <div>
                                        Table
                                        <p class="mb-0"><small>Wooden Table</small></p>
                                    </div>
                                </div>
                            </td>
                            <td>CLASS01</td>
                            <td>Essential</td>
                            <td>RM300.00</td>
                            <td>Jati Bangkit Sdn. Bhd.</td>
                            <td>100 + 10</td>
                            <td>Sufficient</td>
                            <td>
                                <div class="d-flex align-items-centre list-action">
                                    <a class="badge badge-info mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="View"
                                        href="#"><i class="ri-eye-line mr-0"></i></a>
                                    <a class="badge bg-success mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"
                                        href="#"><i class="ri-pencil-line mr-0"></i></a>
                                    <a class="badge bg-warning mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete"
                                        href="#"><i class="ri-delete-bin-line mr-0"></i></a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="checkbox d-inline-block">
                                    <input type="checkbox" class="checkbox-input" id="checkbox3">
                                    <label for="checkbox3" class="mb-0"></label>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-centre">
                                    <img src={{ asset('assets/asset/images/assetpage2.jpg') }} class="img-fluid rounded avatar-50 mr-3" alt="image">                                    
                                    <div>
                                        Chair
                                        <p class="mb-0"><small>Plastic and Wooden Chair</small></p>
                                    </div>
                                </div>
                            </td>
                            <td>CLASS02</td>
                            <td>Essential</td>
                            <td>RM50.00</td>
                            <td>Sinaran Kayu Sdn. Bhd.</td>
                            <td>80 + 50</td>
                            <td>Extra</td>
                            <td>
                                <div class="d-flex align-items-centre list-action">
                                    <a class="badge badge-info mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="View"
                                        href="#"><i class="ri-eye-line mr-0"></i></a>
                                    <a class="badge bg-success mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"
                                        href="#"><i class="ri-pencil-line mr-0"></i></a>
                                    <a class="badge bg-warning mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete"
                                        href="#"><i class="ri-delete-bin-line mr-0"></i></a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="checkbox d-inline-block">
                                    <input type="checkbox" class="checkbox-input" id="checkbox4">
                                    <label for="checkbox4" class="mb-0"></label>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-centre">
                                    <img src={{ asset('assets/asset/images/assetpage3.jpg') }} class="img-fluid rounded avatar-50 mr-3" alt="image">                                    
                                    <div>
                                        Computer
                                        <p class="mb-0"><small>Desktop Computer</small></p>
                                    </div>
                                </div>
                            </td>
                            <td>TECH01</td>
                            <td>N-Essential</td>
                            <td>RM2000.00</td>
                            <td>Apple</td>
                            <td>15</td>
                            <td>Inadequate</td>
                            <td>
                                <div class="d-flex align-items-centre list-action">
                                    <a class="badge badge-info mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="View"
                                        href="#"><i class="ri-eye-line mr-0"></i></a>
                                    <a class="badge bg-success mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"
                                        href="#"><i class="ri-pencil-line mr-0"></i></a>
                                    <a class="badge bg-warning mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete"
                                        href="#"><i class="ri-delete-bin-line mr-0"></i></a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="checkbox d-inline-block">
                                    <input type="checkbox" class="checkbox-input" id="checkbox5">
                                    <label for="checkbox5" class="mb-0"></label>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-centre">
                                    <img src={{ asset('assets/asset/images/assetpage4.jpg') }} class="img-fluid rounded avatar-50 mr-3" alt="image">                                    
                                    <div>
                                        Whiteboard
                                        <p class="mb-0"><small>4x6 Whiteboard</small></p>
                                    </div>
                                </div>
                            </td>
                            <td>CLASS03</td>
                            <td>Essential</td>
                            <td>RM100.00</td>
                            <td>Apex</td>
                            <td>13 + 5</td>
                            <td>Extra</td>
                            <td>
                                <div class="d-flex align-items-centre list-action">
                                    <a class="badge badge-info mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="View"
                                        href="#"><i class="ri-eye-line mr-0"></i></a>
                                    <a class="badge bg-success mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"
                                        href="#"><i class="ri-pencil-line mr-0"></i></a>
                                    <a class="badge bg-warning mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete"
                                        href="#"><i class="ri-delete-bin-line mr-0"></i></a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="checkbox d-inline-block">
                                    <input type="checkbox" class="checkbox-input" id="checkbox6">
                                    <label for="checkbox6" class="mb-0"></label>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-centre">
                                    <img src={{ asset('assets/asset/images/assetpage5.jpg') }} class="img-fluid rounded avatar-50 mr-3" alt="image">
                                    <div>
                                        Projector                                   
                                        <p class="mb-0"><small>Portable Projector</small></p>
                                    </div>
                                </div>
                            </td>
                            <td>TECH02</td>
                            <td>N-Essential</td>
                            <td>RM1000.00</td>
                            <td>Hisense</td>
                            <td>8 + 5</td>
                            <td>Sufficient</td>
                            <td>
                                <div class="d-flex align-items-centre list-action">
                                    <a class="badge badge-info mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="View"
                                        href="#"><i class="ri-eye-line mr-0"></i></a>
                                    <a class="badge bg-success mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"
                                        href="#"><i class="ri-pencil-line mr-0"></i></a>
                                    <a class="badge bg-warning mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete"
                                        href="#"><i class="ri-delete-bin-line mr-0"></i></a>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
        <!-- Page end  -->
    </div>
    <!-- Modal Edit -->
      </div>
    </div>
    
    <footer class="iq-footer">
            <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <ul class="list-inline mb-0">
                                <li class="list-inline-item"><a href="../backend/privacy-policy.html">Privacy Policy</a></li>
                                <li class="list-inline-item"><a href="../backend/terms-of-service.html">Terms of Use</a></li>
                            </ul>
                        </div>
                        <div class="col-lg-6 text-right">
                            <span class="mr-1"><script>document.write(new Date().getFullYear())</script>©</span> <a href="/admins/dashboard" class="">CREAMS</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
  </body>
</html>


<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Asset Management</title>
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <script src="https://kit.fontawesome.com/8f0012bd95.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="{{ asset('css/backend-plugin.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/line-awesome/dist/line-awesome/css/line-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/remixicon/fonts/remixicon.css') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo/favicon.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('css/assetmanagementstyle.css') }}">
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
            <div class="main-top">
              <h1 class="logo">CREAMS</h1>
              <span class="small-text">Community-based REhAbilitation Management System</span>
            </div>
          </section>
          

            <!-- Page content -->
            <div class="wrapper">
                <div class="content-page">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="d-flex flex-wrap flex-wrap align-items-centre justify-content-between mb-4">
                                    <div>
                                        <h4 class="mb-3">Inventory</h4>
                                        <p class="mb-0">The inventory list effectively outlines the current situation
                                            of the
                                            rehabilitation centre's assets, ensuring that they are being used
                                            effectively and
                                            efficiently.</p>

                                    </div>
                                    <a href="{{ route('assetregisterpage') }}" class="btn btn-primary add-list"><i
                                            class="las la-plus mr-3"></i>Add Asset</a>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="table-responsive rounded mb-3">
                                    <table id="data-table" class="data-tables table mb-0 tbl-server-info">
                                        <thead class="bg-white text-uppercase">
                                            <tr class="light light-data">
                                                <th>No.</th>
                                                <th>Product</th>
                                                <th>Code</th>
                                                <th>Category</th>
                                                <th>Price</th>
                                                <th>Brand Name</th>
                                                <th>Quantity</th>
                                                <th>Note</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="light-body">
                                            @foreach ($assets as $index => $asset)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>
                                                        <div class="d-flex align-items-centre">
                                                            <img src="{{ asset($asset->asset_avatar) }}"
                                                                class="img-fluid rounded avatar-50 mr-3" alt="image">
                                                            {{ $asset->asset_name }}
                                                    </td>
                                                    <td>{{ $asset->asset_id }}</td>
                                                    <td>{{ $asset->asset_type }}</td>
                                                    <td>{{ $asset->asset_price }}</td>
                                                    <td>{{ $asset->asset_brand }}</td>
                                                    <td>{{ $asset->asset_quantity }}</td>
                                                    <td>{{ $asset->asset_note }}</td>
                                                    <td>
                                                        <div class="d-flex align-items-centre">
                                                            <a href="{{ route('assetupdatepage', $asset->asset_id) }}"
                                                                class="mr-3" data-toggle="tooltip"
                                                                data-placement="top" title="Edit">
                                                                <i class="ri-pencil-line"></i>
                                                            </a>
                                                            
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Modal Edit -->
            </div>
        </section>
    </div>

    <footer class="iq-footer">
        <!-- Footer content -->
    </footer>

    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#data-table').DataTable();
        });
    </script>
</body>

</html>
 
