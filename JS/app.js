const routes = {
  home: './home.html',
  about: './adderecipe.html'
};

homebtn.addEventListener('click', () => {
  loadPage('home');
});

async function loadPage(page) {
  // Fetch just the content needed
  const response = await fetch(routes[page]);
  const html = await response.text();
  
  // Smooth transition
  document.getElementById('content').style.opacity = '0';
  
  setTimeout(() => {
    document.getElementById('content').innerHTML = html;
    document.getElementById('content').style.opacity = '1';
  }, 200);
  
  // Update URL without reload
  window.history.pushState({}, '', `?type=${page}`);
}