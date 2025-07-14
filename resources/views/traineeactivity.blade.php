<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <title>Trainee Activity - CREAMS</title>
      
      <!-- Favicon -->
      <link rel="shortcut icon" href="../assets/trainee/images/favicon.png" />      
      <link rel="stylesheet" href="{{ asset('assets/trainee/css/backend-plugin.min.css') }}">
      <link rel="stylesheet" href="{{ asset('assets/trainee/css/line-awesome/dist/line-awesome/css/line-awesome.min.css') }}">
      <link rel="stylesheet" href="{{ asset('assets/trainee/css/remixicon/fonts/remixicon.css') }}">  
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
            <div class="col-lg-3">
               <div class="card">
                  <div class="card-body">
                     <div class="iq-todo-page">
                        <form class="position-relative">
                           <div class="form-group mb-0">
                              <input type="text" class="form-control todo-search" id="exampleInputEmail002"  placeholder="Search Activity">
                              <a class="search-link" href="#"><i class="ri-search-line"></i></a>
                           </div>
                        </form>
                        <div class="add-new-project mt-3 mb-3">
                           <a href="javascript:void(0);" class="d-block nrw-project"><i class="ri-add-line mr-2"></i>Add Activity</a>
                        </div>
                        <ul class="todo-task-list p-0 m-0">
                           <li class="">
                              <ul id="todo-task1" class="sub-task  show mt-2 p-0">
                                 <li><a href="javascript:void(0);"><i class="ri-checkbox-blank-circle-fill text-success"></i> All Task <span class="badge badge-danger ml-2 float-right">22</span></a></li>
                                 <li class="active"><a href="javascript:void(0);"><i class="ri-checkbox-blank-circle-fill text-warning"></i> Current Task <span class="badge badge-danger ml-2 float-right">1</span></a></li>
                                 <a href="javascript:void(0);"><i class="ri-stack-fill mr-2"></i> Reading</a>
                                 <li><a href="javascript:void(0);"><i class="ri-checkbox-blank-circle-fill text-danger"></i> Completed Task <span class="badge badge-danger ml-2 float-right">17</span> </a></li>
                                 <li><a href="javascript:void(0);"><i class="ri-checkbox-blank-circle-fill text-primary"></i> Others <span class="badge badge-danger ml-2 float-right">4</span></a></li>
                              </ul>
                           </li>
                        </ul>
                     </div>
                  </div>
               </div>
            </div>
            <div class="col-lg-9">
               <div class="row">
                  <div class="col-sm-12">
                     <div class="card">
                        <div class="card-body">
                           <div class="d-flex justify-content-between align-items-centre">
                              <div class="todo-date d-flex mr-3">
                                 <i class="ri-calendar-2-line text-success mr-2"></i>
                                 <span>Monday, 09th January, 2023</span>
                              </div>
                              <div class="todo-notification d-flex align-items-centre">
                                 <div class="notification-icon position-relative d-flex align-items-centre mr-3">
                                    <a href="#"><i class="ri-notification-3-line font-size-16"></i></a>
                                    <span class="bg-danger text-white">2</span>
                                 </div>
                                 <button type="submit" class="btn iq-bg-success">Notifications</button>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="col-md-8">
                     <div class="card">
                        <div class="card-body p-0">
                           <ul class="todo-task-lists m-0 p-0">
                              <li class="d-flex align-items-centre p-3">
                                 <div class="user-img img-fluid"><img src="{{ asset('assets/trainee/images/stactivity1.jpg') }}" alt="story-img" class="rounded avatar-40"></div>
                                 <div class="media-support-info ml-3">
                                    <h6 class="d-inline-block">Academics</h6>
                                    <p class="mb-0">Language</p>
                                 </div>
                                 <div class="card-header-toolbar d-flex align-items-centre">
                                    <div class="custom-control custom-checkbox">
                                       <input type="checkbox" name="todo-check" class="custom-control-input" id="check1">
                                       <label class="custom-control-label" for="check1"></label>
                                    </div>
                                 </div>
                              </li>

                              <li class="d-flex align-items-centre p-3">
                                 <div class="user-img img-fluid"><img src="{{ asset('assets/trainee/images/stactivity1.jpg') }}" alt="story-img" class="rounded avatar-40"></div>
                                 <div class="media-support-info ml-3">
                                    <h6>Academics</h6>
                                    <p class="mb-0">Mathematic</p>
                                 </div>
                                 <div class="card-header-toolbar d-flex align-items-centre">
                                    <div class="custom-control custom-checkbox">
                                       <input type="checkbox" name="todo-check" class="custom-control-input" id="check2">
                                       <label class="custom-control-label" for="check2"></label>
                                    </div>
                                 </div>
                              </li>

                              <li class="d-flex align-items-centre p-3">
                                 <div class="user-img img-fluid"><img src="{{ asset('assets/trainee/images/stactivity1.jpg') }}" alt="story-img" class="rounded avatar-40"></div>
                                 <div class="media-support-info ml-3">
                                    <h6 class="d-inline-block">Academics</h6>
                                    <p class="mb-0">Science</p>
                                 </div>
                                 <div class="card-header-toolbar d-flex align-items-centre">
                                    <div class="custom-control custom-checkbox">
                                       <input type="checkbox" name="todo-check" class="custom-control-input" id="check3">
                                       <label class="custom-control-label" for="check3"></label>
                                    </div>
                                 </div>
                              </li>

                              <li class="d-flex align-items-centre p-3">
                                 <div class="user-img img-fluid"><img src="{{ asset('assets/trainee/images/stactivity1.jpg') }}" alt="story-img" class="rounded avatar-40"></div>
                                 <div class="media-support-info ml-3">
                                    <h6>Academics</h6>
                                    <p class="mb-0">Arts & Creative</p>
                                 </div>
                                 <div class="card-header-toolbar d-flex align-items-centre">
                                    <div class="custom-control custom-checkbox">
                                       <input type="checkbox" name="todo-check" class="custom-control-input" id="check4">
                                       <label class="custom-control-label" for="check4"></label>
                                    </div>
                                 </div>
                              </li>

                              <li class="d-flex align-items-centre p-3  active-task">
                                 <div class="user-img img-fluid"><img src="{{ asset('assets/trainee/images/stactivity2.jpg') }}" alt="story-img" class="rounded avatar-40"></div>
                                 <div class="media-support-info ml-3">
                                    <h6>Therapy</h6>
                                    <p class="mb-0">Physio</p>
                                 </div>
                                 <div class="card-header-toolbar d-flex align-items-centre">
                                    <div class="custom-control custom-checkbox">
                                       <input type="checkbox" name="todo-check" class="custom-control-input" id="check5" checked="checked">
                                       <label class="custom-control-label" for="check5" ></label>
                                    </div>
                                 </div>
                              </li>
                              <li class="d-flex align-items-centre p-3">
                                 <div class="user-img img-fluid"><img src="{{ asset('assets/trainee/images/stactivity2.jpg') }}" alt="story-img" class="rounded avatar-40"></div>
                                 <div class="media-support-info ml-3">
                                    <h6 class="d-inline-block">Therapy</h6>
                                    <p class="mb-0">Cognitive</p>
                                 </div>
                                 <div class="card-header-toolbar d-flex align-items-centre">
                                    <div class="custom-control custom-checkbox">
                                       <input type="checkbox" name="todo-check" class="custom-control-input" id="check6">
                                       <label class="custom-control-label" for="check6"></label>
                                    </div>
                                 </div>
                              </li>
                              <li class="d-flex align-items-centre p-3">
                                 <div class="user-img img-fluid"><img src="{{ asset('assets/trainee/images/stactivity2.jpg') }}" alt="story-img" class="rounded avatar-40"></div>
                                 <div class="media-support-info ml-3">
                                    <h6>Therapy</h6>
                                    <p class="mb-0">Behavioral</p>
                                 </div>
                                 <div class="card-header-toolbar d-flex align-items-centre">
                                    <div class="custom-control custom-checkbox">
                                       <input type="checkbox" name="todo-check" class="custom-control-input" id="check7">
                                       <label class="custom-control-label" for="check7"></label>
                                    </div>
                                 </div>
                              </li>
                           </ul>
                        </div>
                     </div>
                  </div>
                  <div class="col-md-4">
                     <div class="card">
                        <div class="card-body">
                           <div class="iq-todo-right">
                              <form class="position-relative">
                                 <div class="form-group mb-0">
                                    <input type="text" class="form-control todo-search" id="exampleInputEmail001" placeholder="Search">
                                    <a class="search-link" href="#"><i class="ri-search-line"></i></a>
                                 </div>
                              </form>
                              <div class="iq-todo-friendlist mt-3">
                                 <ul class="suggestions-lists m-0 p-0">
                                    <li class="d-flex mb-4 align-items-centre">
                                       <div class="user-img img-fluid"><img src="{{ asset('assets/trainee/images/stactivity3.jpg') }}" alt="story-img" class="rounded avatar-40"></div>
                                       <div class="media-support-info ml-3">
                                          <h6>Farhan Omar</h6>
                                          <p class="mb-0">Mobility Training</p>
                                       </div>
                                       <div class="card-header-toolbar d-flex align-items-centre">
                                          <div class="dropdown">
                                             <span class="dropdown-toggle text-primary" id="dropdownMenuButton41" data-toggle="dropdown">
                                             <i class="ri-more-2-line"></i>
                                             </span>
                                             <div class="dropdown-menu dropdown-menu-right" style="">
                                                <a class="dropdown-item" href="#"><i class="ri-user-unfollow-line mr-2"></i>Unfollow</a>
                                                <a class="dropdown-item" href="#"><i class="ri-close-circle-line mr-2"></i>Unfriend</a>
                                                <a class="dropdown-item" href="#"><i class="ri-lock-line mr-2"></i>block</a>
                                             </div>
                                          </div>
                                       </div>
                                    </li>
                                    <li class="d-flex mb-4 align-items-centre">
                                       <div class="user-img img-fluid"><img src="{{ asset('assets/trainee/images/dashboard3.jpg') }}" alt="story-img" class="rounded avatar-40"></div>
                                       <div class="media-support-info ml-3">
                                          <h6>Mariani Talib</h6>
                                          <p class="mb-0">Mobility Training</p>
                                       </div>
                                       <div class="card-header-toolbar d-flex align-items-centre">
                                          <div class="dropdown">
                                             <span class="dropdown-toggle text-primary" id="dropdownMenuButton42" data-toggle="dropdown">
                                             <i class="ri-more-2-line"></i>
                                             </span>
                                             <div class="dropdown-menu dropdown-menu-right" style="">
                                                <a class="dropdown-item" href="#"><i class="ri-user-unfollow-line mr-2"></i>Unfollow</a>
                                                <a class="dropdown-item" href="#"><i class="ri-close-circle-line mr-2"></i>Unfriend</a>
                                                <a class="dropdown-item" href="#"><i class="ri-lock-line mr-2"></i>block</a>
                                             </div>
                                          </div>
                                       </div>
                                    </li>
                                    <li class="d-flex mb-4 align-items-centre">
                                       <div class="user-img img-fluid"><img src="{{ asset('assets/trainee/images/stactivity3.jpg') }}" alt="story-img" class="rounded avatar-40"></div>
                                       <div class="media-support-info ml-3">
                                          <h6>Farhan Omar</h6>
                                          <p class="mb-0">Sensory Integration Therapy</p>
                                       </div>
                                       <div class="card-header-toolbar d-flex align-items-centre">
                                          <div class="dropdown">
                                             <span class="dropdown-toggle text-primary" id="dropdownMenuButton43" data-toggle="dropdown">
                                             <i class="ri-more-2-line"></i>
                                             </span>
                                             <div class="dropdown-menu dropdown-menu-right" style="">
                                                <a class="dropdown-item" href="#"><i class="ri-user-unfollow-line mr-2"></i>Unfollow</a>
                                                <a class="dropdown-item" href="#"><i class="ri-close-circle-line mr-2"></i>Unfriend</a>
                                                <a class="dropdown-item" href="#"><i class="ri-lock-line mr-2"></i>block</a>
                                             </div>
                                          </div>
                                       </div>
                                    </li>
                                    <li class="d-flex mb-4 align-items-centre">
                                       <div class="user-img img-fluid"><img src="{{ asset('assets/trainee/images/dashboard3.jpg') }}" alt="story-img" class="rounded avatar-40"></div>
                                       <div class="media-support-info ml-3">
                                          <h6>Mariani Talib</h6>
                                          <p class="mb-0">Sensory Integration Therapy</p>
                                       </div>
                                       <div class="card-header-toolbar d-flex align-items-centre">
                                          <div class="dropdown">
                                             <span class="dropdown-toggle text-primary" id="dropdownMenuButton44" data-toggle="dropdown">
                                             <i class="ri-more-2-line"></i>
                                             </span>
                                             <div class="dropdown-menu dropdown-menu-right" style="">
                                                <a class="dropdown-item" href="#"><i class="ri-user-unfollow-line mr-2"></i>Unfollow</a>
                                                <a class="dropdown-item" href="#"><i class="ri-close-circle-line mr-2"></i>Unfriend</a>
                                                <a class="dropdown-item" href="#"><i class="ri-lock-line mr-2"></i>block</a>
                                             </div>
                                          </div>
                                       </div>
                                    </li>
                                    <li class="d-flex mb-4 align-items-centre">
                                       <div class="user-img img-fluid"><img src="{{ asset('assets/trainee/images/stactivity3.jpg') }}" alt="story-img" class="rounded avatar-40"></div>
                                       <div class="media-support-info ml-3">
                                          <h6>Farhan Omar</h6>
                                          <p class="mb-0">Tactile Perception Training</p>
                                       </div>
                                       <div class="card-header-toolbar d-flex align-items-centre">
                                          <div class="dropdown">
                                             <span class="dropdown-toggle text-primary" id="dropdownMenuButton45" data-toggle="dropdown">
                                             <i class="ri-more-2-line"></i>
                                             </span>
                                             <div class="dropdown-menu dropdown-menu-right" style="">
                                                <a class="dropdown-item" href="#"><i class="ri-user-unfollow-line mr-2"></i>Unfollow</a>
                                                <a class="dropdown-item" href="#"><i class="ri-close-circle-line mr-2"></i>Unfriend</a>
                                                <a class="dropdown-item" href="#"><i class="ri-lock-line mr-2"></i>block</a>
                                             </div>
                                          </div>
                                       </div>
                                    </li>
                                    <li class="d-flex mb-4 align-items-centre">
                                       <div class="user-img img-fluid"><img src="{{ asset('assets/trainee/images/dashboard3.jpg') }}" alt="story-img" class="rounded avatar-40"></div>
                                       <div class="media-support-info ml-3">
                                          <h6>Mariani Talib</h6>
                                          <p class="mb-0">Tactile Perception Training</p>
                                       </div>
                                       <div class="card-header-toolbar d-flex align-items-centre">
                                          <div class="dropdown">
                                             <span class="dropdown-toggle text-primary" id="dropdownMenuButton46" data-toggle="dropdown">
                                             <i class="ri-more-2-line"></i>
                                             </span>
                                             <div class="dropdown-menu dropdown-menu-right" style="">
                                                <a class="dropdown-item" href="#"><i class="ri-user-unfollow-line mr-2"></i>Unfollow</a>
                                                <a class="dropdown-item" href="#"><i class="ri-close-circle-line mr-2"></i>Unfriend</a>
                                                <a class="dropdown-item" href="#"><i class="ri-lock-line mr-2"></i>block</a>
                                             </div>
                                          </div>
                                       </div>
                                    </li>
                                    
                                 </ul>
                                 <a href="javascript:void(0);" class="btn btn-primary d-block"><i class="ri-add-line"></i> Load More</a>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      </div>
    </div>
    <!-- Wrapper End-->
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
                            <span class="mr-1"><script>document.write(new Date().getFullYear())</script>©</span> <a href="admins/dashboard" class="">CREAMS</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <!-- Backend Bundle JavaScript -->
    <script src="../assets/js/backend-bundle.min.js"></script>
    
    <!-- Table Treeview JavaScript -->
    <script src="../assets/js/table-treeview.js"></script>
    
    <!-- Chart Custom JavaScript -->
    <script src="../assets/js/customizer.js"></script>
    
    <!-- Chart Custom JavaScript -->
    <script async src="../assets/js/chart-custom.js"></script>
    
    <!-- app JavaScript -->
    <script src="../assets/js/app.js"></script>
  </body>
</html>