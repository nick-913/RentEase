const animatedText = document.getElementById("animated-text");
let visible = true;

setInterval(() => {
  if (visible) {
    animatedText.style.opacity = "0";
  } else {
    animatedText.style.opacity = "1";
  }
  visible = !visible;
}, 2000); // Every 2 seconds

document.addEventListener("DOMContentLoaded", function () {
  const shiftBtn = document.getElementById("shiftBtn");

  shiftBtn.addEventListener("click", function () {
    window.location.href = "../Shift room/shiftroom.php";
  });
});
