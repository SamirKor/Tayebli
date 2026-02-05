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

// Fetch popular recipes from database
function fetchPopularRecipes() {
  fetch('index.php', {
    method: 'POST',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'Content-Type': 'application/x-www-form-urlencoded'
    },
    body: new URLSearchParams({ get_popular: 'true' })
  })
    .then(response => response.json())
    .then(data => {
      if (data.success && data.recipes) {
        renderPopularRecipes(data.recipes);
      } else {
        console.error('No recipes found or request failed');
      }
    })
    .catch(err => console.error('Error fetching popular recipes:', err));
}

fetchPopularRecipes();

// Render popular recipes with new card design
function renderPopularRecipes(recipes) {
  const container = document.getElementById('popularRecipesContainer');
  container.innerHTML = recipes.map(recipe => `
        <div class="recipe-card">
            <div class="recipe-image-container">
                <img src="${recipe.image}" alt="${recipe.title}" class="recipe-image">
                <button class="favorite-btn ${recipe.isFavorite ? 'active' : ''}" data-id="${recipe.id}">
                    <svg viewBox="0 0 24 24" fill="${recipe.isFavorite ? '#EF4444' : 'none'}" stroke="currentColor" stroke-width="2">
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
                <button class="btn-view-recipe" onclick="window.location.href='recipe_description.php?id=${recipe.id}'">View Recipe</button>
            </div>
        </div>
    `).join('');

  // Setup favorite buttons
  document.querySelectorAll('.recipe-card .favorite-btn').forEach(btn => {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      const recipeId = this.getAttribute('data-id');
      togglePopularRecipeFavorite(recipeId, this);
    });
  });
}

// Toggle favorite for popular recipes
function togglePopularRecipeFavorite(recipeId, btn) {
  fetch('index.php', {
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
        const svg = btn.querySelector('svg');
        const path = svg.querySelector('path');
        
        // Toggle active class
        btn.classList.toggle('active');
        
        // Toggle fill
        if (btn.classList.contains('active')) {
          path.setAttribute('fill', '#EF4444');
        } else {
          path.setAttribute('fill', 'none');
        }
      }
    })
    .catch(err => console.error('Error toggling favorite:', err));
}