// DOM Elements
const skillLevelDropdown = document.getElementById('skillLevelDropdown');
const skillLevelToggle = document.getElementById('skillLevelToggle');
const skillLevelText = document.getElementById('skillLevelText');
const skillLevelOptions = document.querySelectorAll('#skillLevelDropdown .dropdown-option');

const cookingTimeDropdown = document.getElementById('cookingTimeDropdown');
const cookingTimeToggle = document.getElementById('cookingTimeToggle');
const cookingTimeText = document.getElementById('cookingTimeText');
const cookingTimeOptions = document.querySelectorAll('#cookingTimeDropdown .dropdown-option');

const servingsDropdown = document.getElementById('servingsDropdown');
const servingsToggle = document.getElementById('servingsToggle');
const servingsText = document.getElementById('servingsText');
const servingsOptions = document.querySelectorAll('#servingsDropdown .dropdown-option');

const measurementDropdown = document.getElementById('measurementDropdown');
const measurementToggle = document.getElementById('measurementToggle');
const measurementText = document.getElementById('measurementText');
const measurementOptions = document.querySelectorAll('#measurementDropdown .dropdown-option');

const difficultyDropdown = document.getElementById('difficultyDropdown');
const difficultyToggle = document.getElementById('difficultyToggle');
const difficultyText = document.getElementById('difficultyText');
const difficultyOptions = document.querySelectorAll('#difficultyDropdown .dropdown-option');

const visibilityDropdown = document.getElementById('visibilityDropdown');
const visibilityToggle = document.getElementById('visibilityToggle');
const visibilityText = document.getElementById('visibilityText');
const visibilityOptions = document.querySelectorAll('#visibilityDropdown .dropdown-option');

const cuisineTags = document.getElementById('cuisineTags');
const addCuisineBtn = document.getElementById('addCuisineBtn');
const addCuisineModal = document.getElementById('addCuisineModal');
const newCuisineInput = document.getElementById('newCuisineInput');
const cancelCuisineBtn = document.getElementById('cancelCuisineBtn');
const saveCuisineBtn = document.getElementById('saveCuisineBtn');

const changePasswordBtn = document.getElementById('changePasswordBtn');
const changePasswordModal = document.getElementById('changePasswordModal');
const cancelPasswordBtn = document.getElementById('cancelPasswordBtn');
const savePasswordBtn = document.getElementById('savePasswordBtn');

const deleteAccountBtn = document.getElementById('deleteAccountBtn');
const deleteAccountModal = document.getElementById('deleteAccountModal');
const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

const saveChangesBtn = document.querySelector('.btn-save');
const cancelBtn = document.querySelector('.btn-cancel');

// Close all dropdowns when clicking outside
document.addEventListener('click', (e) => {
    if (!e.target.closest('.dropdown')) {
        document.querySelectorAll('.dropdown').forEach(dropdown => {
            dropdown.classList.remove('active');
        });
    }
});

// Skill Level Dropdown
if (skillLevelToggle && skillLevelDropdown && skillLevelText) {
    skillLevelToggle.addEventListener('click', (e) => {
        e.stopPropagation();
        skillLevelDropdown.classList.toggle('active');
    });

    if (skillLevelOptions && skillLevelOptions.length) {
        skillLevelOptions.forEach(option => {
            option.addEventListener('click', () => {
                skillLevelText.textContent = option.textContent.replace('✓', '').trim();
                document.getElementById('skillLevelInput').value = skillLevelText.textContent;
                skillLevelOptions.forEach(opt => opt.classList.remove('selected'));
                option.classList.add('selected');
                skillLevelDropdown.classList.remove('active');
            });
        });
    }
}

// Cooking Time Dropdown
if (cookingTimeToggle && cookingTimeDropdown && cookingTimeText) {
    cookingTimeToggle.addEventListener('click', (e) => {
        e.stopPropagation();
        cookingTimeDropdown.classList.toggle('active');
    });

    if (cookingTimeOptions && cookingTimeOptions.length) {
        cookingTimeOptions.forEach(option => {
            option.addEventListener('click', () => {
                cookingTimeText.textContent = option.textContent.replace('✓', '').trim();
                document.getElementById('cookingTimeInput').value = cookingTimeText.textContent;
                cookingTimeOptions.forEach(opt => opt.classList.remove('selected'));
                option.classList.add('selected');
                cookingTimeDropdown.classList.remove('active');
            });
        });
    }
}

// Servings Dropdown
if (servingsToggle && servingsDropdown && servingsText) {
    servingsToggle.addEventListener('click', (e) => {
        e.stopPropagation();
        servingsDropdown.classList.toggle('active');
    });

    if (servingsOptions && servingsOptions.length) {
        servingsOptions.forEach(option => {
            option.addEventListener('click', () => {
                servingsText.textContent = option.textContent.replace('✓', '').trim();
                document.getElementById('servingsInput').value = servingsText.textContent;
                servingsOptions.forEach(opt => opt.classList.remove('selected'));
                option.classList.add('selected');
                servingsDropdown.classList.remove('active');
            });
        });
    }
}

