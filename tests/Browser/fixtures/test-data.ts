/**
 * CREAMS Test Data Configuration
 *
 * Canonical seeded UAT accounts (UATSeeder) — the same users
 * global-setup.ts authenticates with. The previous
 * TestingGuideDataSeeder users no longer exist in the database.
 *
 * NOTE: interactive logins share the per-IP throttle bucket
 * (throttle:login). For full-suite runs set RATE_LIMIT_LOGIN=100
 * in .env, or runs will start failing after 5 logins per minute.
 */

export const TEST_USERS = {
  admin: {
    email: 'super.admin@uat.creams.test',
    password: 'UatPass2026!',
    role: 'admin',
    dashboard: '/admin/dashboard',
    name: 'UAT Super Admin',
    displayRole: 'Administration'
  },
  supervisor: {
    email: 'supervisor.a1@uat.creams.test',
    password: 'UatPass2026!',
    role: 'supervisor',
    dashboard: '/supervisor/dashboard',
    name: 'UAT Supervisor A1',
    displayRole: 'Supervisor'
  },
  teacher: {
    email: 'teacher.a1@uat.creams.test',
    password: 'UatPass2026!',
    role: 'teacher',
    dashboard: '/teacher/dashboard',
    name: 'UAT Teacher A1',
    displayRole: 'Teacher'
  },
  ajk: {
    email: 'ajk.a1@uat.creams.test',
    password: 'UatPass2026!',
    role: 'ajk',
    dashboard: '/ajk/dashboard',
    name: 'UAT Ajk A1',
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
  assetParents: '/centre/assets',
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
