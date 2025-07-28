@extends('layouts.app')

@section('title', 'CREAMS Dashboard')

@section('content')
<div class="bg-gray-50" x-data="{ sidebarOpen: true, darkMode: false, viewType: 'cards' }">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200 fixed w-full top-0 z-50">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-md hover:bg-gray-100 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <h1 class="ml-4 text-2xl font-bold gradient-bg bg-clip-text text-transparent">CREAMS Dashboard</h1>
                </div>
                
                <div class="flex items-center space-x-4">
                    <!-- Current Time -->
                    <div class="hidden md:flex items-center text-sm text-gray-600">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ $current_time }}
                    </div>

                    <!-- Dark Mode Toggle -->
                    <button @click="darkMode = !darkMode" class="p-2 rounded-full hover:bg-gray-100 transition-colors">
                        <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                        </svg>
                        <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </button>
                    
                    <!-- User Profile -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center space-x-2 p-2 rounded-full hover:bg-gray-100 transition-colors">
                            <div class="w-8 h-8 rounded-full gradient-bg flex items-center justify-center text-white font-semibold">
                                {{ strtoupper(substr($user_name, 0, 1)) }}
                            </div>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                            <a href="{{ route('profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                            <a href="{{ route('logout') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Sidebar -->
    <aside x-show="sidebarOpen" x-transition:enter="transition ease-out duration-300"
           x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
           x-transition:leave="transition ease-in duration-300"
           x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
           class="fixed left-0 top-16 h-full w-64 bg-white shadow-lg z-40">
        <nav class="p-4 space-y-2">
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 p-3 rounded-lg gradient-bg text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                <span class="font-medium">Dashboard</span>
            </a>
            <a href="{{ route('activities.home') }}" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 transition-colors">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                <span class="font-medium">Activities</span>
            </a>
            <a href="{{ route('trainees.index') }}" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 transition-colors">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                <span class="font-medium">Trainees</span>
            </a>
            @if(in_array($role, ['admin', 'supervisor']))
            <a href="{{ route('staffs.index') }}" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 transition-colors">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <span class="font-medium">Staff</span>
            </a>
            @endif
        </nav>
    </aside>

    <!-- Main Content -->
    <main :class="sidebarOpen ? 'ml-64' : 'ml-0'" class="pt-16 transition-all duration-300">
        <div class="p-6">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                @if(isset($quick_stats))
                    @foreach($quick_stats as $stat)
                    <div class="bg-white rounded-2xl p-6 hover-lift">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 rounded-full bg-blue-100">
                                <i class="{{ $stat['icon'] ?? 'fas fa-chart-bar' }} w-6 h-6 text-blue-600"></i>
                            </div>
                            @if(isset($stat['trend']))
                            <span class="text-sm font-medium text-green-600 bg-green-100 px-2 py-1 rounded-full">{{ $stat['trend'] }}</span>
                            @endif
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800">{{ $stat['value'] ?? 0 }}</h3>
                        <p class="text-gray-600 text-sm mt-1">{{ $stat['title'] ?? 'Statistic' }}</p>
                    </div>
                    @endforeach
                @else
                <!-- Default Stats Cards -->
                <div class="bg-white rounded-2xl p-6 hover-lift">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 rounded-full bg-blue-100">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-green-600 bg-green-100 px-2 py-1 rounded-full">+12%</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800">0</h3>
                    <p class="text-gray-600 text-sm mt-1">Total Users</p>
                </div>
                @endif
            </div>

            <!-- Real-time Activities Section -->
            <div class="bg-white rounded-2xl p-6 mb-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-800">Ongoing Activities</h2>
                    <div class="flex items-center space-x-2">
                        <button @click="viewType = 'cards'" :class="viewType === 'cards' ? 'gradient-bg text-white' : 'bg-gray-100 text-gray-600'" class="p-2 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                            </svg>
                        </button>
                        <button @click="viewType = 'table'" :class="viewType === 'table' ? 'gradient-bg text-white' : 'bg-gray-100 text-gray-600'" class="p-2 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                        </button>
                        <button @click="viewType = 'timeline'" :class="viewType === 'timeline' ? 'gradient-bg text-white' : 'bg-gray-100 text-gray-600'" class="p-2 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Cards View -->
                <div x-show="viewType === 'cards'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @if(isset($current_sessions) && count($current_sessions) > 0)
                        @foreach($current_sessions as $session)
                        <div class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-sm font-medium text-green-600 bg-green-100 px-2 py-1 rounded-full">{{ $session['status'] ?? 'Ongoing' }}</span>
                                <span class="text-xs text-gray-500">{{ $session['time'] ?? 'Now' }}</span>
                            </div>
                            <h3 class="font-semibold text-gray-800 mb-2">{{ $session['activity'] ?? 'Activity' }}</h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex items-center text-gray-600">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    {{ $session['teacher'] ?? 'Teacher' }}
                                </div>
                                <div class="flex items-center text-gray-600">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    {{ $session['venue'] ?? 'Venue TBA' }}
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                    <!-- Default placeholder when no current sessions -->
                    <div class="col-span-full text-center py-8 text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p>No ongoing activities at the moment</p>
                    </div>
                    @endif
                </div>

                <!-- Table View -->
                <div x-show="viewType === 'table'" class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-3 px-4 font-medium text-gray-700">Activity</th>
                                <th class="text-left py-3 px-4 font-medium text-gray-700">Teacher</th>
                                <th class="text-left py-3 px-4 font-medium text-gray-700">Status</th>
                                <th class="text-left py-3 px-4 font-medium text-gray-700">Time</th>
                                <th class="text-left py-3 px-4 font-medium text-gray-700">Venue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($current_sessions) && count($current_sessions) > 0)
                                @foreach($current_sessions as $session)
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-3 px-4">{{ $session['activity'] ?? 'Activity' }}</td>
                                    <td class="py-3 px-4">{{ $session['teacher'] ?? 'Teacher' }}</td>
                                    <td class="py-3 px-4">
                                        <span class="text-sm font-medium text-green-600 bg-green-100 px-2 py-1 rounded-full">{{ $session['status'] ?? 'Ongoing' }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-gray-600">{{ $session['time'] ?? 'Now' }}</td>
                                    <td class="py-3 px-4 text-sm text-gray-600">{{ $session['venue'] ?? 'TBA' }}</td>
                                </tr>
                                @endforeach
                            @else
                            <tr>
                                <td colspan="5" class="py-8 text-center text-gray-500">No ongoing activities</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <!-- Timeline View -->
                <div x-show="viewType === 'timeline'" class="space-y-4">
                    @if(isset($current_sessions) && count($current_sessions) > 0)
                    <div class="relative">
                        <div class="absolute left-8 top-0 bottom-0 w-0.5 bg-gray-200"></div>
                        @foreach($current_sessions as $index => $session)
                        <div class="relative flex items-start mb-6">
                            <div class="absolute left-6 w-4 h-4 bg-green-500 rounded-full ring-4 ring-white"></div>
                            <div class="ml-16">
                                <div class="flex items-center space-x-2 mb-1">
                                    <span class="text-sm text-gray-500">{{ $session['time'] ?? 'Now' }}</span>
                                    <span class="text-sm font-medium text-green-600 bg-green-100 px-2 py-1 rounded-full">{{ $session['status'] ?? 'Ongoing' }}</span>
                                </div>
                                <h4 class="font-semibold text-gray-800">{{ $session['activity'] ?? 'Activity' }}</h4>
                                <p class="text-sm text-gray-600">{{ $session['teacher'] ?? 'Teacher' }} at {{ $session['venue'] ?? 'TBA' }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-8 text-gray-500">
                        <p>No ongoing activities in timeline</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions & Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Quick Actions -->
                <div class="bg-white rounded-2xl p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Quick Actions</h2>
                    <div class="grid grid-cols-2 gap-4">
                        @if(in_array($role, ['admin', 'supervisor', 'teacher']))
                        <a href="{{ route('activities.create') }}" class="p-4 border border-gray-200 rounded-xl hover:shadow-md transition-all hover:-translate-y-1 block text-center">
                            <svg class="w-8 h-8 mx-auto mb-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <span class="text-sm font-medium text-gray-700">Add Activity</span>
                        </a>
                        @endif
                        
                        @if(in_array($role, ['admin', 'supervisor']))
                        <a href="{{ route('trainees.create') }}" class="p-4 border border-gray-200 rounded-xl hover:shadow-md transition-all hover:-translate-y-1 block text-center">
                            <svg class="w-8 h-8 mx-auto mb-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                            <span class="text-sm font-medium text-gray-700">Add Trainee</span>
                        </a>
                        @endif
                        
                        <a href="#" class="p-4 border border-gray-200 rounded-xl hover:shadow-md transition-all hover:-translate-y-1 block text-center">
                            <svg class="w-8 h-8 mx-auto mb-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span class="text-sm font-medium text-gray-700">View Reports</span>
                        </a>
                        
                        <a href="#" class="p-4 border border-gray-200 rounded-xl hover:shadow-md transition-all hover:-translate-y-1 block text-center">
                            <svg class="w-8 h-8 mx-auto mb-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span class="text-sm font-medium text-gray-700">Schedule</span>
                        </a>
                    </div>
                </div>

                <!-- Activity Distribution Chart -->
                <div class="bg-white rounded-2xl p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Activity Distribution</h2>
                    @if(isset($progress_summary))
                    <div class="relative h-64 flex items-center justify-center">
                        <div class="text-center">
                            <div class="w-48 h-48 mx-auto relative">
                                <svg class="w-48 h-48 transform -rotate-90">
                                    <circle cx="96" cy="96" r="80" stroke="#e5e7eb" stroke-width="16" fill="none"></circle>
                                    <circle cx="96" cy="96" r="80" stroke="url(#gradient)" stroke-width="16" fill="none" 
                                            stroke-dasharray="{{ ($progress_summary['percentage'] / 100) * 502.65 }} 502.65" stroke-linecap="round"></circle>
                                    <defs>
                                        <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" style="stop-color:#32bdea;stop-opacity:1" />
                                            <stop offset="100%" style="stop-color:#c850c0;stop-opacity:1" />
                                        </linearGradient>
                                    </defs>
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div>
                                        <div class="text-3xl font-bold text-gray-800">{{ $progress_summary['percentage'] }}%</div>
                                        <div class="text-sm text-gray-600">{{ $progress_summary['title'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    <!-- Default chart when no progress data -->
                    <div class="h-64 flex items-center justify-center text-gray-500">
                        <div class="text-center">
                            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            <p>No activity data available</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </main>
</div>
@endsection

@section('styles')
<style>
    :root {
        --primary-color: #32bdea;
        --secondary-color: #c850c0;
        --primary-gradient: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    }

    .gradient-bg {
        background: var(--primary-gradient);
    }

    .glass-morphism {
        background: rgba(255, 255, 255, 0.25);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.18);
    }

    .neumorphic {
        background: #f0f0f3;
        box-shadow: 5px 5px 10px #d1d1d1, -5px -5px 10px #ffffff;
    }

    .neumorphic-inset {
        background: #f0f0f3;
        box-shadow: inset 5px 5px 10px #d1d1d1, inset -5px -5px 10px #ffffff;
    }

    @keyframes pulse-badge {
        0%, 100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.05); opacity: 0.8; }
    }

    .pulse-animation {
        animation: pulse-badge 2s infinite;
    }

    .hover-lift {
        transition: all 0.3s ease;
    }

    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
</style>
@endsection

@section('scripts')
<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endsection