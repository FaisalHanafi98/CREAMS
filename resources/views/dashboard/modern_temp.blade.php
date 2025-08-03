@section('scripts')
<script>
console.log('🔍 DASHBOARD SCRIPT - MINIMAL VERSION FOR DEBUGGING');

// Test basic JavaScript functionality
function testDashboardJS() {
    alert('Dashboard JavaScript is working!');
}

// Simple expand function for Important Updates
function showImportantUpdatesMessage() {
    console.log('showImportantUpdatesMessage function called');
    alert('📋 Important Updates\n\nExpand feature coming soon!\n\nThis will show all notifications in an expanded format when you have more items to display.');
}

console.log('Dashboard script loaded successfully');
</script>
@endsection