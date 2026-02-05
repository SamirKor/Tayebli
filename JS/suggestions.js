const recipesByMealTime = {
    breakfast: [],
    lunch: [],
    dinner: [],
    surprise: []
};

// Meal time configurations
const mealTimeConfig = {
    breakfast: {
        title: 'Breakfast',
        description: 'Based on the current time (6:00 - 11:00 AM), we suggest breakfast recipes.',
        gradient: 'breakfast-gradient',
        icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 2v10"/>
            <path d="M18.36 6.64a9 9 0 1 1-12.73 0"/>
            <circle cx="12" cy="12" r="10"/>
        </svg>`
    },
    lunch: {
        title: 'Lunch',
        description: 'Based on the current time (11:00 AM - 3:00 PM), we suggest lunch recipes.',
        gradient: 'lunch-gradient',
        icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <polyline points="12 6 12 12 16 14"/>
        </svg>`
    },
    dinner: {
        title: 'Dinner',
        description: 'Based on the current time (5:00 PM - 9:00 PM), we suggest dinner recipes.',
        gradient: 'dinner-gradient',
        icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
        </svg>`
    },
    surprise: {
        title: 'Surprise Me!',
        description: 'Feeling adventurous? Let us pick something unexpected that you might love.',
        gradient: 'surprise-gradient',
        icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="16 3 21 3 21 8"/>
            <line x1="4" y1="20" x2="21" y2="3"/>
            <polyline points="21 16 21 21 16 21"/>
            <line x1="15" y1="15" x2="21" y2="21"/>
            <line x1="4" y1="4" x2="9" y2="9"/>
        </svg>`
    }
};

let selectedMealTime = '';
let currentRecipes = [];
let currentFavorites = {}; // Store favorite status globally

// Get current time-based suggestion
function getCurrentTimeBasedSuggestion() {
    const hour = new Date().getHours();
    if (hour >= 6 && hour < 11) return 'breakfast';
    if (hour >= 11 && hour < 15) return 'lunch';
    if (hour >= 17 && hour < 21) return 'dinner';
    return 'surprise';
}

// Setup smart banner
function setupSmartBanner() {
    const smartMealTime = getCurrentTimeBasedSuggestion();
    const config = mealTimeConfig[smartMealTime];

    const bannerIcon = document.getElementById('bannerIcon');
    const bannerTitle = document.getElementById('bannerTitle');
    const bannerDescription = document.getElementById('bannerDescription');
    const smartBanner = document.querySelector('.smart-banner');

    bannerIcon.innerHTML = config.icon;
    bannerTitle.textContent = `Perfect time for ${config.title.toLowerCase()}!`;
    bannerDescription.textContent = config.description;

    // Update banner gradient
    smartBanner.className = 'smart-banner';
    if (smartMealTime === 'breakfast') {
        smartBanner.style.background = 'linear-gradient(135deg, #FBBF24, #F97316)';
    } else if (smartMealTime === 'lunch') {
        smartBanner.style.background = 'linear-gradient(135deg, #60A5FA, #2563EB)';
    } else if (smartMealTime === 'dinner') {
        smartBanner.style.background = 'linear-gradient(135deg, #C084FC, #9333EA)';
    } else {
        smartBanner.style.background = 'linear-gradient(135deg, #F472B6, #EF4444)';
    }

    // Setup show suggestions button
    const showSuggestionsBtn = document.getElementById('showSuggestionsBtn');
    showSuggestionsBtn.addEventListener('click', () => {   
        console.log('Show Suggestions button clicked for', smartMealTime);
        getrecipes(smartMealTime);
    });
}

// Create recipe card HTML
function createRecipeCard(recipe) {
    return `
        <div class="recipe-card">
            <div class="recipe-image-container">
                <img src="${recipe.image}" alt="${recipe.title}" class="recipe-image">
                <button class="favorite-btn" data-id="${recipe.id}">
                    <svg viewBox="0 0 24 24" fill="${recipe.isFavorite ? 'red' : 'none'}" stroke="currentColor" stroke-width="2">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                    </svg>
                </button>
                <span class="difficulty-badge difficulty-${recipe.difficulty.toLowerCase()}">
                    ${recipe.difficulty}
                </span>
            </div>
            <div class="recipe-content">
                <h3 class="recipe-title">${recipe.title}</h3>
                <p class="recipe-description">${recipe.description}</p>
                <div class="recipe-meta">
                    <div class="meta-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                        <span>${recipe.cookTime}</span>
                    </div>
                    <div class="meta-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                        <span>${recipe.servings}</span>
                    </div>
                    <div class="meta-item">
                        <svg class="star-icon" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                        </svg>
                        <span>${recipe.rating}</span>
                    </div>
                </div>
                <button class="btn-view-recipe" data-id="${recipe.id}">View Recipe</button>
            </div>
        </div>
    `;
}

