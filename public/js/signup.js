import { $, sendData, styleProgress } from "./func.js";

// Show / Hide password
const showPassBtn = $("#show-password");
let showing = false;

showPassBtn.addEventListener("click", () => {
  showing = !showing;
  if (showing) {
    showPassBtn.querySelector("img").src = "../img/hide-password.svg";
    inputs.password.type = "text";
  } else {
    showPassBtn.querySelector("img").src = "../img/show-password.svg";
    inputs.password.type = "password";
  }
});

const inputs = {
  username: $("#username"),
  email: $("#email"),
  password: $("#password")
};

// Calculate password strength
const progress = $("#progress");

inputs.password.addEventListener("input", async () => {
  const form = new FormData($("form"));
  let result = await sendData("./fetch.php?post=password-strength", form);
  styleProgress(progress, result);
});

// Handle submissions
const signupBtn = $("#signup");
signupBtn.addEventListener("click", async () => {
  const form = new FormData($("form"));
  let result = await sendData("./fetch.php?post=signup", form);
  for (const [key, value] of Object.entries(inputs)) {
    styleSignup(result.status, value, result[key]);
  };
  if (result.status === "success") {
    setTimeout(() => {
      location.href = "./dashboard.php";
    }, 1000);
  }
});

// ----------------------------------------

// Style signup
function styleSignup(status, input, msg) {
  // Remove existing styles
  const container = clearSignup(input);

  // Check status
  if (status === "empty") return;

  // Add event listeners
  input.addEventListener("input", () => { 
    clearSignup(input);
  }, {once: true});

  // Create error message
  if (msg) {
    const errMsg = document.createElement("div");
    errMsg.classList.add("errMsg");
    errMsg.innerHTML = msg;
    container.append(errMsg);
  }
  
  // Create icon
  const icon = document.createElement("img");
  icon.classList.add("icon");
  icon.src = msg ? "./img/invalid.svg" : "./img/valid.svg";

  if (msg) {
    icon.addEventListener("mouseenter", () => {
      const setErr = container.querySelector(".errMsg");
      setErr.style.opacity = 1;
    });
  
    icon.addEventListener("mouseleave", () => {
      const setErr = container.querySelector(".errMsg");
      setErr.style.opacity = 0;
    });
  }
  
  container.append(icon);

  // Outline
  if (input.id === "password")
    input = input.closest("#password-wrapper");
  input.style.outline = msg ? "1px solid red" : "1px solid var(--sky)";
}

// Clear signup styling
function clearSignup(input) {
  const container = input.closest("div");
  container.querySelector(".icon")?.remove();
  container.querySelector(".errMsg")?.remove();
  input.closest("#password-wrapper")?.removeAttribute("style");
  input?.removeAttribute("style");
  return container;
}

