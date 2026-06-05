let selectedDraftId = null;

document.addEventListener('DOMContentLoaded', function() {
    
    const saveBtn = document.getElementById('saveDraft');
    if (saveBtn) {
        saveBtn.addEventListener('click', function(e) {
            
            e.preventDefault();
            
            const title = document.getElementById('title').value.trim();
            const content = document.getElementById('content').value.trim();
            const description = document.getElementById('description').value;
            const author = document.getElementById('author').value;
            const publish_as = document.querySelector('input[name="publish_as"]:checked').value;
            const adult = document.querySelector('input[name="adult"]:checked').value;
            
            if (!title || !content) {
                alert('Для сохранения черновика нужны название и текст стихотворения.');
                return;
            }
            
            const draft = {
                id: Date.now(),
                title: title,
                content: content,
                description: description,
                author: author,
                publish_as: publish_as,
                adult: adult,
                timestamp: new Date().toLocaleString()
            };
            
            let drafts = getDrafts();
            
            drafts.unshift(draft);
            
            if (drafts.length > 5) {
                drafts = drafts.slice(0, 5);
            }
            
            localStorage.setItem(STORAGE_KEY, JSON.stringify(drafts));
            
            displayDrafts();
        });
    }
    
    const loadBtn = document.getElementById('loadDraftBtn');
    if (loadBtn) {
        loadBtn.addEventListener('click', function() {
            if (!selectedDraftId) {
                alert('Сначала выберите черновик.');
                return;
            }
            
            const drafts = getDrafts();
            const draft = drafts.find(d => d.id === selectedDraftId);
            
            if (!draft) {
                alert('Черновик не найден!');
                return;
            }
            
            document.getElementById('title').value = draft.title || '';
            document.getElementById('content').value = draft.content || '';
            document.getElementById('description').value = draft.description || '';
            document.getElementById('author').value = draft.author || '';
            
            const radio = document.querySelector(`input[name="publish_as"][value="${draft.publish_as}"]`);
            if (radio) radio.checked = true;
            const radio2 = document.querySelector(`input[name="adult"][value="${draft.adult}"]`);
            if (radio2) radio2.checked = true;
            
        });
    }
    
    const deleteBtn = document.getElementById('deleteDraftBtn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function() {
            if (!selectedDraftId) {
                alert('Сначала выберите черновик.');
                return;
            }
            
            let drafts = getDrafts();
            drafts = drafts.filter(draft => draft.id !== selectedDraftId);
            localStorage.setItem(STORAGE_KEY, JSON.stringify(drafts));
            
            selectedDraftId = null;
            displayDrafts();
        });
    }
    
    const clearAllBtn = document.getElementById('clearAllDraftsBtn');
    if (clearAllBtn) {
        clearAllBtn.addEventListener('click', function() {
            if (confirm('Все черновики будут удалены. Продолжить?')) {
                localStorage.removeItem(STORAGE_KEY);
                selectedDraftId = null;
                displayDrafts();
                alert('Все черновики удалены!');
            }
        });
    }
    
    function getDrafts() {
        const draftsJson = localStorage.getItem(STORAGE_KEY);
        if (!draftsJson) {
            return [];
        }
        return JSON.parse(draftsJson);
    }
    
    function displayDrafts() {
        const container = document.getElementById('draftsList');
        if (!container) return;
        
        const drafts = getDrafts();
        
        if (drafts.length === 0) {
            container.innerHTML = '<p class="no-drafts">Нет сохранённых черновиков</p>';
            return;
        }
        
        let html = '';
        drafts.forEach(draft => {
            const title = draft.title || 'Без названия';
            const description = draft.description || 'Без описания';
            const isChecked = selectedDraftId === draft.id ? 'checked' : '';
            
            html += `
                <div class="draft-item">
                    <label style="display: block; cursor: pointer;">
                        <input type="radio" name="draft_select" value="${draft.id}" ${isChecked}>
                        <strong>${safeHtml(title)}</strong>
                        <div class="draft-description">${safeHtml(description)}</div>
                        <div class="draft-date">${draft.timestamp}</div>
                    </label>
                </div>
            `;
        });
        
        container.innerHTML = html;
        
        document.querySelectorAll('input[name="draft_select"]').forEach(radio => {
            radio.addEventListener('change', function() {
                selectedDraftId = parseInt(this.value);
            });
        });
    }
    
    function safeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    displayDrafts();
});