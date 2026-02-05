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


document.getElementById('add-ingredient-btn').addEventListener('click', function () {
  const ingredientsList = document.getElementById('ingredients-list');

  // Create new ingredient element
  const newIngredient = document.createElement('div');
  newIngredient.className = 'ingredient-item';
  newIngredient.innerHTML = `
    <input type="text" placeholder="Ingredient Name" class="inputbox" name="Iname[]">
    <input type="text" placeholder="Amount(e.g 2cups)" class="inputbox" name="Iamount[]">
    <button class="remove" type="button">-</button>
  `;
  // Add to list
  ingredientsList.appendChild(newIngredient);

  // Add event listener to remove button AFTER creating it
  newIngredient.querySelector('.remove').addEventListener('click', function (e) {
    e.preventDefault();
    ingredientsList.removeChild(newIngredient);
  });
});


// Add event listeners to existing remove buttons (if any) - do this on DOM load
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.ingredient-item .remove').forEach(button => {
    button.addEventListener('click', function (e) {
      e.preventDefault();
      this.parentElement.remove();
    });
  });
});

const planner = document.getElementById('planbtn');
planner.addEventListener("click", () => {
  window.location.href = "planner.php?type=planner"
})

var stepCount = 1;



document.getElementById('addstepbtn').addEventListener('click', function () {
  const ingredientsList = document.getElementById('instrc-list');
  stepCount++;
  // Create new ingredient element
  const newIngredient = document.createElement('div');
  newIngredient.className = 'inst-item';
  newIngredient.innerHTML = `
    <p
                            style="border-radius: 20px;height: fit-content; padding: 10px 15px; font-size: 15px; background-color: #FF914D; color: white; margin-top: 10px;">
                            ${stepCount}</p>
                        <div class="instcols">
                            <div class="twoinrow">
                                <input style="width: 45%" type="text" placeholder="Step Title (Optinal)" class="inputbox" name="step_title[]">
                                <input style="width: 45%" type="number" placeholder="Time Needed (in minutes)" class="inputbox" name="step_time[]">
                            </div>
                            
                            <input required type="text" placeholder="Describe this step in Details"
                                class="inputbox Description" name="step_description[]">
                        </div>
                        <button class="remove" type="button"> - </button>
  `;
  // Add to list
  ingredientsList.appendChild(newIngredient);

  // Add event listener to remove button AFTER creating it
  newIngredient.querySelector('.remove').addEventListener('click', function (e) {
    e.preventDefault();
    ingredientsList.removeChild(newIngredient);
    stepCount--;
  });
});





// Get the elements
const imgbtn = document.getElementById('imgbtn'); // This is your button
const imageUpload = document.getElementById('imageUpload'); // This should be the hidden file input
const imagePreview = document.querySelector('.imagezone');

// If imgbtn is a button, make it trigger the file input
if (imgbtn && imageUpload) {
  imgbtn.addEventListener('click', function () {
    imageUpload.click();
  });
} else {
  console.error("Missing elements:", {
    imgbtn: !!imgbtn,
    imageUpload: !!imageUpload
  });
}

// Event listener for when file is selected
if (imageUpload) {
  imageUpload.addEventListener('change', function (event) {
    console.log("File input change event fired!");
    console.log("Files in input:", event.target.files);

    const file = event.target.files[0];

    if (file) {
      console.log("File selected:", file.name, "Size:", file.size, "Type:", file.type);

      if (!file.type.startsWith('image/')) {
        alert('Please select an image file');
        return;
      }

      const reader = new FileReader();

      reader.onload = function (e) {
        console.log("File read successfully");
        if (imagePreview) {
          // Remove only the preview image, NOT the file input
          const existingImg = imagePreview.querySelector('img');
          if (existingImg) {
            existingImg.remove();
          }

          const img = document.createElement('img');
          img.src = e.target.result;
          img.style.maxWidth = '100%';
          img.style.maxHeight = '100%';
          img.style.objectFit = 'cover';
          img.style.marginTop = '10px';

          // Insert image after the file input, not replacing everything
          imageUpload.parentElement.insertBefore(img, imageUpload.nextSibling);
          imagePreview.style.border = '2px solid #FF914D';
        }
      };

      reader.readAsDataURL(file);
    } else {
      console.warn("No file in event.target.files[0]");
    }
  });
}

