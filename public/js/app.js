/**
 * AZ-305 Helper - Main Application JavaScript
 */

class AZ305App {
    constructor() {
        this.apiBase = '/api';
        this.currentView = 'home';
        this.domains = [];
        this.currentSession = null;
        this.currentQuestionIndex = 0;
        this.sessionAnswers = [];
        this.startTime = null;
        
        this.init();
    }

    async init() {
        this.setupEventListeners();
        
        // Test API connectivity first
        console.log('Testing API connectivity...');
        try {
            const debugResponse = await fetch(`${this.apiBase}/debug`);
            const debugData = await debugResponse.json();
            console.log('API Debug Info:', debugData);
        } catch (e) {
            console.error('Debug endpoint error:', e);
        }
        
        await this.loadDomains();
        this.renderDomainsList();
    }

    setupEventListeners() {
        // Navigation
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', (e) => this.handleNavigation(e));
        });

        // Home View
        document.getElementById('start-session-btn').addEventListener('click', () => this.showView('setup'));

        // Setup View
        document.getElementById('create-session-btn').addEventListener('click', () => this.createSession());
        document.getElementById('back-home-btn').addEventListener('click', () => this.showView('home'));

        // Quiz View
        document.getElementById('prev-question-btn').addEventListener('click', () => this.previousQuestion());
        document.getElementById('next-question-btn').addEventListener('click', () => this.nextQuestion());
        document.getElementById('submit-session-btn').addEventListener('click', () => this.submitSession());

        // Results View
        document.getElementById('new-session-btn').addEventListener('click', () => this.showView('setup'));
        document.getElementById('home-btn').addEventListener('click', () => this.showView('home'));

        // About
        document.getElementById('about-back-btn').addEventListener('click', () => this.showView('home'));
    }

    handleNavigation(e) {
        e.preventDefault();
        const view = e.target.dataset.view;
        this.showView(view);
    }

    showView(viewName) {
        // Hide all views
        document.querySelectorAll('.view').forEach(view => {
            view.classList.remove('active');
        });

        // Update navigation
        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('active');
            if (link.dataset.view === viewName) {
                link.classList.add('active');
            }
        });

        // Show selected view
        const view = document.getElementById(`${viewName}-view`);
        if (view) {
            view.classList.add('active');
            this.currentView = viewName;

            if (viewName === 'setup') {
                this.renderDomainsCheckboxes();
            }
        }
    }

    async loadDomains() {
        try {
            console.log('Fetching domains from:', `${this.apiBase}/domains`);
            const response = await fetch(`${this.apiBase}/domains`);
            
            console.log('Response status:', response.status);
            console.log('Response ok:', response.ok);
            
            if (!response.ok) {
                const errorData = await response.text();
                console.error('API response:', errorData);
                throw new Error(`API error: ${response.status}`);
            }
            
            this.domains = await response.json();
            console.log('Domains loaded:', this.domains);
            console.log('Domains type:', typeof this.domains);
            console.log('Domains is array:', Array.isArray(this.domains));
            
            // Ensure domains is an array
            if (!Array.isArray(this.domains)) {
                console.error('Domains response is not an array:', this.domains);
                this.domains = [];
            }
        } catch (error) {
            console.error('Error loading domains:', error);
            this.domains = [];
        }
    }

    renderDomainsList() {
        const domainsList = document.getElementById('domains-list');
        domainsList.innerHTML = '';

        if (!this.domains || this.domains.length === 0) {
            const errorMsg = 'Error: Unable to load domains. Please refresh the page.';
            domainsList.innerHTML = `<p style="color: #da3b01;">${errorMsg}</p>`;
            console.warn('Domains array is empty or undefined. Domains:', this.domains);
            return;
        }

        this.domains.forEach(domain => {
            const card = document.createElement('div');
            card.className = 'domain-card';
            card.innerHTML = `
                <h4>${domain.name}</h4>
                <p>${domain.description}</p>
                <div class="domain-percentage">${domain.percentage}% of exam</div>
            `;
            domainsList.appendChild(card);
        });
    }

    renderDomainsCheckboxes() {
        const container = document.getElementById('domains-checkboxes');
        container.innerHTML = '';

        if (!this.domains || this.domains.length === 0) {
            container.innerHTML = '<p style="color: #da3b01;">Error: Unable to load domains. Please refresh the page.</p>';
            console.warn('Domains array is empty or undefined');
            return;
        }

        this.domains.forEach(domain => {
            const item = document.createElement('div');
            item.className = 'checkbox-item';
            item.innerHTML = `
                <input type="checkbox" id="domain-${domain.id}" value="${domain.id}">
                <label for="domain-${domain.id}">${domain.name}</label>
            `;
            container.appendChild(item);
        });
    }

    async createSession() {
        const questionCount = parseInt(document.getElementById('question-count').value);
        const selectedDomains = Array.from(
            document.querySelectorAll('#domains-checkboxes input[type="checkbox"]:checked')
        ).map(cb => cb.value);

        if (questionCount < 5 || questionCount > 50) {
            alert('Please select between 5 and 50 questions');
            return;
        }

        try {
            const response = await fetch(`${this.apiBase}/session/create`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    question_count: questionCount,
                    domains: selectedDomains.length > 0 ? selectedDomains : null
                })
            });

            if (!response.ok) {
                throw new Error(`API error: ${response.status}`);
            }

            const session = await response.json();
            console.log('Session created:', session);
            
            if (session && session.id) {
                this.currentSession = session;
                this.currentQuestionIndex = 0;
                this.sessionAnswers = new Array(session.questions.length).fill(null);
                this.startTime = Date.now();
                this.showView('quiz');
                this.displayQuestion();
            } else {
                alert('Error creating session: Invalid response');
            }
        } catch (error) {
            console.error('Error creating session:', error);
            alert('Error creating session: ' + error.message);
        }
    }

    async loadSession(sessionId) {
        try {
            const response = await fetch(`${this.apiBase}/session/get/${sessionId}`);
            this.currentSession = await response.json();
            this.currentQuestionIndex = 0;
            this.sessionAnswers = new Array(this.currentSession.questions.length).fill(null);
            this.displayQuestion();
        } catch (error) {
            console.error('Error loading session:', error);
            alert('Error loading session');
        }
    }

    displayQuestion() {
        const question = this.currentSession.questions[this.currentQuestionIndex];
        if (!question) return;

        // Update progress
        const progress = ((this.currentQuestionIndex + 1) / this.currentSession.questions.length) * 100;
        document.getElementById('progress-fill').style.width = progress + '%';
        document.getElementById('question-counter').textContent = 
            `Question ${this.currentQuestionIndex + 1} of ${this.currentSession.questions.length}`;

        // Display elapsed time
        const elapsed = Math.floor((Date.now() - this.startTime) / 1000);
        const minutes = Math.floor(elapsed / 60);
        const seconds = elapsed % 60;
        document.getElementById('session-timer').textContent = 
            `Time: ${minutes}m ${seconds.toString().padStart(2, '0')}s`;

        // Update domain badge
        const domainName = this.domains.find(d => d.id === question.domain)?.name || question.domain;
        document.getElementById('domain-badge').textContent = domainName;

        // Display question
        document.getElementById('question-text').textContent = question.question;

        // Display alternatives
        const alternativesContainer = document.getElementById('alternatives-container');
        alternativesContainer.innerHTML = '';

        question.alternatives.forEach((alt, index) => {
            const label = document.createElement('label');
            label.className = 'alternative';
            if (this.sessionAnswers[this.currentQuestionIndex] === index) {
                label.classList.add('selected');
            }

            label.innerHTML = `
                <input type="radio" name="alternative" value="${index}" 
                    ${this.sessionAnswers[this.currentQuestionIndex] === index ? 'checked' : ''}>
                <span>${alt.text}</span>
            `;

            label.addEventListener('change', (e) => {
                document.querySelectorAll('.alternative').forEach(alt => {
                    alt.classList.remove('selected');
                });
                label.classList.add('selected');
                this.sessionAnswers[this.currentQuestionIndex] = index;
            });

            alternativesContainer.appendChild(label);
        });

        // Update button states
        document.getElementById('prev-question-btn').disabled = this.currentQuestionIndex === 0;
        const isLastQuestion = this.currentQuestionIndex === this.currentSession.questions.length - 1;
        document.getElementById('next-question-btn').style.display = isLastQuestion ? 'none' : 'block';
        document.getElementById('submit-session-btn').style.display = isLastQuestion ? 'block' : 'none';
    }

    previousQuestion() {
        if (this.currentQuestionIndex > 0) {
            this.currentQuestionIndex--;
            this.displayQuestion();
        }
    }

    nextQuestion() {
        if (this.currentQuestionIndex < this.currentSession.questions.length - 1) {
            this.currentQuestionIndex++;
            this.displayQuestion();
        }
    }

    async submitSession() {
        if (confirm('Are you sure you want to submit your answers? You cannot change them after submission.')) {
            try {
                const response = await fetch(
                    `${this.apiBase}/session/submit/${this.currentSession.id}`,
                    {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ answers: this.sessionAnswers })
                    }
                );

                const results = await response.json();
                this.displayResults(results);
                this.showView('results');
            } catch (error) {
                console.error('Error submitting session:', error);
                alert('Error submitting answers');
            }
        }
    }

    displayResults(results) {
        // Overall score
        document.getElementById('overall-percentage').textContent = results.overall.percentage + '%';
        document.getElementById('overall-grade').textContent = results.overall.grade;
        document.getElementById('score-summary').textContent = 
            `You answered ${results.overall.correct} out of ${results.overall.total} questions correctly.`;

        // Domain results
        const domainResults = document.getElementById('domain-results-list');
        domainResults.innerHTML = '';

        Object.keys(results.by_domain).forEach(domainId => {
            const stats = results.by_domain[domainId];
            const card = document.createElement('div');
            card.className = 'domain-result-card';
            card.innerHTML = `
                <h4>${stats.name}</h4>
                <div class="domain-result-stats">
                    Correct: ${stats.correct}/${stats.total}
                </div>
                <div class="domain-result-percentage">
                    Score: ${stats.percentage}%
                </div>
            `;
            domainResults.appendChild(card);
        });

        // Detailed results
        const detailedList = document.getElementById('detailed-list');
        detailedList.innerHTML = '';

        results.details.forEach(detail => {
            const item = document.createElement('div');
            item.className = `result-item ${detail.is_correct ? 'correct' : 'incorrect'}`;

            const status = detail.is_correct ? 'Correct' : 'Incorrect';
            const statusClass = detail.is_correct ? 'correct' : 'incorrect';

            let answersHtml = '';
            if (detail.user_answer) {
                answersHtml += `
                    <div class="result-answer">
                        <strong>Your answer:</strong> 
                        <span class="${statusClass}">${detail.user_answer}</span>
                    </div>
                `;
            }
            if (!detail.is_correct) {
                answersHtml += `
                    <div class="result-answer">
                        <strong>Correct answer:</strong> 
                        <span class="correct-answer">${detail.correct_answer}</span>
                    </div>
                `;
            }

            let referencesHtml = '';
            if (detail.references && detail.references.length > 0) {
                referencesHtml = '<div class="references">';
                referencesHtml += '<div class="references-title">Learn more:</div>';
                detail.references.forEach(ref => {
                    referencesHtml += `<div class="reference-link"><a href="${ref}" target="_blank">📚 Microsoft Learn</a></div>`;
                });
                referencesHtml += '</div>';
            }

            item.innerHTML = `
                <div class="result-item-header">
                    <span class="result-badge ${statusClass}">${status}</span>
                    <span class="domain-badge">${this.getDomainName(detail.domain)}</span>
                </div>
                <div class="result-question">Q${detail.question_index + 1}: ${detail.question}</div>
                ${answersHtml}
                <div class="explanation">
                    <strong>Explanation:</strong> ${detail.explanation}
                </div>
                ${referencesHtml}
            `;

            detailedList.appendChild(item);
        });
    }

    getDomainName(domainId) {
        const domain = this.domains.find(d => d.id === domainId);
        return domain ? domain.name : domainId;
    }
}

// Initialize app when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.app = new AZ305App();
});
