import { $, sendData, styleProgress } from "./func.js";

const inputs = {
  password: $("#password"),
  confirmPassword: $("#confirm-password")
}

// Show passwords
let showing = false;
Object.values(inputs).forEach(input => {
  const button = input.closest("#password-inputs").querySelector("button");
  button.addEventListener("click", () => {
    showing = !showing;
    if (showing) {
      button.querySelector("img").src = "../img/hide-password.svg";
      input.type = "text";
    } else {
      button.querySelector("img").src = "../img/show-password.svg";
      input.type = "password";
    }
  });
});

// Calculate password strength
const progress = $("#progress");
inputs.password.addEventListener("input", async () => {
  const form = new FormData($("form"));
  let result = await sendData("./fetch.php?post=password-strength", form);
  styleProgress(progress, result);
});

// Handle change password
const chngPassBtn = $("#change-password");
chngPassBtn.addEventListener("click", async () => {
  const form = new FormData($("form"));
  const params = new URLSearchParams(window.location.search);
  const token = params.get("token");
  let result = await sendData(`./fetch.php?post=change-password&token=${token}`, form);
  styleChangePassword(result, inputs);
  if (result.status === "success") {
    setTimeout(() => {
      location.href = "./dashboard.php";
    }, 1000);
  }
});

// Style change password
function styleChangePassword(result, inputs) {
  if (result.status === "empty") return;

  // Remove existing styles
  const container = clearChangePassword(inputs);

  // Outline and add event listeners to inputs
  for (const [key, value] of Object.entries(inputs)) {
    value.closest("#password-inputs").style.outline = result.status !== "success" ? "1px solid red" : "1px solid var(--sky)";
    value.addEventListener("input", () => {
      clearChangePassword(inputs);
    });
  }

  // Create error message
  if (result.password) {
    const errMsg = document.createElement("div");
    errMsg.classList.add("errMsg");
    errMsg.innerHTML = result.password;
    container.append(errMsg);
  }

  // Create icon
  const icon = document.createElement("img");
  icon.classList.add("icon");
  icon.src = result.password ? "./img/invalid.svg" : "./img/valid.svg";

  if (result.password) {
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
}

// Clear login styling
function clearChangePassword(inputs) {
  for (const [key, value] of Object.entries(inputs)) {
    value.closest("#password-inputs").removeAttribute("style");
  }

  const container = inputs.password.closest("div");
  container.querySelector(".icon")?.remove();
  container.querySelector(".errMsg")?.remove();
  return container;
}