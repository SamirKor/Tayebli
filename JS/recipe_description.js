// Tab functionality
document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('.tab');
    const tabContents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', function () {
            const targetTab = this.getAttribute('data-tab');

            // Remove active class from all tabs and contents
            tabs.forEach(t => t.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));

            // Add active class to clicked tab and corresponding content
            this.classList.add('active');
            document.getElementById(targetTab).classList.add('active');
        });
    });

    // Quick action buttons functionality
    // const actionButtons = document.querySelectorAll('.action-btn');
    // actionButtons.forEach(button => {
    //     button.addEventListener('click', function () {
    //         const action = this.querySelector('span').textContent;

    //         // Special handling for Start Cooking button
    //         if (action === 'Start Cooking') {
    //             // Switch to instructions tab when Start Cooking is clicked
    //             tabs.forEach(t => t.classList.remove('active'));
    //             tabContents.forEach(c => c.classList.remove('active'));

    //             document.querySelector('[data-tab="instructions"]').classList.add('active');
    //             document.getElementById('instructions').classList.add('active');

    //             // Scroll to top of instructions
    //             document.getElementById('instructions').scrollIntoView({ behavior: 'smooth' });
    //         } else {
    //             alert(`"${action}" feature would be implemented here!`);
    //         }
    //     });
    // });

    //this should be the add to plan btn
    const planbtn = document.getElementById('AddMealPlan');
    planbtn.addEventListener('click' , function()
    {
        const params = new URLSearchParams(window.location.search);
        const recipeID = params.get('id');
        if (planbtn.classList.contains("added"))
        {
            let formdata = new FormData()
                formdata.append('recipe_id', recipeID);
                formdata.append('remove_from_recipe_planner', true);
                fetch('recipe_description.php', {
                    method: 'POST',
                    headers:{
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formdata
                }).then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('REMOVEDDDDDDDDDDDDDDD');
                        planbtn.textContent = "Add to Meal Plan";
                        planbtn.classList.remove("added");
                        planbtn.style="background-color: #f5f5f5;";
                    } else {
                        alert(data.message || 'Error updating plan');
                    }
                })
                .catch((e) => {
                    console.error('Error toggling plan:', e);
                    alert('Error updating plan. Please try again.');
            });
        }else
        {
            let formdata = new FormData()
                formdata.append('recipe_id', recipeID);
                formdata.append('add_to_recipe_planner', true);
                fetch('recipe_description.php', {
                    method: 'POST',
                    headers:{
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formdata

                }).then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('PERFECTOOOOOOOOOOOOO');
                        planbtn.textContent = "Added to Meal Plan";
                        planbtn.classList.add("added");
                        planbtn.style="background-color: #34D399;";
                    } else {
                        alert(data.message || 'Error updating plan');
                    }
                })
                .catch((e) => {
                    console.error('Error toggling plan:', e);
                    alert('Error updating plan. Please try again.');
            });
        }
        
        
    })

    // Write review button
    const writeReviewBtn = document.querySelector('.write-review-btn');
    writeReviewBtn.addEventListener('click', function () {
        // Check if user is signed in via AJAX
        fetch('recipe_description.php', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'check_login=true'
        })
            .then(response => response.json())
            .then(data => {
                if (data.logged_in) {
                    // User is logged in, proceed with showing the review form
                    tabs.forEach(t => t.classList.remove('active'));
                    tabContents.forEach(c => c.classList.remove('active'));

                    document.querySelector('[data-tab="reviews"]').classList.add('active');
                    document.getElementById('reviews').classList.add('active');

                    // Create and show a review form
                    showReviewForm();
                    writeReviewBtn.style = "display: none;";
                } else {
                    // User is not logged in, redirect to login
                    alert('Please sign in to write a review');
                    window.location.href = 'login.php?type=login';
                }
            })
            .catch((e) => {
                console.error('Error checking login status:', e);
                alert('Error checking login status. Please try again.');
            });
    });

    // Top bar action icons
    const actionIcons = document.querySelectorAll('.action-icons a');
    actionIcons.forEach(icon => {
        icon.addEventListener('click', function (e) {
            e.preventDefault();
            const action = this.getAttribute('title');

            if (action === 'Print') {
                window.print();
            } else if (action === 'Share') {
                if (navigator.share) {
                    navigator.share({
                        title: 'Creamy Pasta Carbonara',
                        text: 'Check out this delicious carbonara recipe!',
                        url: window.location.href,
                    });
                } else {
                    alert('Share this recipe with your friends!');
                }
            } else if (action === 'Save') {
                // Toggle save state
                const icon = this.querySelector('i');
                if (icon.classList.contains('far')) {
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                    alert('Recipe saved to your favorites!');
                } else {
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                    alert('Recipe removed from your favorites!');
                }
            }
        });
    });

    // Back button functionality

});

