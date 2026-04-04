// DROPDOWN
function toggleMenu(){
  const dropdown = document.getElementById("userDropdown");
  const arrow = document.getElementById("arrow");

  dropdown.classList.toggle("show");

  arrow.style.transform = dropdown.classList.contains("show")
    ? "rotate(180deg)"
    : "rotate(0deg)";
}

// CLICK NGOÀI -> ĐÓNG
window.addEventListener("click", function(e){
  const menu = document.querySelector(".user-menu");
  const dropdown = document.getElementById("userDropdown");
  const arrow = document.getElementById("arrow");

  if(!menu.contains(e.target)){
    dropdown.classList.remove("show");
    arrow.style.transform = "rotate(0deg)";
  }
});

// DARK MODE
const toggleBtn = document.getElementById("darkModeToggle");

function applyTheme(theme){
  if(theme === "dark"){
    document.body.classList.add("dark-mode");
    toggleBtn.textContent = '☀';
  } else {
    document.body.classList.remove("dark-mode");
    toggleBtn.textContent = '🌙';
  }
}

toggleBtn.onclick = function(){
  const isDark = document.body.classList.toggle("dark-mode");
  localStorage.setItem("theme", isDark ? "dark" : "light");
  applyTheme(isDark ? "dark" : "light");
}

applyTheme(localStorage.getItem("theme") === "dark" ? "dark" : "light");