// Ensure hidden inputs for category and difficulty are populated before the form submits
const publishForm = document.getElementById('Publishform');
if (publishForm) {
  publishForm.addEventListener('submit', function (e) {
    const diff = document.getElementById('Difficulty');
    const cat = document.getElementById('Category');
    const diffInput = document.getElementById('DifficultyID');
    const catInput = document.getElementById('CategoryID');
    if (diff && diffInput) diffInput.value = diff.value;
    if (cat && catInput) catInput.value = cat.value;

    // DEBUG: Check file input status
    const imageUpload = document.getElementById('imageUpload');
    console.log("Form submitted - checking file input...");
    console.log("imageUpload element:", imageUpload);
    console.log("imageUpload.files:", imageUpload.files);
    console.log("imageUpload.files.length:", imageUpload.files.length);
    if (imageUpload.files.length > 0) {
      console.log("File selected:", imageUpload.files[0].name);
    } else {
      console.warn("WARNING: No file selected!");
    }

    // Add AI analysis loading indicator
    const submitBtn = document.getElementById('publishbtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Analyzing Ingredients...';
    submitBtn.disabled = true;
    
    // Show processing message
    const processingMsg = document.createElement('div');
    processingMsg.id = 'ai-processing';
    processingMsg.innerHTML = `
        <div style="background: #e3f2fd; padding: 10px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #2196f3;">
            <i class="fas fa-brain"></i> AI is analyzing your ingredients for nutrition data...
        </div>
    `;
    this.prepend(processingMsg);
  });
}

// Add real-time ingredient validation
document.addEventListener('DOMContentLoaded', function () {
  const ingredientInputs = document.querySelectorAll('input[name="Iname[]"]');

  ingredientInputs.forEach(input => {
    input.addEventListener('blur', function () {
      validateIngredientName(this);
    });
  });

  // For dynamically added ingredients
  document.addEventListener('click', function (e) {
    if (e.target && e.target.classList.contains('addbutton')) {
      setTimeout(() => {
        const newInputs = document.querySelectorAll('input[name="Iname[]"]:not(.validated)');
        newInputs.forEach(input => {
          input.classList.add('validated');
          input.addEventListener('blur', function () {
            validateIngredientName(this);
          });
        });
      }, 100);
    }
  });
});

function validateIngredientName(input) {
  const value = input.value.trim().toLowerCase();

  // Check if ingredient is too vague
  const vagueTerms = ['sauce', 'dressing', 'mix', 'blend', 'powder', 'extract'];
  let isVague = vagueTerms.some(term => value.includes(term) && value.split(' ').length < 2);

  // Check if it's a brand name or processed food
  const brandIndicators = ['kraft', 'heinz', 'nestle', 'campbell', 'progresso'];
  let isBrand = brandIndicators.some(brand => value.includes(brand));

  // Add visual feedback
  input.style.border = '2px solid #ccc';
  const helpText = input.nextElementSibling && input.nextElementSibling.classList.contains('help-text')
    ? input.nextElementSibling
    : null;

  if (isBrand) {
    input.style.borderColor = 'orange';
    if (!helpText) {
      const help = document.createElement('small');
      help.className = 'help-text';
      help.style.color = 'orange';
      help.style.display = 'block';
      help.innerHTML = '<i class="fas fa-info-circle"></i> Use generic name instead of brand (e.g., "ketchup" not "Heinz ketchup")';
      input.parentNode.insertBefore(help, input.nextSibling);
    }
  } else if (isVague) {
    input.style.borderColor = 'orange';
    if (!helpText) {
      const help = document.createElement('small');
      help.className = 'help-text';
      help.style.color = 'orange';
      help.style.display = 'block';
      help.innerHTML = '<i class="fas fa-info-circle"></i> Be specific (e.g., "soy sauce" not just "sauce")';
      input.parentNode.insertBefore(help, input.nextSibling);
    }
  } else if (helpText) {
    helpText.remove();
  }
}

// Add AI processing styles dynamically
const style = document.createElement('style');
style.textContent = `
    .nutrition-badge {
        display: inline-block;
        padding: 3px 8px;
        margin: 2px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: bold;
        color: white;
    }
    .vegetarian { background: #4CAF50; }
    .vegan { background: #8BC34A; }
    .gluten-free { background: #FF9800; }
    .dairy-free { background: #2196F3; }
    .has-nuts { background: #795548; }
    .low-carb { background: #9C27B0; }
    
    .help-text {
        font-size: 12px;
        color: #666;
        margin-top: 5px;
        display: block;
    }
    
    #ai-processing {
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.7; }
        100% { opacity: 1; }
    }
`;
document.head.appendChild(style);