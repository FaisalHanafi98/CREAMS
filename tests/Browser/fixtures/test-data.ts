/**
 * CREAMS Test Data Configuration
 *
 * These credentials are from TestingGuideDataSeeder.php
 * and verified working via Playwright MCP on 2026-01-25
 */

export const TEST_USERS = {
  admin: {
    email: 'lakshmi.krishnan@iium.edu.my',
    password: 'Admin@2024!',
    role: 'admin',
    dashboard: '/admin/dashboard',
    name: 'Dr. Lakshmi a/p Krishnan',
    displayRole: 'Administration'
  },
  supervisor: {
    email: 'supervisor.gombak@iium.edu.my',
    password: 'Supervise@2024',
    role: 'supervisor',
    dashboard: '/supervisor/dashboard',
    name: 'Dr. Aminah binti Mohd Said',
    displayRole: 'Supervisor'
  },
  teacher: {
    email: 'ahmad.hassan@iium.edu.my',
    password: 'Teacher@2024',
    role: 'teacher',
    dashboard: '/teacher/dashboard',
    name: 'Ustaz Ahmad bin Hassan',
    displayRole: 'Teacher'
  },
  ajk: {
    email: 'fatimah.abdullah@iium.edu.my',
    password: 'AJK@2024',
    role: 'ajk',
    dashboard: '/ajk/dashboard',
    name: 'Siti Fatimah binti Abdullah',
    displayRole: 'AJK'
  }
} as const;

export type UserRole = keyof typeof TEST_USERS;

export const BASE_URL = 'http://localhost:8000';

export const ROUTES = {
  login: '/login',
  home: '/',
  forgotPassword: '/forgot-password',

  // Admin routes
  adminDashboard: '/admin/dashboard',
  adminUsers: '/admin/users',
  adminCentres: '/admin/centres',
  adminVolunteers: '/admin/volunteers',

  // Supervisor routes
  supervisorDashboard: '/supervisor/dashboard',
  supervisorUsers: '/supervisor/users',
  supervisorCentres: '/supervisor/centres',

  // Teacher routes
  teacherDashboard: '/teacher/dashboard',
  teacherCentres: '/teacher/centres',

  // AJK routes
  ajkDashboard: '/ajk/dashboard',
  ajkCentres: '/ajk/centres',

  // Shared routes
  profile: '/profile',
  traineesHome: '/trainees/home',
  traineesCreate: '/trainees/create',
  activitiesHome: '/activities/home',
  activitiesCategories: '/activities/categories',
  activitiesSchedule: '/activities/schedule',
  activitiesCreate: '/activities/create',
  centreAttendance: '/centres/attendance',
  assetParents: '/centre/asset-parents',
} as const;

export const SELECTORS = {
  // Login page
  login: {
    identifier: '#identifier',
    password: '#password',
    submitButton: '.login-btn',
    rememberCheckbox: '#remember',
    forgotPasswordLink: 'a[href*="forgot-password"]',
    errorAlert: '.alert-danger',
    successAlert: '.alert-success',
  },

  // Dashboard/Navigation
  navigation: {
    logoutButton: '.logout-btn',
    sidebarMenu: '.sidebar-link',
    searchInput: 'input[placeholder="Search..."]',
    userAvatar: 'img[alt="User Avatar"]',
    notificationBell: '.notification-bell',
  },

  // Dashboard content
  dashboard: {
    welcomeHeading: 'h1:has-text("Welcome back")',
    statsCard: '.stat-card, [class*="stat"]',
    quickActions: '[class*="quick-action"]',
    scheduleSection: '[class*="schedule"]',
  },

  // Toast notifications
  toast: {
    success: '.toast-success',
    error: '.toast-error',
    warning: '.toast-warning',
    info: '.toast-info',
    message: '.toast-message',
  },

  // Common form elements
  form: {
    submitButton: 'button[type="submit"], input[type="submit"]',
    cancelButton: 'button:has-text("Cancel"), a:has-text("Cancel")',
    invalidField: '.is-invalid',
    invalidFeedback: '.invalid-feedback',
    validFeedback: '.valid-feedback',
  },

  // Table elements
  table: {
    container: 'table',
    header: 'thead',
    body: 'tbody',
    row: 'tbody tr',
    viewButton: 'a:has-text("View")',
    editButton: 'a:has-text("Edit")',
    deleteButton: 'button:has-text("Delete"), a:has-text("Delete")',
    searchInput: 'input[type="search"], #search',
  },
} as const;

/**
 * Performance thresholds for functional tests (in milliseconds)
 */
export const PERFORMANCE_THRESHOLDS = {
  pageLoad: 5000,           // Max page load time
  formSubmission: 10000,    // Max form submission time
  searchOperation: 3000,    // Max search operation time
  fullCrudCycle: 30000,     // Max time for complete CRUD cycle
} as const;

/**
 * Test configuration
 */
export const TEST_CONFIG = {
  defaultTimeout: 30000,
  navigationTimeout: 15000,
  waitForNetworkIdle: true,
  screenshotOnFailure: true,
  traceOnFailure: true,
} as const;
