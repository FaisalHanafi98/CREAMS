/**
 * Avatar Fix Script
 * 
 * This script fixes issues with avatars not loading properly.
 */

document.addEventListener('DOMContentLoaded', function() {
    // Fix for avatar images
    function fixAvatarImages() {
        const avatarImages = document.querySelectorAll('.avatar-img, .profile-img, .rounded-circle[src*="profile"], .user-avatar img');
        
        avatarImages.forEach(function(img) {
            // Check if src is empty, null, or undefined
            if (!img.getAttribute('src') || img.getAttribute('src') === '') {
                console.log('Avatar with empty src found, applying default image');
                applyDefaultImage(img);
            }
            
            // Add error handler for loading failures
            img.addEventListener('error', function(e) {
                console.log('Avatar image failed to load, applying default image');
                applyDefaultImage(this);
            });
        });
        
        function applyDefaultImage(imgElement) {
            imgElement.src = '/images/default-avatar.png';
            // Also set as background in case img element is styled with size but content doesn't load
            imgElement.style.backgroundImage = "url('/images/default-avatar.png')";
            imgElement.style.backgroundSize = "cover";
            imgElement.style.backgroundPosition = "center";
        }
    }
    
    // Run immediately
    fixAvatarImages();
    
    // Also run after a short delay to catch dynamically loaded elements
    setTimeout(fixAvatarImages, 500);
    setTimeout(fixAvatarImages, 1500);
    
    // Fix navbar avatar
    function fixNavbarAvatar() {
        const navbarAvatar = document.querySelector('.user-avatar img');
        if (navbarAvatar) {
            if (!navbarAvatar.getAttribute('src') || navbarAvatar.getAttribute('src') === '') {
                navbarAvatar.src = '/images/default-avatar.png';
            }
            
            navbarAvatar.addEventListener('error', function() {
                this.src = '/images/default-avatar.png';
            });
        }
    }
    
    // Run navbar avatar fix
    fixNavbarAvatar();
});