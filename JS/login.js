const buttons = document.querySelectorAll('.signinupbtns button');
buttons.forEach(btn => {
  btn.addEventListener('click', () => {
    buttons.forEach(b => b.classList.remove('active')); // remove from all
    btn.classList.add('active'); // add to clicked one
  });
});

const signinbtn = document.getElementById('signinBtn');
const signupbtn = document.getElementById('signupBtn');
const signinform = document.getElementById('sign_in_form');
const signupform = document.getElementById('sign_up_form');


function showform(form) {
  if (form === 'signin') {
    signinform.style.display = 'flex';
    signupform.style.display = 'none';
    signinbtn.classList.add('active');
    signupbtn.classList.remove('active');
  } else {
    signinform.style.display = 'none';
    signupform.style.display = 'flex';
    signupbtn.classList.add('active');
    signinbtn.classList.remove('active');
  }
}

showform('signin');
signinbtn.addEventListener('click', () => showform('signin'));
signupbtn.addEventListener('click', () => showform('signup'));

function checkpassword() {
  const password = document.getElementById('password1')?.value || '';
  const confirm = document.getElementById('password2')?.value || '';
  if (password !== confirm) {
    return false;
  }
  return true;
}

function togglepassword(id) {
  const input = document.getElementById(id);
  if (input.type === "password") {
    input.type = "text";
  } else {
    input.type = "password";
  }
}


const signupFormEl = document.getElementById('sign_up_form');
if (signupFormEl) {
  signupFormEl.addEventListener('submit',async function (e) {
   
    e.preventDefault();

    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password1').value;
    const confirmPassword = document.getElementById('password2').value;

    let isValid = true;

    // Clear previous errors
    clearErrors();

    // Name validation (2-50 characters, letters and spaces only)
    if (name === '') {
      showError('nameError', 'Name is required');
      isValid = false;
    } else if (name.length < 2 || name.length > 50) {
      showError('nameError', 'Name must be between 2-50 characters');
      isValid = false;
    } else if (!/^[a-zA-Z0-9_]+$/.test(name)) {
      showError('nameError', 'User Name can only contain letters, numbers, and underscores');
      isValid = false;
    }

    // Email validation
    if (email === '') {
      showError('emailError', 'Email is required');
      isValid = false;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      showError('emailError', 'Please enter a valid email address');
      isValid = false;
    }

    // Password validation (min 8 chars, 1 uppercase, 1 lowercase, 1 number)
    if (password === '') {
      showError('passwordError1', 'Password is required');
      isValid = false;
    } else if (password.length < 8) {
      showError('passwordError1', 'Password must be at least 8 characters');
      isValid = false;
    } else if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(password)) {
      showError('passwordError1', 'Password must contain at least one uppercase letter, one lowercase letter, and one number');
      isValid = false;
    }

    if (!checkpassword()) {
      isValid = false;
      showError('passwordError2', 'Passwords do not match');
    };

    if (isValid) {
      const formData = new FormData(this);
      // ensure server sees this as the sign-up form submission
      formData.append('createbtn', '1');
        
        try {
            // ❺ AJAX request
        const response = await fetch('login.php', {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          body: formData
        });
            
            // if server redirected (e.g. PHP sent Location header), follow it
            if (response.redirected) {
              window.location.href = response.url;
              return;
            }

            // ❻ Get response text and display any messages
            const result = await response.text();
            if (result) {
              // show server response (errors or messages)
              const el = document.getElementById('result');
              if (el) el.innerHTML = '<div style="color:green;">' + result + '</div>';
            }
            } catch (error) {
            document.getElementById('result').innerHTML = 
                '<div style="color:red;">Network error</div>';
        }
            }

  });
}

function showError(elementId, message) {
  const el = document.getElementById(elementId);
  if (el) el.textContent = message;
}

function clearErrors() {
  const ids = ['nameError', 'emailError', 'passwordError1', 'passwordError2', 'emailError1', 'passwordError'];
  ids.forEach(id => {
    const el = document.getElementById(id);
    if (el) el.textContent = '';
  });
}



// const signinFormEl = document.getElementById('sign_in_form');
// if (signinFormEl) {
//   signinFormEl.addEventListener('submit', function (e) {
  
//     e.preventDefault();
//     const email1 = document.getElementById('email2').value.trim();
//     const password1 = document.getElementById('password').value;


//     let isValid = true;

//     // Clear previous errors
//     clearErrors();

//     // Email validation
//     if (email1 === '') {
//       showError('emailError1', 'Email is required');
//       isValid = false;
//     } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email1)) {
//       showError('emailError1', 'Please enter a valid email address');
//       isValid = false;
//     }

//     // Password validation (min 8 chars, 1 uppercase, 1 lowercase, 1 number)
//     if (password1 === '') {
//       showError('passwordError', 'Password is required');
//       isValid = false;
//     } else if (password1.length < 8) {
//       showError('passwordError', 'Password must be at least 8 characters');
//       isValid = false;
//     } else if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(password1)) {
//       showError('passwordError', 'Password must contain at least one uppercase letter, one lowercase letter, and one number');
//       isValid = false;
//     }

//     if (isValid) {
//       signinFormEl.submit();
//     }
   
//   });
// }