// Header functionality
document.addEventListener("DOMContentLoaded", function () {
    // Mobile menu toggle
    const mobileNavToggle = document.getElementById("mobile-nav-toggle");
    const mobileNav = document.getElementById("mobile-nav");

    if (mobileNavToggle && mobileNav) {
        mobileNavToggle.addEventListener("click", function () {
            mobileNav.classList.toggle("mobile-nav-active");
        });
    }

    // Header scrolling effect
    const header = document.getElementById("header");

    window.addEventListener("scroll", function () {
        if (window.scrollY > 50) {
            header.classList.add("header-scrolled");
        } else {
            header.classList.remove("header-scrolled");
        }
    });

    // Close mobile menu when clicking outside
    document.addEventListener("click", function (e) {
        if (
            mobileNav &&
            mobileNav.classList.contains("mobile-nav-active") &&
            !mobileNav.contains(e.target) &&
            e.target !== mobileNavToggle &&
            !mobileNavToggle.contains(e.target)
        ) {
            mobileNav.classList.remove("mobile-nav-active");
        }
    });
});