// Select meal time
function selectMealTime(mealTime) {
    selectedMealTime = mealTime;
    // Note: currentRecipes is already set by getrecipes() before calling this function
    // Do NOT overwrite it here

    // Update card selection
    document.querySelectorAll('.meal-time-card').forEach(card => {
        card.classList.remove('selected');
    });
    const selectedCard = document.querySelector(`[data-meal="${mealTime}"]`);
    if (selectedCard) {
        selectedCard.classList.add('selected');
    }

    // Show/hide sections
    document.getElementById('emptyState').style.display = 'none';
    document.getElementById('suggestedSection').style.display = 'block';

    // Update title
    const config = mealTimeConfig[mealTime];
    document.getElementById('suggestedTitle').textContent = `Suggested ${config.title} Recipes`;

    // Render recipes
    renderRecipes();

    // Scroll to recipes
    document.getElementById('suggestedSection').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// Render recipes
function renderRecipes() {
    const recipesGrid = document.getElementById('recipesGrid');

    if (currentRecipes.length > 0) {
        recipesGrid.innerHTML = currentRecipes.map(recipe => createRecipeCard(recipe)).join('');
        setupEventListeners();
    } else {
        recipesGrid.innerHTML = '<p>No recipes available for this meal time.</p>';
    }
}

// Toggle favorite via AJAX 
function toggleFavorite(recipeId) {
    const favBtn = document.querySelector(`.favorite-btn[data-id="${recipeId}"]`);
    if (!favBtn) return;

    const svg = favBtn.querySelector('svg');
    const isFavorited = svg.getAttribute('fill') === 'red';

    fetch('suggestions.php', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `toggle_favorite=true&recipe_id=${recipeId}`
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Toggle the fill color
                if (isFavorited) {
                    svg.setAttribute('fill', 'none');
                } else {
                    svg.setAttribute('fill', 'red');
                }
            } else {
                alert(data.message || 'Error updating favorite');
            }
        })
        .catch((e) => {
            console.error('Error toggling favorite:', e);
            alert('Error updating favorite. Please try again.');
        });
}

// Shuffle recipes (get new suggestions)
function shuffleRecipes() {
    if (!selectedMealTime) {
        console.warn('No meal time selected yet');
        alert('Please select a meal time first');
        return;
    }

    console.log('Shuffling recipes for meal time:', selectedMealTime);
    
    fetch('suggestions.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `meal=${encodeURIComponent(selectedMealTime)}`
    })
    .then(response => response.json())
    .then(data => {
        console.log('Server response:', data);
        if (data.success && data.recipes) {
            console.log('✓ Received shuffled recipes:', data.recipes.length);
            
            // Store favorites globally
            currentFavorites = data.isfavorite || {};
            
            // Format recipes
            currentRecipes = data.recipes.map(recipe => ({
                id: recipe.RecipeID,
                title: recipe.Rtitle || 'Untitled Recipe',
                description: recipe.Description || 'No description',
                image: recipe.ImagePath || recipe.ImageURL || 'https://via.placeholder.com/400',
                cookTime: recipe.TotalTime || 'N/A',
                servings: recipe.Serving || 2,
                difficulty: recipe.Difficulty || 'Medium',
                isFavorite: currentFavorites[recipe.RecipeID] || false,
                rating: 4.5
            }));
            
            renderRecipes();
            
        } else {
            console.error('Failed to shuffle recipes:', data.message || 'Unknown error');
            alert('Error: ' + (data.message || 'Failed to get new recipes'));
        }
    })
    .catch(error => {
        console.error('Error shuffling recipes:', error);
        alert('Error shuffling recipes: ' + error);
    });
}

// View recipe
function viewRecipe(recipeId) {
    console.log('Viewing recipe:', recipeId);
    window.location.href = 'recipe_description.php?id=' + recipeId;
}

// Setup event listeners
function setupEventListeners() {
    // Favorite buttons
    document.querySelectorAll('.favorite-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const recipeId = btn.getAttribute('data-id');
            toggleFavorite(recipeId);
        });
    });

    // View recipe buttons
    document.querySelectorAll('.btn-view-recipe').forEach(btn => {
        btn.addEventListener('click', () => {
            const recipeId = btn.getAttribute('data-id');
            viewRecipe(recipeId);
        });
    });
}

