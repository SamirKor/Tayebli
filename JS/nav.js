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
