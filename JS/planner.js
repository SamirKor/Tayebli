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


const lstofrecipes = document.getElementById("lstofrecipes");
const selectrcpBtns = document.querySelectorAll(".selectrcp");
const closeBtn = document.getElementById("closePopup");
const recipes = document.querySelectorAll(".recpitem");
const requiredingr = document.querySelector(".requiredingr");
const ingredientee = document.getElementById("Ingredients");
const shplstbtn = document.getElementById("shoplstbtn");

let counterofrecipes = INITIAL_RECIPE_COUNT;
let percentweek = Math.round((counterofrecipes / 28) * 100);
const totalplanned = document.getElementById('totalplanned');
const percentrecipes = document.getElementById('percentage');
var daycount = 0;
const totalDaysEl = document.getElementById("totaldays");
let counterofingredients = INITIAL_INGREDIENT_COUNT;
const totalingredients = document.getElementById('ingredientscount')


function updateStats() {
  let daysCompleted = 0;
  document.querySelectorAll(".aday").forEach(day => {
    const selectedCount = day.querySelectorAll(".selected-recipe").length;
    if (selectedCount === 4) daysCompleted++;
  });
  totalDaysEl.textContent = daysCompleted;
}


function initializeStats() {
  if (totalplanned) totalplanned.textContent = counterofrecipes;
  if (percentrecipes) percentrecipes.textContent = percentweek;
  if (totalingredients) totalingredients.textContent = counterofingredients;
  updateDaysCompleted();
}


function updateDaysCompleted() {
  let daysCompleted = 0;
  document.querySelectorAll(".aday").forEach(day => {
    const selectedCount = day.querySelectorAll(".selected-recipe").length;
    if (selectedCount === 4) daysCompleted++;
  });
  if (totalDaysEl) totalDaysEl.textContent = daysCompleted;
}





shplstbtn.addEventListener('click', ()=>{
  requiredingr.style.display = "flex";
});

let currentSlot = null; // will store which button opened the popup

// Open recipe list popup
selectrcpBtns.forEach(btn => {
  btn.onclick = () => {
    currentSlot = btn.dataset.slot; // remember which meal/day was clicked
    lstofrecipes.style.display = "flex";
  };
});


//function to render recipes
function renderRecipes(recipe, day, mealtype) {
  return `
  <div data-recipe-id="${recipe.RecipeID}" class="selected-recipe">
      <div class="recipe-image-container">
        <img src="${recipe.ImagePath}" alt="${recipe.Rtitle}" class="recipe-thumb">
      </div>
      <div class="recipe-info">
        <p class="recipe-title">${recipe.Rtitle}</p>
        <div class="recipe-footer">
          <p class="recipe-time"><i class="fa-regular fa-clock"></i> ${recipe.TotalTime} min</p>
          <button class="remove-recipe-btn" data-slot="${day}-${mealtype}" aria-label="Remove recipe">
            <i class="fa-solid fa-trash"></i></button>
        </div>
      </div>
    </div>
  `;
}

function renderIng(ingredient, day, mealtype)
{
  return `
                  <li class="ingredientItem" data-spotid="${day}-${mealtype}">
                        <p>${ingredient.Iname} ${ingredient.Amount}</p>
                        </li>
                        `;
}



// Close popup
closeBtn.onclick = () => lstofrecipes.style.display = "none";

// Close popup if user clicks outside
window.onclick = (e) => {
  if (e.target === lstofrecipes) lstofrecipes.style.display = "none";
};

window.onclick = (e) => {
  if (e.target === requiredingr) requiredingr.style.display = "none";
}

// When user picks a recipe
recipes.forEach(rcp => {
  rcp.onclick = () => {
    if (currentSlot) {
      const slotBtn = document.querySelector(`[data-slot="${currentSlot}"]`);

      const slot = slotBtn.dataset.slot;   // "tuesday-dinner"
      const [day, mealType] = slot.split('-');

      const recipeID = rcp.dataset.recipeId;
      console.log('Selected Recipe ID:', recipeID);

      fetch('planner.php', {
        method: 'POST',
        headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
    get_recipe_by_id: recipeID,
    day: day,
    meal_type: mealType
  })

      }).then(response => response.json())
      .then(data => {
        console.log('RAW RESPONSE:', data);
        if (data.success && data.recipe && data.ingredients) {
          const recipe = data.recipe;
          console.log('Recipe had been received yooooooooooo');


          // Create selected recipe div
          slotBtn.parentElement.insertAdjacentHTML(
            'beforeend',
            renderRecipes(data.recipe, day, mealType)
          );  
          
          updateCounters(counterofrecipes + 1);

          
          let ingcount = 0;
          
          data.ingredients.forEach(ing =>
          {
            ingredientee.insertAdjacentHTML(
              'beforeend',
              renderIng(ing, day, mealType)
            );
            ingcount = ingcount + 1;
          })

          updateCountIngredient(counterofingredients + ingcount);

          slotBtn.remove(); // remove the select button
          document.querySelectorAll('.remove-recipe-btn').forEach(btn =>
          {
            deleteProcess(btn);
          }
          )
          lstofrecipes.style.display = "none";

        }
        else {
          console.error('Failed to fetch recipe details' + data.message);
        }
      }).catch((err => console.error('Error fetching recipe:', err)));
      }
    }
  }
);



