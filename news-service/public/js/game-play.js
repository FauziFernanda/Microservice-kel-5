let currentQuestionIndex = 0;
let questions = [];
let players = [];
let scores = {};
let timer;
let timeLeft = 30;
let gameStarted = false;
let gameType = '';
let userRole = '';

// Initialize game
function initGame(gameData, gameTypeData, userRoleData) {
    questions = gameData.questions;
    players = gameData.players;
    gameType = gameTypeData;
    userRole = userRoleData;
    
    // Initialize scores
    players.forEach(player => {
        scores[player.id] = 0;
    });
    
    if (gameType === 'offline') {
        if (userRole === 'student') {
            loadPlayers(); // Read-only view
            loadQuestionReadOnly();
        } else {
            loadPlayers();
            startGame();
        }
    } else {
        // Online mode
        loadStudents();
        if (userRole === 'student') {
            startGame();
        } else {
            startGame();
        }
    }
}

function startGame() {
    gameStarted = true;
    if (gameType === 'offline' && userRole === 'student') {
        loadQuestionReadOnly();
    } else {
        loadQuestion();
        startTimer();
    }
}

function loadQuestion() {
    if (currentQuestionIndex >= questions.length) {
        finishGame();
        return;
    }

    const question = questions[currentQuestionIndex];
    document.getElementById('questionNumber').textContent = currentQuestionIndex + 1;
    document.getElementById('questionText').textContent = question.question_text;
    
    const optionsContainer = document.getElementById('optionsContainer');
    optionsContainer.innerHTML = '';
    
    if (question.options) {
        question.options.forEach((option, index) => {
            const optionDiv = document.createElement('div');
            optionDiv.className = 'p-3 bg-[#2A2A32] rounded-lg border border-pink-500/20 hover:border-pink-500/40 transition-all cursor-pointer';
            optionDiv.innerHTML = `
                <div class="flex items-center">
                    <span class="w-6 h-6 rounded-full bg-pink-500/20 text-pink-400 text-sm font-medium mr-3 flex items-center justify-center">
                        ${String.fromCharCode(65 + index)}
                    </span>
                    <span class="text-white">${option}</span>
                </div>
            `;
            optionDiv.onclick = () => selectAnswer(index);
            optionsContainer.appendChild(optionDiv);
        });
    }
}

function loadQuestionReadOnly() {
    if (currentQuestionIndex >= questions.length) {
        return;
    }

    const question = questions[currentQuestionIndex];
    document.getElementById('questionNumber').textContent = currentQuestionIndex + 1;
    document.getElementById('questionText').textContent = question.question_text;
    
    const optionsContainer = document.getElementById('optionsContainer');
    optionsContainer.innerHTML = '';
    
    if (question.options) {
        question.options.forEach((option, index) => {
            const optionDiv = document.createElement('div');
            optionDiv.className = 'p-3 bg-[#2A2A32] rounded-lg border border-pink-500/20';
            optionDiv.innerHTML = `
                <div class="flex items-center">
                    <span class="w-6 h-6 rounded-full bg-pink-500/20 text-pink-400 text-sm font-medium mr-3 flex items-center justify-center">
                        ${String.fromCharCode(65 + index)}
                    </span>
                    <span class="text-white">${option}</span>
                </div>
            `;
            optionsContainer.appendChild(optionDiv);
        });
    }
}

function selectAnswer(selectedIndex) {
    const question = questions[currentQuestionIndex];
    const isCorrect = selectedIndex === question.correct_answer;
    
    // Calculate score based on time left
    let points = 0;
    if (isCorrect) {
        points = Math.max(1, Math.floor(timeLeft / 3)); // More points for faster answers
    }
    
    // Highlight correct answer
    const options = document.querySelectorAll('#optionsContainer > div');
    options.forEach((option, index) => {
        if (index === question.correct_answer) {
            option.classList.add('border-green-500', 'bg-green-500/20');
        } else if (index === selectedIndex && !isCorrect) {
            option.classList.add('border-red-500', 'bg-red-500/20');
        }
    });
    
    // Show result
    const resultDiv = document.createElement('div');
    resultDiv.className = `mt-4 p-4 rounded-lg ${isCorrect ? 'bg-green-500/20 border border-green-500' : 'bg-red-500/20 border border-red-500'}`;
    resultDiv.innerHTML = `
        <div class="flex items-center">
            <i class="fi ${isCorrect ? 'fi-rr-check text-green-400' : 'fi-rr-cross text-red-400'} text-xl mr-2"></i>
            <span class="text-white font-medium">${isCorrect ? 'Correct!' : 'Incorrect!'}</span>
            ${isCorrect ? `<span class="ml-2 text-green-400">+${points} points</span>` : ''}
        </div>
    `;
    document.getElementById('questionContainer').appendChild(resultDiv);
    
    // Stop timer
    clearInterval(timer);
    
    // If student in online mode, send score
    if (gameType === 'online' && userRole === 'student') {
        sendStudentScore(points);
    }
    
    // Show next/finish button for teachers
    if (userRole !== 'student') {
        if (currentQuestionIndex < questions.length - 1) {
            document.getElementById('nextButton').style.display = 'block';
        } else {
            document.getElementById('finishButton').style.display = 'block';
        }
    }
}

