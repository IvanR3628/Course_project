const urlParams = new URLSearchParams(window.location.search);

const urlPoemId = urlParams.get('poem_id');
const urlFilterAuthor = urlParams.get('author');
const urlFilterPublisher = urlParams.get('publisher');

const selectedPoemId = urlPoemId && !isNaN(parseInt(urlPoemId)) ? parseInt(urlPoemId) : null;
const filterAuthor = urlFilterAuthor && urlFilterAuthor.trim() !== '' ? urlFilterAuthor : null;
const filterPublisher = urlFilterPublisher && urlFilterPublisher.trim() !== '' ? urlFilterPublisher : null;

let currentSelectedPoemId = selectedPoemId;

let currentFilters = {
    title: '',
    author: filterAuthor || '',
    publisher: filterPublisher || '',
    description: '',
    sortBy: 'newest'
};

document.addEventListener('DOMContentLoaded', function() {
    
    displayPoemsList();
    
    const filtersContent = document.getElementById('filtersContent');
    
    const searchTitle = document.getElementById('searchTitle');
    searchTitle.addEventListener('input', function() {
        currentFilters.title = this.value;
        displayPoemsList();
    });
    
    const searchAuthor = document.getElementById('searchAuthor');
    searchAuthor.addEventListener('input', function() {
        currentFilters.author = this.value;
        displayPoemsList();
    });
    
    const authorSelect = document.getElementById('authorSelect');
    authorSelect.addEventListener('change', function() {
        currentFilters.author = this.value;
        searchAuthor.value = this.value;
        displayPoemsList();
    });
    
    const searchPublisher = document.getElementById('searchPublisher');
    searchPublisher.addEventListener('input', function() {
        currentFilters.publisher = this.value;
        displayPoemsList();
    });
    
    const publisherSelect = document.getElementById('publisherSelect');
    publisherSelect.addEventListener('change', function() {
        currentFilters.publisher = this.value;
        searchPublisher.value = this.value;
        displayPoemsList();
    });
    
    const searchDescription = document.getElementById('searchDescription');
    searchDescription.addEventListener('input', function() {
        currentFilters.description = this.value;
        displayPoemsList();
    });
    
    const sortBy = document.getElementById('sortBy');
    sortBy.addEventListener('change', function() {
        currentFilters.sortBy = this.value;
        displayPoemsList();
    });
    
    const resetButton = document.getElementById('resetFiltersButton');
    resetButton.addEventListener('click', function() {
        currentFilters = {
            title: '',
            author: '',
            publisher: '',
            description: '',
            sortBy: 'newest'
        };
        searchTitle.value = '';
        searchAuthor.value = '';
        authorSelect.value = '';
        searchPublisher.value = '';
        publisherSelect.value = '';
        searchDescription.value = '';
        sortBy.value = 'newest';
        displayPoemsList();
    });
    
    function getPoemAuthor(poem) {
        
        if (poem.author && poem.author.trim() !== '') {
            return poem.author;
        }
        
        if (poem.anonymity === 'y') {
            return 'Аноним';
        }
        
        if (poem.authorid && allUsers[poem.authorid]) {
            return allUsers[poem.authorid];
        }
        return '?';
    }

    function getPoemPublisher(poem) {
        
        if (poem.anonymity === 'y') {
            if (currentUserId && poem.authorid == currentUserId) {
                if (poem.authorid && allUsers[poem.authorid]) {
                    return `${safeHtml(allUsers[poem.authorid])} (анонимно)`;
                }
            }
            return 'Аноним';
        }

        if (poem.authorid && allUsers[poem.authorid]) {
            return safeHtml(allUsers[poem.authorid]);
        }

        return '?';
    }
    
    
    function displayPoemsList() {
        
        let filteredPoems = allPoems.filter(poem => {
            
            if (currentFilters.title && !poem.title.toLowerCase().includes(currentFilters.title.toLowerCase())) {
                return false;
            }
            
            if (currentFilters.author) {
                const authorName = getPoemAuthor(poem);
                if (authorName.toLowerCase() !== currentFilters.author.toLowerCase()) {
                    return false;
                }
            }
            
            if (currentFilters.publisher) {
                const publisherName = getPoemPublisher(poem);
                if (publisherName.toLowerCase() !== currentFilters.publisher.toLowerCase() && publisherName.toLowerCase().replace(' (анонимно)', '') !== currentFilters.publisher.toLowerCase()) {
                    return false;
                }
            }
            
            if (currentFilters.description && !poem.description.toLowerCase().includes(currentFilters.description.toLowerCase())) {
                return false;
            }
            
            return true;
        });
        
        
        if (currentFilters.sortBy === 'newest') {
            filteredPoems.sort((a, b) => new Date(b.changedate) - new Date(a.changedate));
        } else {
            filteredPoems.sort((a, b) => new Date(a.changedate) - new Date(b.changedate));
        }
        
        const container = document.getElementById('poemsListContainer');
        
        if (filteredPoems.length === 0) {
            container.innerHTML = '<p>Нет стихотворений, соответствующих фильтрам</p>';
            return;
        }
        
        let html = '';
        filteredPoems.forEach(poem => {
            const authorName = getPoemAuthor(poem);
            const publisherName = getPoemPublisher(poem);
            const date = new Date(poem.changedate).toLocaleDateString();
            const isActive = currentSelectedPoemId === poem.id ? 'active' : '';

            html += `
                <div class="poemitem ${isActive}" data-poem-id="${poem.id}">
                    <div>
                        ${safeHtml(poem.title)}
                        ${poem.unsafeage == 'y' ? '<span">(18+)</span>' : ''}
                    </div>
                    <div>
                        <span>${safeHtml(authorName)}</span>
                        <span>| ${safeHtml(publisherName)}</span>
                        <span>| ${date}</span>
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
        
        document.querySelectorAll('.poemitem').forEach(item => {
            item.addEventListener('click', function() {
                const poemId = parseInt(this.dataset.poemId);
                currentSelectedPoemId = poemId;
                displayPoem(poemId);
                displayPoemsList();
            });
        });
    }
    
    function displayPoem(poemId) {
        const poem = allPoems.find(p => p.id === poemId);
        if (!poem) return;

        const container = document.getElementById('poemViewContent');

        const authorName = getPoemAuthor(poem);
        const publisherName = getPoemPublisher(poem);
        const date = new Date(poem.changedate).toLocaleString();
        
        const safeTitle = safeHtml(poem.title);
        const safeContent = safeHtml(poem.content).replace(/\n/g, '<br>');
        const safeDescription = poem.description ? safeHtml(poem.description).replace(/\n/g, '<br>') : '';
        const safeAuthor = safeHtml(authorName);
        const safePublisher = safeHtml(publisherName);
        
        let deleteButtonHtml = '';
        if (isAdmin === "y" || (currentUserId && poem.authorid == currentUserId)) {
            deleteButtonHtml = `
                <form method="POST" onsubmit="return confirm('Вы уверены, что хотите удалить это стихотворение?');">
                    <input type="hidden" name="poem_id" value="${poem.id}">
                    <button type="submit" name="delete_poem">
                        Удалить стихотворение
                    </button>
                </form>
            `;
        }

        container.innerHTML = `
            <h2>${safeTitle}</h2>
            ${poem.unsafeage == 'y' ? '<div>Контент для взрослых (18+)</div><br>' : ''}
            <div>${safeContent}</div>
            <div>
                ${poem.description ? `<p><strong>Описание:</strong> ${safeDescription}</p>` : '<p><strong>Нет описания</strong></p>'}
                <p><strong>Автор оригинала:</strong> ${safeAuthor}</p>
                <p><strong>Опубликовал:</strong> ${safePublisher}</p>
                <p><strong>Дата публикации:</strong> ${date}</p>
            </div>
            ${deleteButtonHtml}
        `;
    }
    
    function safeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    if (filterAuthor) {
        const authorSelect = document.getElementById('authorSelect');
        authorSelect.addEventListener('change', function() {
            currentFilters.author = this.value;
            searchAuthor.value = this.value;
            displayPoemsList();
        });
        
        const publisherSelect = document.getElementById('publisherSelect');
        publisherSelect.addEventListener('change', function() {
            currentFilters.publisher = this.value;
            searchPublisher.value = this.value;
            displayPoemsList();
        });
    }
    
    if (selectedPoemId) {
        setTimeout(() => {
            
            currentSelectedPoemId = selectedPoemId;
            displayPoem(selectedPoemId);

            const activeItem = document.querySelector(`.poemitem[data-poem-id="${selectedPoemId}"]`);
            if (activeItem) {
                activeItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }, 100);
    }
    
    if (filterPublisher) {
        const searchPublisher = document.getElementById('searchPublisher');
        if (searchPublisher) {
            searchPublisher.value = filterPublisher;
            currentFilters.publisher = filterPublisher;
            displayPoemsList();
        }
    }
    
});