// Function to show review form
function showReviewForm() {
    // Check if form already exists
    if (document.querySelector('.review-form')) {
        return;
    }

    // Create review form
    const reviewForm = document.createElement('div');
    reviewForm.className = 'review-form';
    reviewForm.innerHTML = `
        <div class="review-form-container">
            <h3>Write Your Review</h3>
            <form method="post" action="recipe_description.php" class="review-form-element" id="review_form">
            <div class="star-rating-input">
                <span class="star" data-rating="1">☆</span>
                <span class="star" data-rating="2">☆</span>
                <span class="star" data-rating="3">☆</span>
                <span class="star" data-rating="4">☆</span>
                <span class="star" data-rating="5">☆</span>
            </div>
            <textarea id="review_text" name="comment" type="text" placeholder="Share your experience with this recipe..." class="review-textarea"></textarea>
            <div class="review-form-buttons">
                <button type="reset" class="cancel-review-btn">Cancel</button>
                <button type="submit" class="submit-review-btn">Submit Review</button>
            </div>
            </form>
        </div>
    `;

    // Add styles for the form
    const style = document.createElement('style');
    style.textContent = `
        .review-form {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .review-form-container h3 {
            margin-bottom: 15px;
            color: #101828;
        }
        
        .star-rating-input {
            display: flex;
            gap: 5px;
            margin-bottom: 15px;
        }
        
        .star-rating-input .star {
            font-size: 24px;
            cursor: pointer;
            color: #ddd;
            transition: color 0.2s;
        }
        
        .star-rating-input .star.active {
            color: #f39c12;
        }
        
        .review-textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            resize: vertical;
            min-height: 100px;
            margin-bottom: 15px;
            font-family: inherit;
        }
        
        .review-form-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        .cancel-review-btn, .submit-review-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        
        .cancel-review-btn {
            background-color: #f5f5f5;
            color: #333;
        }
        
        .submit-review-btn {
            background-color: #e74c3c;
            color: white;
        }
        
        .cancel-review-btn:hover {
            background-color: #e9e9e9;
        }
        
        .submit-review-btn:hover {
            background-color: #c0392b;
        }
    `;

    document.head.appendChild(style);

    // Insert the form before the Write a Review button
    const writeReviewBtn = document.querySelector('.write-review-btn');
    writeReviewBtn.parentNode.insertBefore(reviewForm, writeReviewBtn);

    // Add functionality to the form
    const stars = reviewForm.querySelectorAll('.star');
    let selectedRating = 0;

    stars.forEach(star => {
        star.addEventListener('click', function () {
            const rating = parseInt(this.getAttribute('data-rating'));
            selectedRating = rating;

            // Update star display
            stars.forEach((s, index) => {
                if (index < rating) {
                    s.textContent = '★';
                    s.classList.add('active');
                } else {
                    s.textContent = '☆';
                    s.classList.remove('active');
                }
            });
        });
    });

    // Cancel button
    reviewForm.querySelector('.cancel-review-btn').addEventListener('click', function () {
        reviewForm.remove();                    
        writeReviewBtn.style = "display: flex;";

    });

    // Submit button (AJAX)
    reviewForm.querySelector('.submit-review-btn').addEventListener('click', function (e) {
        e.preventDefault();

        const submitBtn = this;
        const reviewTextElem = reviewForm.querySelector('.review-textarea');
        const reviewText = reviewTextElem.value.trim();

        if (selectedRating === 0) {
            alert('Please select a rating');
            return;
        }

        if (reviewText === '') {
            alert('Please write a review');
            return;
        }

        submitBtn.disabled = true;

        const params = new URLSearchParams(window.location.search);
        const recipeID = params.get('id');

        const formData = new FormData();
        formData.append('recipe_id', recipeID);
        formData.append('rating', selectedRating);
        formData.append('comment', reviewText);

        fetch('recipe_description.php', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data && data.success) {
                    console.log(data.message);
                    // Insert the new review into the reviews list
                    const reviewList = document.querySelector('.review-list');
                    if (reviewList) {
                        const newReview = document.createElement('div');
                        newReview.className = 'review-item';
                        const stars = '★'.repeat(selectedRating) + '☆'.repeat(5 - selectedRating);
                        newReview.innerHTML = `
                        <div class="review-header">
                            <span class="reviewer-name">You</span>
                            <span class="review-date">Just now</span>
                        </div>
                        <div class="review-rating">${stars}</div>
                        <p class="review-text">${escapeHtml(reviewText)}</p>
                    `;
                        reviewList.prepend(newReview);
                    }

                    // alert('Thank you for your review! It will be displayed after approval.');
                    reviewForm.remove();

                } else {
                    alert('Unable to submit review. Please try again later.');
                }
            })
            .catch((e) => {
                console.log("falseeeee error:" + e);
            })
            .finally(() => {
                submitBtn.disabled = false;
            });
    });
}

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


// Helper to escape HTML in user input
function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

const favbtn = document.getElementById('favorite-btn');
favbtn.addEventListener('click', () => {
    const params = new URLSearchParams(window.location.search);
    const recipeID = params.get('id');

    const isFavorited = favbtn.getAttribute('fill') === 'red';

    fetch('recipe_description.php', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `toggle_favorite=true&recipe_id=${recipeID}`
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Toggle the fill color
                if (isFavorited) {
                    favbtn.setAttribute('fill', 'none');
                } else {
                    favbtn.setAttribute('fill', 'red');
                }
            } else {
                alert(data.message || 'Error updating favorite');
            }
        })
        .catch((e) => {
            console.error('Error toggling favorite:', e);
            alert('Error updating favorite. Please try again.');
        });
});