// --- Navbar Scroll Logic ---
const navbar = document.getElementById('mainNav');
if (navbar) {
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
}

// --- Theme Toggle ---
const themeBtn = document.getElementById('theme-toggle');
if (themeBtn) {
    const icon = themeBtn.querySelector('i');
    
    // Check saved theme
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-bs-theme', savedTheme);
    icon.className = savedTheme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';

    themeBtn.addEventListener('click', (e) => {
        e.preventDefault();
        const html = document.documentElement;
        const currentTheme = html.getAttribute('data-bs-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        html.setAttribute('data-bs-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        icon.className = newTheme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
    });
}

// --- Voice AI: Speak Question ---
function speakQuestion(id) {
    const textElement = document.getElementById('qtext-' + id);
    if (!textElement) return;

    const text = textElement.innerText;
    if ('speechSynthesis' in window) {
        window.speechSynthesis.cancel(); // Stop any current speech
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = 'fr-FR'; // French
        window.speechSynthesis.speak(utterance);
    } else {
        alert("Synthèse vocale non supportée sur ce navigateur.");
    }
}

// --- Voice AI: Listen for Answer ---
function listenAnswer(id) {
    if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
        alert("Reconnaissance vocale non supportée."); return;
    }
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    const recognition = new SpeechRecognition();
    const micBtn = document.getElementById('mic-' + id);

    recognition.lang = 'fr-FR';
    recognition.interimResults = false;
    recognition.maxAlternatives = 1;

    recognition.onstart = () => {
        if(micBtn) micBtn.classList.add('listening');
    };
    recognition.onend = () => {
        if(micBtn) micBtn.classList.remove('listening');
    };

    recognition.onresult = (event) => {
        const transcript = event.results[0][0].transcript.toLowerCase();
        let selected = null;
        
        // Map spoken words to options
        if (transcript.includes('a') || transcript.includes('première')) selected = 'A';
        else if (transcript.includes('b') || transcript.includes('deuxième')) selected = 'B';
        else if (transcript.includes('c') || transcript.includes('troisième')) selected = 'C';
        else if (transcript.includes('d') || transcript.includes('quatrième')) selected = 'D';

        if (selected) {
            const radio = document.getElementById('opt-' + id + '-' + selected);
            if (radio) { 
                radio.checked = true; 
                radio.focus(); 
            }
        } else {
            alert("Je n'ai pas compris. Dites 'A', 'B', 'C' ou 'D'.");
        }
    };
    recognition.start();
}