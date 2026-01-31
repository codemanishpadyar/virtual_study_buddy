document.addEventListener("DOMContentLoaded", function() {
    const buttons = document.querySelectorAll("button");

    // Button hover effect
    buttons.forEach(button => {
        button.addEventListener("mouseover", () => {
            button.style.transform = "translateY(-3px)";
        });
        button.addEventListener("mouseout", () => {
            button.style.transform = "translateY(0)";
        });
    });
    const toggle = document.getElementById("darkModeToggle");
    toggle.addEventListener("click", () => {
        document.body.classList.toggle("dark-mode");
    });
    
    // Fade-in effect for elements
    document.querySelectorAll(".fade-in").forEach(el => {
        el.style.opacity = "0";
        el.style.transition = "opacity 1.5s ease-in-out";
        setTimeout(() => {
            el.style.opacity = "1";
        }, 500);
    });
});