// Measurement System Dropdown
if (measurementToggle && measurementDropdown && measurementText) {
    measurementToggle.addEventListener('click', (e) => {
        e.stopPropagation();
        measurementDropdown.classList.toggle('active');
    });

    if (measurementOptions && measurementOptions.length) {
        measurementOptions.forEach(option => {
            option.addEventListener('click', () => {
                measurementText.textContent = option.textContent.replace('✓', '').trim();
                measurementOptions.forEach(opt => opt.classList.remove('selected'));
                option.classList.add('selected');
                measurementDropdown.classList.remove('active');
            });
        });
    }
}

// Difficulty Dropdown
if (difficultyToggle && difficultyDropdown && difficultyText) {
    difficultyToggle.addEventListener('click', (e) => {
        e.stopPropagation();
        difficultyDropdown.classList.toggle('active');
    });

    if (difficultyOptions && difficultyOptions.length) {
        difficultyOptions.forEach(option => {
            option.addEventListener('click', () => {
                difficultyText.textContent = option.textContent.replace('✓', '').trim();
                document.getElementById('difficultyInput').value = difficultyText.textContent;
                difficultyOptions.forEach(opt => opt.classList.remove('selected'));
                option.classList.add('selected');
                difficultyDropdown.classList.remove('active');
            });
        });
    }
}

// Visibility Dropdown
if (visibilityToggle && visibilityDropdown && visibilityText) {
    visibilityToggle.addEventListener('click', (e) => {
        e.stopPropagation();
        visibilityDropdown.classList.toggle('active');
    });

    if (visibilityOptions && visibilityOptions.length) {
        visibilityOptions.forEach(option => {
            option.addEventListener('click', () => {
                visibilityText.textContent = option.textContent.replace('✓', '').trim();
                document.getElementById('visibilityInput').value = visibilityText.textContent;
                visibilityOptions.forEach(opt => opt.classList.remove('selected'));
                option.classList.add('selected');
                visibilityDropdown.classList.remove('active');
            });
        });
    }
}

// Remove Cuisine Tag
document.addEventListener('click', (e) => {
    if (e.target.classList && e.target.classList.contains('remove-tag')) {
        e.target.parentElement.remove();
    }
});

// Add Cuisine Modal
if (addCuisineBtn && addCuisineModal && newCuisineInput && cancelCuisineBtn && saveCuisineBtn && cuisineTags) {
    addCuisineBtn.addEventListener('click', () => {
        addCuisineModal.classList.add('active');
        newCuisineInput.focus();
    });

    cancelCuisineBtn.addEventListener('click', () => {
        addCuisineModal.classList.remove('active');
        newCuisineInput.value = '';
    });

    saveCuisineBtn.addEventListener('click', () => {
        const cuisineName = newCuisineInput.value.trim();
        if (cuisineName) {
            const newTag = document.createElement('div');
            newTag.className = 'cuisine-tag';
            newTag.innerHTML = `
                ${cuisineName}
                <span class="remove-tag">×</span>
            `;
            cuisineTags.insertBefore(newTag, addCuisineBtn);
            addCuisineModal.classList.remove('active');
            newCuisineInput.value = '';
        }
    });

    newCuisineInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            saveCuisineBtn.click();
        }
    });
}

// Change Password Modal
if (changePasswordBtn && changePasswordModal && cancelPasswordBtn && savePasswordBtn) {
    changePasswordBtn.addEventListener('click', () => {
        changePasswordModal.classList.add('active');
    });

    cancelPasswordBtn.addEventListener('click', () => {
        changePasswordModal.classList.remove('active');
        const cp = document.getElementById('currentPassword');
        const np = document.getElementById('newPassword');
        const cf = document.getElementById('confirmPassword');
        if (cp) cp.value = '';
        if (np) np.value = '';
        if (cf) cf.value = '';
    });

    savePasswordBtn.addEventListener('click', () => {
        const currentPasswordEl = document.getElementById('currentPassword');
        const newPasswordEl = document.getElementById('newPassword');
        const confirmPasswordEl = document.getElementById('confirmPassword');
        const currentPassword = currentPasswordEl ? currentPasswordEl.value : '';
        const newPassword = newPasswordEl ? newPasswordEl.value : '';
        const confirmPassword = confirmPasswordEl ? confirmPasswordEl.value : '';

        if (!currentPassword || !newPassword || !confirmPassword) {
            alert('Please fill in all password fields');
            return;
        }

        if (newPassword !== confirmPassword) {
            alert('New passwords do not match');
            return;
        }

        if (newPassword.length < 6) {
            alert('Password must be at least 6 characters long');
            return;
        }

        // set hidden flag so PHP knows this is a password change
        const hidden = document.getElementById('savePasswordHidden');
        if (hidden) hidden.value = '1';

        const pwForm = document.getElementById('passwordForm');
        if (pwForm) {
            pwForm.submit();
        } else {
            // fallback: close modal and clear fields
            changePasswordModal.classList.remove('active');
            if (currentPasswordEl) currentPasswordEl.value = '';
            if (newPasswordEl) newPasswordEl.value = '';
            if (confirmPasswordEl) confirmPasswordEl.value = '';
        }
    });
}

