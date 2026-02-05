// Favorite recipes data - will be populated from database
let favoriteRecipes = [];

let searchQuery = '';
let sortBy = 'recent';

// Fetch favorites from database
function fetchFavoritesFromDatabase() {
    return fetch('favorites.php', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'get_favorites=true'
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                favoriteRecipes = data.recipes;
                renderRecipes();
            } else {
                console.error('Error fetching favorites:', data.message);
            }
        })
        .catch((e) => {
            console.error('Error fetching favorites from database:', e);
        });
}

// Create recipe card HTML
function createRecipeCard(recipe) {
    return `
        <div class="recipe-card">
            <div class="recipe-image-container">
                <img src="${recipe.image}" alt="${recipe.title}" class="recipe-image">
                <button class="favorite-btn" data-id="${recipe.id}">
                    <svg viewBox="0 0 24 24" fill="red" stroke="currentColor" stroke-width="2">
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

// Filter recipes
function filterRecipes() {
    return favoriteRecipes.filter(recipe => {
        if (searchQuery === '') return true;
        return recipe.title.toLowerCase().includes(searchQuery.toLowerCase()) ||
               recipe.description.toLowerCase().includes(searchQuery.toLowerCase());
    });
}

// Sort recipes
function sortRecipes(recipes) {
    const sorted = [...recipes];
    
    switch (sortBy) {
        case 'recent':
            sorted.sort((a, b) => new Date(b.dateAdded).getTime() - new Date(a.dateAdded).getTime());
            break;
        case 'oldest':
            sorted.sort((a, b) => new Date(a.dateAdded).getTime() - new Date(b.dateAdded).getTime());
            break;
        case 'rating':
            sorted.sort((a, b) => b.rating - a.rating);
            break;
        case 'name':
            sorted.sort((a, b) => a.title.localeCompare(b.title));
            break;
        case 'cookTime':
            sorted.sort((a, b) => parseInt(a.cookTime) - parseInt(b.cookTime));
            break;
        default:
            break;
    }
    
    return sorted;
}

// Update stats
function updateStats() {
    const totalRecipes = favoriteRecipes.length;
    const avgCookTime = Math.floor(
        favoriteRecipes.reduce((sum, recipe) => sum + parseInt(recipe.cookTime), 0) / totalRecipes
    );
    const avgRating = (
        favoriteRecipes.reduce((sum, recipe) => sum + recipe.rating, 0) / totalRecipes
    ).toFixed(1);
    
    document.getElementById('totalRecipes').textContent = totalRecipes;
    document.getElementById('avgCookTime').textContent = avgCookTime;
    document.getElementById('avgRating').textContent = avgRating;
}

// Render recipes
function renderRecipes() {
    const recipesGrid = document.getElementById('recipesGrid');
    const emptyState = document.getElementById('emptyState');
    const noResultsState = document.getElementById('noResultsState');
    const statsSection = document.getElementById('statsSection');
    const searchFilterSection = document.getElementById('searchFilterSection');
    const tipsSection = document.getElementById('tipsSection');
    const clearAllBtn = document.getElementById('clearAllBtn');
    
    if (favoriteRecipes.length === 0) {
        // Show empty state
        emptyState.style.display = 'block';
        recipesGrid.style.display = 'none';
        noResultsState.style.display = 'none';
        statsSection.style.display = 'none';
        searchFilterSection.style.display = 'none';
        tipsSection.style.display = 'none';
        clearAllBtn.style.display = 'none';
    } else {
        const filteredRecipes = filterRecipes();
        const sortedRecipes = sortRecipes(filteredRecipes);
        
        emptyState.style.display = 'none';
        statsSection.style.display = 'block';
        searchFilterSection.style.display = 'block';
        tipsSection.style.display = 'block';
        clearAllBtn.style.display = 'flex';
        
        updateStats();
        
        if (sortedRecipes.length > 0) {
            recipesGrid.innerHTML = sortedRecipes.map(recipe => createRecipeCard(recipe)).join('');
            recipesGrid.style.display = 'grid';
            noResultsState.style.display = 'none';
        } else {
            recipesGrid.style.display = 'none';
            noResultsState.style.display = 'block';
        }
        
        setupEventListeners();
    }
}

// Remove from favorites via AJAX
function removeFromFavorites(recipeId) {
    fetch('favorites.php', {
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
                // Remove from local array and re-render
                favoriteRecipes = favoriteRecipes.filter(recipe => recipe.id != recipeId);
                renderRecipes();
            } else {
                alert(data.message || 'Error removing from favorites');
            }
        })
        .catch((e) => {
            console.error('Error removing from favorites:', e);
            alert('Error removing from favorites. Please try again.');
        });
}

// Clear all favorites
function clearAllFavorites() {
    if (confirm('Are you sure you want to remove all favorite recipes?')) {
        // Remove all favorites via AJAX
        Promise.all(favoriteRecipes.map(recipe => 
            fetch('favorites.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `toggle_favorite=true&recipe_id=${recipe.id}`
            })
        )).then(() => {
            favoriteRecipes = [];
            renderRecipes();
        }).catch(err => {
            console.error('Error clearing favorites:', err);
            alert('Error clearing favorites. Please try again.');
        });
    }
}

// View recipe
function viewRecipe(recipeId) {
    console.log('Viewing recipe:', recipeId);
    window.location.href = `recipe_description.php?id=${recipeId}`;
}

// Setup event listeners
function setupEventListeners() {
    // Favorite buttons (remove from favorites)
    document.querySelectorAll('.favorite-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const recipeId = btn.getAttribute('data-id');
            removeFromFavorites(recipeId);
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

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    // Fetch favorites from database first
    fetchFavoritesFromDatabase().then(() => {
        // Search input
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                searchQuery = e.target.value;
                renderRecipes();
            });
        }
        
        // Sort by
        const sortBySelect = document.getElementById('sortBy');
        if (sortBySelect) {
            sortBySelect.addEventListener('change', (e) => {
                sortBy = e.target.value;
                renderRecipes();
            });
        }
        
        // Clear all button
        const clearAllBtn = document.getElementById('clearAllBtn');
        if (clearAllBtn) {
            clearAllBtn.addEventListener('click', clearAllFavorites);
        }
    });
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
find.addEventListener('click', ()=>
{
  window.location.href = "recipes.php"
})

const suggest = document.getElementById('Suggestbtn');
suggest.addEventListener('click', ()=>
{
  window.location.href = "suggestions.php"
})

const favs = document.getElementById('favbtn');
favs.addEventListener('click', ()=>
{
  window.location.href = "favorites.php"
})


const planbtn = document.getElementById('planbtn');
planbtn.addEventListener('click', ()=>
{
  window.location.href = "planner.php"
})

const addrecpbtn = document.getElementById('addrecpbtn');
addrecpbtn.addEventListener('click', ()=>
{
  window.location.href = "addrecipe.php"
})

const settingsbtn = document.getElementById('settingsbtn');
settingsbtn.addEventListener('click', ()=>
{
  window.location.href = "settings.php"
})
