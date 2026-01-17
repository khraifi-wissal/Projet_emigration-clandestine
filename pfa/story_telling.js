// Navbar Scroll Effect
const navbar = document.getElementById('mainNav');
window.addEventListener('scroll', () => {
    if (window.scrollY > 50) {
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }
});

// Modal Logic
function openStoryModal() { 
    document.getElementById('storyModal').classList.add('active'); 
    document.body.style.overflow = 'hidden'; 
}

function closeStoryModal() { 
    document.getElementById('storyModal').classList.remove('active'); 
    document.body.style.overflow = 'auto'; 
}

document.getElementById('storyModal').addEventListener('click', function(e) { 
    if (e.target === this) { 
        closeStoryModal(); 
    } 
});

// Theme Toggle Logic
const themeToggle = document.getElementById('theme-toggle');
const icon = themeToggle.querySelector('i');

// 1. Initialize Theme on Load
const savedTheme = localStorage.getItem('theme') || 'dark';
document.documentElement.setAttribute('data-bs-theme', savedTheme);
updateIcon(savedTheme);

// 2. Listen for Click
themeToggle.addEventListener('click', (e) => {
    e.preventDefault();
    const currentTheme = document.documentElement.getAttribute('data-bs-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    document.documentElement.setAttribute('data-bs-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    updateIcon(newTheme);
});

// 3. Update Icon Function (Sun for Light, Moon for Dark)
function updateIcon(theme) { 
    // Remove both classes first to be safe
    icon.classList.remove('fa-sun', 'fa-moon');
    
    if (theme === 'light') {
        icon.classList.add('fa-sun'); // Light Mode = Sun Icon
    } else {
        icon.classList.add('fa-moon'); // Dark Mode = Moon Icon
    }
}
// --- VOICE TO TEXT LOGIC ---
const micBtn = document.getElementById('micBtn');
const micStatus = document.getElementById('micStatus');
const storyTextarea = document.getElementById('storyTextarea');

if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    const recognition = new SpeechRecognition();
    recognition.continuous = false;
    recognition.lang = 'fr-FR';
    recognition.interimResults = false;

    micBtn.addEventListener('click', () => {
        if (micBtn.classList.contains('recording')) {
            recognition.stop();
        } else {
            recognition.start();
        }
    });

    recognition.onstart = () => {
        micBtn.classList.add('recording');
        micStatus.style.display = 'block';
        micStatus.textContent = "Parlez maintenant...";
    };

    recognition.onend = () => {
        micBtn.classList.remove('recording');
        micStatus.style.display = 'none';
    };

    recognition.onresult = (event) => {
        const transcript = event.results[0][0].transcript;
        typeWriterEffect(storyTextarea, transcript);
    };
} else {
    micBtn.style.display = 'none'; // Hide if not supported
}

function typeWriterEffect(element, text, speed = 30) {
    let i = 0;
    const currentVal = element.value ? element.value + " " : "";
    element.value = currentVal;
    element.focus();
    
    function type() {
        if (i < text.length) {
            element.value += text.charAt(i);
            element.scrollTop = element.scrollHeight;
            i++;
            setTimeout(type, speed);
        }
    }
    type();
}