function getSlotIdentifier(mealElement) {
  // Try to get from existing button's data-slot
  const existingBtn = mealElement.querySelector("button");
  if (existingBtn && existingBtn.dataset.slot) {
    return existingBtn.dataset.slot;
  }
  
  // Otherwise construct it from the DOM structure
  // Assuming structure: <div class="ameal breakfast"> inside a day column
  const mealType = mealElement.classList[1] || 'unknown'; // e.g., "breakfast", "lunch"
  
  // Get the day from parent structure
  const dayHeader = mealElement.closest('.day-column')?.querySelector('.day-header');
  const day = dayHeader ? dayHeader.textContent.toLowerCase().trim() : 'unknown';
  
  return `${mealType}-${day}`;
}


function clearAllMealSlots() {
  document.querySelectorAll(".rcpholder").forEach(meal => {
    // Clear everything
    meal.innerHTML = "";
    
    // Add "Add Recipe" button
    const slot = getSlotIdentifier(meal);
    const btn = document.createElement("button");
    btn.className = "selectrcp";
    btn.dataset.slot = slot;
    btn.innerHTML = '<i class="fa-solid fa-plus"></i> Add Recipe';
    btn.onclick = () => {
      currentSlot = slot;
      lstofrecipes.style.display = "flex";
    };
    meal.appendChild(btn);
  });
}

function updateCounters(count) {
  counterofrecipes = count;
  percentweek = Math.round((count / 28) * 100);
  if (totalplanned) totalplanned.textContent = count;
  if (percentrecipes) percentrecipes.textContent = percentweek;
  updateDaysCompleted();
}

function updateCountIngredient(count)
{
  counterofingredients = count;
  if(totalingredients) totalingredients.textContent = counterofingredients;

}




const clearWeekBtn = document.getElementById("clearbtn");
clearWeekBtn.onclick = () => {

  const originalText = clearWeekBtn.innerHTML;
  const originalDisabled = clearWeekBtn.disabled;

  clearWeekBtn.disabled = true;
  clearWeekBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Clearing...';
  
  fetch('planner.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'Clear_Week=true'
  })
  .then(response => {
    if (response.ok) {
      // Success - clear UI
      clearAllMealSlots();
      updateCounters(0);
      updateCountIngredient(0);
      console.log("Week cleared successfully!");
    } else {
      
      throw new Error(`Server error: ${response.status}`);
    }
  })
  .catch(error => {
    console.error("Clear failed:", error);
    alert("Failed to clear week. Please try again.");
  })
  .finally(() => {
    // Reset button
    clearWeekBtn.disabled = false;
    clearWeekBtn.innerHTML = originalText;
  });
};
  

const deletebtns = document.querySelectorAll('.remove-recipe-btn');
deletebtns.forEach(btn =>
{
  btn.addEventListener('click', function()
{
    deleteProcess(btn);
  
})
}
)

function deleteProcess(btn)
{
  btn.addEventListener('click', function()
  {
    currentSlot = btn.dataset.slot; 
    const slotBtn = document.querySelector(`[data-slot="${currentSlot}"]`);
    
    const slot = slotBtn.dataset.slot;   // "tuesday-dinner"
    const [day, mealType] = slot.split('-');


    fetch('planner.php', 
      {
        method: 'POST',
        headers:
        {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
          delete_recipe: true,
          day: day,
          meal_type: mealType
        })
      }
    ).then(response => response.json())
    .then(data =>
    {
      if (data.success) {
        console.log('u smart');

        // Find the parent container of the recipe card
          const recipeCard = btn.closest('.selected-recipe');
          const container = recipeCard.parentElement;
          
          // Clear the container
          container.innerHTML = '';

          // Add the "Add Recipe" button
          const newBtn = document.createElement("button");
          newBtn.className = "selectrcp";
          newBtn.dataset.slot = currentSlot;
          newBtn.innerHTML = '<i class="fa-solid fa-plus"></i> Add Recipe';
          newBtn.onclick = () => {
            currentSlot = currentSlot;
            lstofrecipes.style.display = "flex";
          };
          container.appendChild(newBtn);
          updateCounters(counterofrecipes - 1);
          

          
      }else console.error('Failed delete' + data.message);
    }
    ).catch(error => {
      console.error("Clear failed:", error);
      alert("Failed to remove that one");
    })
    
}) 
}


 



initializeStats();
