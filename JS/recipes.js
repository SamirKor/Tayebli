// All recipes data - will be populated from database
let allRecipes = [];

// Fetch recipes from database
function fetchRecipesFromDatabase() {
    return fetch('recipes.php', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'get_recipes=true'
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                allRecipes = data.recipes;
                renderRecipes();
            } else {
                console.error('Error fetching recipes:', data.message);
            }
        })
        .catch((e) => {
            console.error('Error fetching recipes from database:', e);
        });
}

// Filter state
let filters = {
    search: '',
    category: 'all',
    difficulty: 'all',
    cookTime: 'all'
};

let sortBy = 'popular';

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
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                        <span>${recipe.cookTime}</span>
                    </div>
                    <div class="meta-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                        <span>${recipe.servings}</span>
                    </div>
                    <div class="meta-item">
                        <svg class="star-icon" viewBox="0 0 24 24" stroke="currentColor">
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
    return allRecipes.filter(recipe => {
        // Search filter
        const matchesSearch = filters.search === '' ||
            recipe.title.toLowerCase().includes(filters.search.toLowerCase()) ||
            recipe.description.toLowerCase().includes(filters.search.toLowerCase());

        // Category filter
        const matchesCategory = filters.category === 'all' || recipe.category === filters.category;

        // Difficulty filter
        const matchesDifficulty = filters.difficulty === 'all' || recipe.difficulty === filters.difficulty;

        // Cook time filter
        let matchesCookTime = true;
        if (filters.cookTime !== 'all') {
            const cookTimeMinutes = parseInt(recipe.cookTime);
            if (filters.cookTime === 'quick') {
                matchesCookTime = cookTimeMinutes <= 20;
            } else if (filters.cookTime === 'medium') {
                matchesCookTime = cookTimeMinutes > 20 && cookTimeMinutes <= 40;
            } else if (filters.cookTime === 'long') {
                matchesCookTime = cookTimeMinutes > 40;
            }
        }

        return matchesSearch && matchesCategory && matchesDifficulty && matchesCookTime;
    });
}

// Sort recipes
function sortRecipes(recipes) {
    const sorted = [...recipes];

    switch (sortBy) {
        case 'rating':
            sorted.sort((a, b) => b.rating - a.rating);
            break;
        case 'time-asc':
            sorted.sort((a, b) => parseInt(a.cookTime) - parseInt(b.cookTime));
            break;
        case 'time-desc':
            sorted.sort((a, b) => parseInt(b.cookTime) - parseInt(a.cookTime));
            break;
        case 'name':
            sorted.sort((a, b) => a.title.localeCompare(b.title));
            break;
        case 'popular':
        default:
            // Keep default order for popular
            break;
    }

    return sorted;
}

// Render recipes
function renderRecipes() {
    const recipesGrid = document.getElementById('recipesGrid');
    const emptyState = document.getElementById('emptyState');
    const resultsCount = document.getElementById('resultsCount');

    const filteredRecipes = filterRecipes();
    const sortedRecipes = sortRecipes(filteredRecipes);

    // Update results count
    resultsCount.textContent = `Found ${sortedRecipes.length} recipe${sortedRecipes.length !== 1 ? 's' : ''}`;

    if (sortedRecipes.length > 0) {
        recipesGrid.innerHTML = sortedRecipes.map(recipe => createRecipeCard(recipe)).join('');
        recipesGrid.style.display = 'grid';
        emptyState.style.display = 'none';
    } else {
        recipesGrid.style.display = 'none';
        emptyState.style.display = 'block';
    }

    setupEventListeners();
}

// Toggle favorite via AJAX
function toggleFavorite(recipeId) {
    const recipe = allRecipes.find(r => r.id == recipeId);
    if (!recipe) return;

    const favBtn = document.querySelector(`.favorite-btn[data-id="${recipeId}"]`);
    if (!favBtn) return;

    const svg = favBtn.querySelector('svg');
    const isFavorited = svg.getAttribute('fill') === 'red';

    fetch('recipes.php', {
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
                // Update local data
                recipe.isFavorite = !recipe.isFavorite;
                // Update SVG fill
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

// View recipe
function viewRecipe(recipeId) {
    console.log('Viewing recipe:', recipeId);
    window.location.href = `recipe_description.php?id=${recipeId}`;
}

// Clear all filters
function clearFilters() {
    filters = {
        search: '',
        category: 'all',
        difficulty: 'all',
        cookTime: 'all'
    };

    document.getElementById('searchInput').value = '';
    document.getElementById('categoryFilter').value = 'all';
    document.getElementById('difficultyFilter').value = 'all';
    document.getElementById('cookTimeFilter').value = 'all';

    renderRecipes();
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

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    // Fetch recipes from database first
    fetchRecipesFromDatabase().then(() => {
        // Search input
        document.getElementById('searchInput').addEventListener('input', (e) => {
            filters.search = e.target.value;
            renderRecipes();
        });

        // Category filter
        document.getElementById('categoryFilter').addEventListener('change', (e) => {
            filters.category = e.target.value;
            renderRecipes();
        });

        // Difficulty filter
        document.getElementById('difficultyFilter').addEventListener('change', (e) => {
            filters.difficulty = e.target.value;
            renderRecipes();
        });

        // Cook time filter
        document.getElementById('cookTimeFilter').addEventListener('change', (e) => {
            filters.cookTime = e.target.value;
            renderRecipes();
        });

        // Sort by
        document.getElementById('sortBy').addEventListener('change', (e) => {
            sortBy = e.target.value;
            renderRecipes();
        });

        // Clear filters button
        document.getElementById('clearFilters').addEventListener('click', clearFilters);
    });
});


const buttons = document.querySelectorAll('.nav-buttons button');
buttons.forEach(btn => {
    btn.addEventListener('click', () => {
        buttons.forEach(b => b.classList.remove('active')); // remove from all
        btn.classList.add('active'); // add to clicked one
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
