@extends('layouts.app')

@section('title', 'CREAMS Activities')

@section('content')
<div class="bg-gray-50" x-data="{
    sidebarOpen: true,
    view: 'grid',
    filterOpen: false,
    selectedStatus: 'all',
    selectedCategory: 'all',
    showAddModal: false,
    searchQuery: ''
}">
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
                    <h1 class="ml-4 text-2xl font-bold gradient-bg bg-clip-text text-transparent">Activities Management</h1>
                </div>

                <!-- Search Bar -->
                <div class="hidden md:flex items-center flex-1 max-w-md mx-8">
                    <div class="relative w-full">
                        <input
                            type="text"
                            x-model="searchQuery"
                            placeholder="Search activities, trainees, or teachers..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                        <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center space-x-3">
                    @if(in_array($role, ['admin', 'supervisor', 'teacher']))
                    <button @click="showAddModal = true" class="gradient-bg text-white px-4 py-2 rounded-lg font-medium hover:opacity-90 transition-opacity flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        <span class="hidden sm:inline">Add Activity</span>
                    </button>
                    @endif

                    <button class="p-2 rounded-lg hover:bg-gray-100 transition-colors relative">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        @if(isset($notifications) && count($notifications) > 0)
                        <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
                        @endif
                    </button>
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
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 transition-colors">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                <span class="font-medium">Dashboard</span>
            </a>
            <a href="{{ route('activities.home') }}" class="flex items-center space-x-3 p-3 rounded-lg gradient-bg text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            <!-- Page Header with Stats -->
            <div class="mb-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    @if(isset($activity_stats))
                        @foreach($activity_stats as $stat)
                        <div class="bg-white rounded-xl p-4 border border-gray-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-600">{{ $stat['title'] }}</p>
                                    <p class="text-2xl font-bold {{ $stat['color_class'] ?? 'text-gray-800' }}">{{ $stat['value'] }}</p>
                                </div>
                                <div class="p-3 {{ $stat['bg_class'] ?? 'bg-blue-100' }} rounded-lg">
                                    <i class="{{ $stat['icon'] ?? 'fas fa-chart-bar' }} w-6 h-6 {{ $stat['icon_color'] ?? 'text-blue-600' }}"></i>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                    <!-- Default Stats -->
                    <div class="bg-white rounded-xl p-4 border border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Total Activities</p>
                                <p class="text-2xl font-bold text-gray-800">{{ $total_activities ?? 0 }}</p>
                            </div>
                            <div class="p-3 bg-blue-100 rounded-lg">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl p-4 border border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Ongoing</p>
                                <p class="text-2xl font-bold text-green-600">{{ $ongoing_activities ?? 0 }}</p>
                            </div>
                            <div class="p-3 bg-green-100 rounded-lg">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl p-4 border border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Completed Today</p>
                                <p class="text-2xl font-bold text-purple-600">{{ $completed_today ?? 0 }}</p>
                            </div>
                            <div class="p-3 bg-purple-100 rounded-lg">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl p-4 border border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Scheduled</p>
                                <p class="text-2xl font-bold text-orange-600">{{ $scheduled_activities ?? 0 }}</p>
                            </div>
                            <div class="p-3 bg-orange-100 rounded-lg">
                                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Filters and View Options -->
            <div class="bg-white rounded-xl p-4 mb-6 flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Status Filter -->
                    <select x-model="selectedStatus" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="all">All Status</option>
                        <option value="ongoing">Ongoing</option>
                        <option value="completed">Completed</option>
                        <option value="scheduled">Scheduled</option>
                        <option value="cancelled">Cancelled</option>
                    </select>

                    <!-- Category Filter -->
                    <select x-model="selectedCategory" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="all">All Categories</option>
                        @if(isset($categories))
                            @foreach($categories as $category)
                            <option value="{{ $category }}">{{ ucfirst($category) }}</option>
                            @endforeach
                        @else
                        <option value="speech">Speech Therapy</option>
                        <option value="physical">Physical Therapy</option>
                        <option value="art">Art Therapy</option>
                        <option value="music">Music Therapy</option>
                        <option value="occupational">Occupational Therapy</option>
                        @endif
                    </select>

                    <!-- Date Filter -->
                    <input type="date" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">

                    <!-- Clear Filters -->
                    <button @click="selectedStatus = 'all'; selectedCategory = 'all'" class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium">
                        Clear Filters
                    </button>
                </div>

                <!-- View Toggle -->
                <div class="flex items-center space-x-2">
                    <button @click="view = 'grid'" :class="view === 'grid' ? 'gradient-bg text-white' : 'bg-gray-100 text-gray-600'" class="p-2 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                        </svg>
                    </button>
                    <button @click="view = 'list'" :class="view === 'list' ? 'gradient-bg text-white' : 'bg-gray-100 text-gray-600'" class="p-2 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                        </svg>
                    </button>
                    <button @click="view = 'calendar'" :class="view === 'calendar' ? 'gradient-bg text-white' : 'bg-gray-100 text-gray-600'" class="p-2 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Activities Content -->
            <!-- Grid View -->
            <div x-show="view === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @if(isset($activities) && count($activities) > 0)
                    @foreach($activities as $activity)
                    <div class="bg-white rounded-xl overflow-hidden hover-scale cursor-pointer">
                        <div class="h-2 {{ $activity['status_color'] ?? 'gradient-bg' }}"></div>
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-sm font-medium {{ $activity['status_text_color'] ?? 'text-green-600' }} {{ $activity['status_bg_color'] ?? 'bg-green-100' }} px-3 py-1 rounded-full flex items-center">
                                    @if($activity['status'] === 'ongoing')
                                    <span class="w-2 h-2 bg-green-600 rounded-full mr-2 animate-pulse"></span>
                                    @endif
                                    {{ ucfirst($activity['status'] ?? 'Scheduled') }}
                                </span>
                                <button class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                    </svg>
                                </button>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ $activity['name'] ?? 'Activity Name' }}</h3>
                            <p class="text-sm text-gray-600 mb-4">{{ $activity['description'] ?? 'Activity description...' }}</p>

                            <div class="space-y-3 mb-4">
                                <div class="flex items-center text-sm text-gray-600">
                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    {{ $activity['teacher_name'] ?? 'Teacher Name' }}
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    {{ $activity['participant_count'] ?? 0 }} Trainees
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ $activity['schedule_time'] ?? 'Time TBA' }}
                                </div>
                            </div>

                            @if(isset($activity['next_session_info']))
                            <div class="bg-orange-50 rounded-lg p-3 mb-4">
                                <p class="text-sm text-orange-800">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ $activity['next_session_info'] }}
                                </p>
                            </div>
                            @endif

                            <div class="flex space-x-2">
                                <button class="flex-1 py-2 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors text-sm font-medium">
                                    View Details
                                </button>
                                @if($activity['status'] === 'ongoing')
                                <button class="flex-1 py-2 px-4 gradient-bg text-white rounded-lg hover:opacity-90 transition-opacity text-sm font-medium">
                                    Join Session
                                </button>
                                @elseif($activity['status'] === 'scheduled')
                                <button class="flex-1 py-2 px-4 gradient-bg text-white rounded-lg hover:opacity-90 transition-opacity text-sm font-medium">
                                    Prepare Session
                                </button>
                                @else
                                <button class="flex-1 py-2 px-4 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm font-medium">
                                    View Report
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                <!-- No activities placeholder -->
                <div class="col-span-full text-center py-12">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No activities found</h3>
                    <p class="text-gray-500 mb-4">Get started by creating your first activity.</p>
                    @if(in_array($role, ['admin', 'supervisor', 'teacher']))
                    <button @click="showAddModal = true" class="gradient-bg text-white px-6 py-2 rounded-lg font-medium hover:opacity-90 transition-opacity">
                        Create Activity
                    </button>
                    @endif
                </div>
                @endif
            </div>

            <!-- List View -->
            <div x-show="view === 'list'" class="bg-white rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activity</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teacher</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trainees</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @if(isset($activities) && count($activities) > 0)
                                @foreach($activities as $activity)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $activity['name'] ?? 'Activity Name' }}</div>
                                            <div class="text-sm text-gray-500">{{ Str::limit($activity['description'] ?? 'Description', 50) }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 rounded-full gradient-bg flex items-center justify-center text-white font-semibold text-sm mr-3">
                                                {{ strtoupper(substr($activity['teacher_name'] ?? 'T', 0, 2)) }}
                                            </div>
                                            <div class="text-sm text-gray-900">{{ $activity['teacher_name'] ?? 'Teacher' }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">{{ $activity['participant_count'] ?? 0 }} trainees</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $activity['schedule_time'] ?? 'Time TBA' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm font-medium {{ $activity['status_text_color'] ?? 'text-green-600' }} {{ $activity['status_bg_color'] ?? 'bg-green-100' }} px-3 py-1 rounded-full">
                                            {{ ucfirst($activity['status'] ?? 'Scheduled') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-2">
                                            <button class="text-blue-600 hover:text-blue-800">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </button>
                                            @if(in_array($role, ['admin', 'supervisor', 'teacher']))
                                            <button class="text-gray-600 hover:text-gray-800">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </button>
                                            @endif
                                            @if(in_array($role, ['admin', 'supervisor']))
                                            <button class="text-red-600 hover:text-red-800">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            @else
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">No activities found</h3>
                                    <p class="text-gray-500">Get started by creating your first activity.</p>
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Calendar View -->
            <div x-show="view === 'calendar'" class="bg-white rounded-xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-800">{{ now()->format('F Y') }}</h3>
                    <div class="flex items-center space-x-2">
                        <button class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                        <button class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-7 gap-px bg-gray-200 rounded-lg overflow-hidden">
                    <!-- Day headers -->
                    @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                    <div class="bg-gray-50 p-3 text-center text-sm font-medium text-gray-700">{{ $day }}</div>
                    @endforeach

                    <!-- Calendar days -->
                    @php
                    $startOfMonth = now()->startOfMonth();
                    $endOfMonth = now()->endOfMonth();
                    $startDate = $startOfMonth->copy()->startOfWeek();
                    $endDate = $endOfMonth->copy()->endOfWeek();
                    $current = $startDate->copy();
                    @endphp

                    @while($current->lte($endDate))
                    <div class="bg-white p-3 min-h-[100px]">
                        <div class="text-sm {{ $current->month === now()->month ? 'font-medium text-gray-900' : 'text-gray-400' }}">
                            {{ $current->day }}
                        </div>
                        @if($current->month === now()->month)
                        <div class="mt-1 space-y-1">
                            @if(isset($calendar_events[$current->format('Y-m-d')]))
                                @foreach($calendar_events[$current->format('Y-m-d')] as $event)
                                <div class="text-xs p-1 {{ $event['color_class'] ?? 'bg-blue-100 text-blue-700' }} rounded truncate">
                                    {{ $event['title'] ?? 'Event' }}
                                </div>
                                @endforeach
                            @endif
                        </div>
                        @endif
                    </div>
                    @php $current->addDay(); @endphp
                    @endwhile
                </div>
            </div>
        </div>
    </main>

    <!-- Add Activity Modal -->
    <div x-show="showAddModal" x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
         @click.self="showAddModal = false">
        <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto"
             @click.stop>
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-800">Add New Activity</h2>
                    <button @click="showAddModal = false" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <form action="{{ route('activities.store') }}" method="POST" class="p-6 space-y-6">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Activity Name</label>
                    <input type="text" name="activity_name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Enter activity name" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                    <select name="category" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        <option value="">Select Category</option>
                        <option value="speech">Speech Therapy</option>
                        <option value="physical">Physical Therapy</option>
                        <option value="art">Art Therapy</option>
                        <option value="music">Music Therapy</option>
                        <option value="occupational">Occupational Therapy</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Enter activity description"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                        <input type="date" name="activity_date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Time</label>
                        <input type="time" name="activity_time" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                    </div>
                </div>

                @if(isset($teachers) && count($teachers) > 0)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Assign Teacher</label>
                    <select name="teacher_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        <option value="">Select Teacher</option>
                        @foreach($teachers as $teacher)
                        <option value="{{ $teacher['id'] }}">{{ $teacher['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                @if(isset($trainees) && count($trainees) > 0)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Assign Trainees</label>
                    <div class="space-y-2 max-h-32 overflow-y-auto border border-gray-300 rounded-lg p-3">
                        @foreach($trainees as $trainee)
                        <label class="flex items-center">
                            <input type="checkbox" name="trainees[]" value="{{ $trainee['id'] }}" class="mr-2 rounded text-blue-600">
                            <span class="text-sm">{{ $trainee['name'] }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <button type="button" @click="showAddModal = false" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-2 gradient-bg text-white rounded-lg hover:opacity-90 transition-opacity">
                        Create Activity
                    </button>
                </div>
            </form>
        </div>
    </div>
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

    .gradient-border {
        position: relative;
        background: white;
    }

    .gradient-border::before {
        content: '';
        position: absolute;
        inset: -2px;
        border-radius: inherit;
        padding: 2px;
        background: var(--primary-gradient);
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
    }

    .hover-scale {
        transition: all 0.3s ease;
    }

    .hover-scale:hover {
        transform: scale(1.02);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .skeleton-loader {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: loading 1.5s infinite;
    }

    @keyframes loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }

    .float-animation {
        animation: float 3s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }
</style>
@endsection

@section('scripts')
<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endsection