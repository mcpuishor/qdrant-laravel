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

    // Add syntax highlighting to code blocks
    const codeBlocks = document.querySelectorAll('pre code');

    // Simple syntax highlighting for PHP code
    function highlightSyntax(code) {
        // Replace PHP keywords with highlighted spans
        const keywords = ['use', 'function', 'return', 'if', 'else', 'foreach', 'echo', 'class', 'public', 'private', 'protected', 'static', 'new', 'true', 'false', 'null', 'array'];

        let html = code.innerHTML;

        // Highlight keywords
        keywords.forEach(keyword => {
            const regex = new RegExp(`\\b${keyword}\\b`, 'g');
            html = html.replace(regex, `<span class="keyword">${keyword}</span>`);
        });

        // Highlight strings
        html = html.replace(/(["'])(.*?)\1/g, '<span class="string">$&</span>');

        // Highlight comments
        html = html.replace(/(\/\/.*)/g, '<span class="comment">$1</span>');

        code.innerHTML = html;
    }

    // Add CSS for syntax highlighting
    const style = document.createElement('style');
    style.textContent = `
        .keyword { color: #007bff; font-weight: bold; }
        .string { color: #28a745; }
        .comment { color: #6c757d; font-style: italic; }
    `;
    document.head.appendChild(style);

    // Apply syntax highlighting to all code blocks
    codeBlocks.forEach(highlightSyntax);
});