function sendStudentScore(points) {
    // Send student's score to server
    fetch('/classroom/games/score', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            player_id: window.currentUserId,
            score: points,
            question_index: currentQuestionIndex
        })
    });
}

function nextQuestion() {
    currentQuestionIndex++;
    timeLeft = 30;
    document.getElementById('timer').textContent = timeLeft;
    document.getElementById('nextButton').style.display = 'none';
    document.getElementById('finishButton').style.display = 'none';
    
    // Remove result message
    const resultDiv = document.querySelector('#questionContainer > div:last-child');
    if (resultDiv && resultDiv.classList.contains('mt-4')) {
        resultDiv.remove();
    }
    
    if (gameType === 'offline' && userRole === 'student') {
        loadQuestionReadOnly();
    } else {
        loadQuestion();
        startTimer();
    }
}

function startTimer() {
    clearInterval(timer);
    timer = setInterval(() => {
        timeLeft--;
        document.getElementById('timer').textContent = timeLeft;
        
        if (timeLeft <= 0) {
            clearInterval(timer);
            // Auto-select wrong answer (time's up)
            selectAnswer(-1);
        }
    }, 1000);
}

function scorePlayer(playerId, points) {
    scores[playerId] += points;
    document.getElementById('currentScore').textContent = points;
    
    // Update player score display
    const playerButton = document.querySelector(`[data-player-id="${playerId}"]`);
    if (playerButton) {
        const scoreSpan = playerButton.querySelector('.score');
        scoreSpan.textContent = scores[playerId];
    }
    
    // Send score to server
    fetch('/classroom/games/score', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            player_id: playerId,
            score: points
        })
    });
}

function finishGame() {
    clearInterval(timer);
    window.location.href = window.scoreboardUrl;
}

// Load players/teams
function loadPlayers() {
    const container = document.getElementById('playersContainer');
    if (!container) return;
    
    container.innerHTML = '';
    
    players.forEach(player => {
        const playerDiv = document.createElement('div');
        playerDiv.className = 'p-4 bg-[#2A2A32] rounded-lg border border-pink-500/20 hover:border-pink-500/40 transition-all cursor-pointer';
        playerDiv.setAttribute('data-player-id', player.id);
        
        if (userRole !== 'student') {
            playerDiv.onclick = () => scorePlayer(player.id, 10);
        }
        
        const displayName = player.team ? player.team.name : (player.student_name || player.user?.name || 'Unknown');
        
        playerDiv.innerHTML = `
            <div class="text-center">
                <div class="text-white font-medium mb-2">${displayName}</div>
                <div class="text-gray-400 text-sm mb-2">Score: <span class="score text-pink-400">${scores[player.id]}</span></div>
                ${userRole !== 'student' ? '<button class="px-3 py-1 bg-green-500 text-white rounded text-sm hover:bg-green-600 transition-colors">+10 Points</button>' : ''}
            </div>
        `;
        
        container.appendChild(playerDiv);
    });
}

// Load students for online mode
function loadStudents() {
    const container = document.getElementById('studentsContainer');
    if (!container) return;
    
    container.innerHTML = '';
    
    // Get students from classroom
    const students = window.classroomStudents || [];
    
    students.forEach(student => {
        const studentDiv = document.createElement('div');
        studentDiv.className = 'p-4 bg-[#2A2A32] rounded-lg border border-pink-500/20';
        studentDiv.innerHTML = `
            <div class="text-center">
                <div class="text-white font-medium mb-2">${student.name}</div>
                <div class="text-gray-400 text-sm">Student</div>
            </div>
        `;
        
        container.appendChild(studentDiv);
    });
}

// Make functions globally available
window.initGame = initGame;
window.nextQuestion = nextQuestion;
window.finishGame = finishGame;
window.scorePlayer = scorePlayer; 