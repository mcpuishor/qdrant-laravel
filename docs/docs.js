document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const sections = document.querySelectorAll('.section');
    const navLinks = document.querySelectorAll('.nav-link');
    const sidebar = document.querySelector('.sidebar');
    const menuToggle = document.getElementById('menu-toggle');

    // Toggle sidebar on mobile
    menuToggle.addEventListener('click', function() {
        sidebar.classList.toggle('active');
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        if (window.innerWidth <= 992 &&
            !sidebar.contains(event.target) &&
            event.target !== menuToggle) {
            sidebar.classList.remove('active');
        }
    });

    // Smooth scroll for navigation links
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();

            // Get the target section
            const targetId = this.getAttribute('href');
            const targetSection = document.querySelector(targetId);

            // Scroll to the target section
            window.scrollTo({
                top: targetSection.offsetTop - 20,
                behavior: 'smooth'
            });

            // Close sidebar on mobile after clicking a link
            if (window.innerWidth <= 992) {
                sidebar.classList.remove('active');
            }
        });
    });

    // Scroll spy functionality
    function updateActiveLink() {
        let currentSection = '';

        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;

            if (window.scrollY >= sectionTop - 100) {
                currentSection = section.getAttribute('id');
            }
        });

        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === `#${currentSection}`) {
                link.classList.add('active');
            }
        });
    }

    // Initial call to set active link on page load
    updateActiveLink();

    // Update active link on scroll
    window.addEventListener('scroll', updateActiveLink);

    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 992) {
            sidebar.classList.remove('active');
        }
    });

    // Add basic styling to code blocks without syntax highlighting
    const style = document.createElement('style');
    style.textContent = `
        pre {
            background-color: #f5f5f5;
            border-radius: 4px;
            padding: 1rem;
            margin: 1em 0;
            overflow: auto;
            border: 1px solid #e0e0e0;
        }
        pre code {
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
            font-size: 14px;
            white-space: pre;
            color: #00008B; /* Dark blue text as requested */
            display: block;
            line-height: 1.5;
        }
    `;
    document.head.appendChild(style);
});