// Delete Account Modal
if (deleteAccountBtn && deleteAccountModal && cancelDeleteBtn && confirmDeleteBtn) {
    deleteAccountBtn.addEventListener('click', () => {
        deleteAccountModal.classList.add('active');
    });

    cancelDeleteBtn.addEventListener('click', () => {
        deleteAccountModal.classList.remove('active');
    });

    confirmDeleteBtn.addEventListener('click', () => {
        // In a real app, you would send a delete request to the server
        alert('Account deletion process initiated. You will receive a confirmation email.');
        deleteAccountModal.classList.remove('active');
    });
}

// Cancel
if (cancelBtn) {
    cancelBtn.addEventListener('click', () => {
        if (confirm('Are you sure you want to cancel? Any unsaved changes will be lost.')) {
            window.location.reload();
        }
    });
}

// Form Validation
const fullNameEl = document.getElementById('fullName');
if (fullNameEl) {
    fullNameEl.addEventListener('blur', function () {
        if (!this.value.trim()) {
            this.style.borderColor = '#ff3b30';
        } else {
            this.style.borderColor = '#d2d2d7';
        }
    });
}

const emailEl = document.getElementById('email');
if (emailEl) {
    emailEl.addEventListener('blur', function () {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(this.value)) {
            this.style.borderColor = '#ff3b30';
        } else {
            this.style.borderColor = '#d2d2d7';
        }
    });
}


const footerEl = document.getElementById("footer");
if (footerEl) {
    fetch("./footer.php")
        .then(response => response.text())
        .then(data => {
            footerEl.innerHTML = data;
        })
        .catch(err => console.error("Error loading footer:", err));
}

const homebtn = document.getElementById("homebtn");
if (homebtn) homebtn.addEventListener('click', () => { window.location.href = "index.php?type=home"; });

const login = document.getElementById('Login');
if (login) login.addEventListener("click", () => { window.location.href = "login.php?type=login"; });

const signup = document.getElementById('Sign-up');
if (signup) signup.addEventListener("click", () => { window.location.href = "login.php?type=singup"; });

const find = document.getElementById('findbtn');
if (find) find.addEventListener('click', () => { window.location.href = "recipes.php"; });

const suggest = document.getElementById('Suggestbtn');
if (suggest) suggest.addEventListener('click', () => { window.location.href = "suggestions.php"; });

const favs = document.getElementById('favbtn');
if (favs) favs.addEventListener('click', () => { window.location.href = "favorites.php"; });

const planbtn = document.getElementById('planbtn');
if (planbtn) planbtn.addEventListener('click', () => { window.location.href = "planner.php"; });

const addrecpbtn = document.getElementById('addrecpbtn');
if (addrecpbtn) addrecpbtn.addEventListener('click', () => { window.location.href = "addrecipe.php"; });

const settingsbtn = document.getElementById('settingsbtn');
if (settingsbtn) settingsbtn.addEventListener('click', () => { window.location.href = "settings.php"; });




const saveProfileBtn = document.getElementById('saveProfile');
if (saveProfileBtn) {
    saveProfileBtn.addEventListener('click', function () {
        const nameEl = document.getElementById('fullName');
        const emailEl = document.getElementById('email');
        const name = nameEl ? nameEl.value.trim() : '';
        const email = emailEl ? emailEl.value.trim() : '';

        let isValid = true;

        // Clear errors
        const nameError = document.getElementById('nameError');
        const emailError = document.getElementById('emailError');
        if (nameError) nameError.textContent = '';
        if (emailError) emailError.textContent = '';

        // Name validation
        if (name === '') {
            if (nameError) nameError.textContent = 'Name is required';
            isValid = false;
        }

        // Email validation
        if (email === '') {
            if (emailError) emailError.textContent = 'Email is required';
            isValid = false;
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            if (emailError) emailError.textContent = 'Invalid email format';
            isValid = false;
        }

        if (isValid) {
            document.getElementById('skillLevelInput').value = skillLevelText.textContent;
            document.getElementById('cookingTimeInput').value = cookingTimeText.textContent;
            document.getElementById('servingsInput').value = servingsText.textContent;
            document.getElementById('difficultyInput').value = difficultyText.textContent;
            document.getElementById('visibilityInput').value = visibilityText.textContent;

            const mainForm = document.getElementById('settingsForm');
            if (mainForm) mainForm.submit();

            setTimeout(() => {
                location.reload();
            }, 1000);

        }
    });
}

// stray debug removed