let mealTime;

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    console.log('=== DOMContentLoaded fired ===');

    // Setup smart banner
    try {
        setupSmartBanner();
        console.log('✓ Smart banner setup complete');
    } catch (err) {
        console.error('✗ Error setting up smart banner:', err);
    }

    // Setup meal time card clicks: select locally and POST selection to server
    const cards = document.querySelectorAll('.meal-time-card');
    console.log('Found ' + cards.length + ' meal time cards');

    cards.forEach(card => {
        card.addEventListener('click', async () => {
            mealTime = card.getAttribute('data-meal');
            console.log('--- Meal card clicked: ' + mealTime + ' ---');

            getrecipes(mealTime);

        });
    });

    // Setup shuffle button
    const shuffleBtn = document.getElementById('shuffleBtn');
    if (shuffleBtn) {
        shuffleBtn.addEventListener('click', shuffleRecipes);
        console.log('✓ Shuffle button listener attached');
    } else {
        console.warn('Shuffle button not found');
    }
});



fetch("./footer.php")
    .then(response => response.text())
    .then(data => {
        document.getElementById("footer").innerHTML = data;
    })
    .catch(err => console.error("Error loading footer:", err));

const homebtn = document.getElementById("homebtn")
homebtn.addEventListener('click', () => {
    window.location.href = "index.php?type=home"
})

const login = document.getElementById('Login');
login.addEventListener("click", () => {
    window.location.href = "login.php?type=login";
})

const signup = document.getElementById('Sign-up');
signup.addEventListener("click", () => {
    window.location.href = "login.php?type=singup"
})

const find = document.getElementById('findbtn');
find.addEventListener('click', () => {
    window.location.href = "recipes.php"
})

const suggest = document.getElementById('Suggestbtn');
suggest.addEventListener('click', () => {
    window.location.href = "suggestions.php"
})

const favs = document.getElementById('favbtn');
favs.addEventListener('click', () => {
    window.location.href = "favorites.php"
})


const planbtn = document.getElementById('planbtn');
planbtn.addEventListener('click', () => {
    window.location.href = "planner.php"
})

const addrecpbtn = document.getElementById('addrecpbtn');
addrecpbtn.addEventListener('click', () => {
    window.location.href = "addrecipe.php"
})

const settingsbtn = document.getElementById('settingsbtn');
settingsbtn.addEventListener('click', () => {
    window.location.href = "settings.php"
})


// form submission is now handled by clicking meal cards (AJAX + redirect)

// Root-level log to verify script execution
console.log('=== suggestions.js loaded ===');

// Additional log to confirm DOMContentLoaded
console.log('Waiting for DOMContentLoaded...');



async function getrecipes(time)
{
    
    try {
                console.log('Fetching from suggestions.php with meal=' + time);
                const response = await fetch('suggestions.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `meal=${encodeURIComponent(time)}`,
                });

                console.log('Response status:', response.status);
                const contentType = response.headers.get('content-type') || '';
                console.log('Content-Type header:', contentType);

                let data = null;

                if (contentType.includes('application/json')) {
                    data = await response.json();
                    console.log('✓ Server JSON response:', data);
                } else {
                    const text = await response.text();
                    console.log('Server text response (first 500 chars):', text.slice(0, 500));
                    try {
                        // Check if the text is valid JSON
                        if (text.trim().startsWith('{') || text.trim().startsWith('[')) {
                            data = JSON.parse(text);
                            console.log('✓ Parsed JSON from text:', data);
                        } else {
                            console.warn('Response is not valid JSON.');
                            data = null;
                        }
                    } catch (e) {
                        console.warn('Could not parse as JSON:', e.message);
                        data = null;
                    }
                }

                if (data && data.success && data.recipes && data.recipes.length > 0) {
                    console.log('✓ Success flag received. Recipe count:', data.recipes.length);
                    
                    // Store favorites globally for use in renderRecipes
                    currentFavorites = data.isfavorite || {};
                    
                    const formattedRecipes = data.recipes.map(recipe => ({
                        id: recipe.RecipeID,
                        title: recipe.Rtitle || 'Untitled Recipe',
                        description: recipe.Description || 'No description',
                        image: recipe.ImagePath || recipe.ImageURL || 'https://via.placeholder.com/400',
                        cookTime: recipe.TotalTime || 'N/A',
                        servings: recipe.Serving || 2,
                        difficulty: recipe.Difficulty || 'Medium',
                        isFavorite: currentFavorites[recipe.RecipeID] || false,
                        rating: 4.5
                    }));

                    console.log('✓ Formatted recipes:', formattedRecipes.length, 'recipes');
                    currentRecipes = formattedRecipes;
                    console.log('✓ Stored in currentRecipes, now calling selectMealTime');
                    // NOW render after data is stored
                    selectMealTime(time);
                    console.log('✓ Recipes rendered on screen');
                } else {
                    console.error('✗ No recipes or malformed response:', data);
                }

            } catch (err) {
                console.error('✗ Error:', err);
            }

}