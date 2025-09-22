import { $, sendData } from "./func.js";

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
  emailUsername: $("#emailUsername"),
  password: $("#password")
};

// Handle submissions
const loginBtn = $("#login");
loginBtn.addEventListener("click", async () => {
  const form = new FormData($("form"));
  let result = await sendData("./fetch.php?post=login", form);
  styleLogin(result.status, inputs);
  if (result.status === "success") {
    const formEl = $("form");
    setTimeout(() => {
      formEl.style.opacity = 0;
      formEl.addEventListener("transitionend", function handler(e) {
        if (e.propertyName === "opacity") {
          form2fa(formEl);
          requestAnimationFrame(() => {
            formEl.style.opacity = 1;
          });
          formEl.removeEventListener("transitionend", handler);
        }
      });
    }, 500);
  }
  
  if (!result.attempts) return;
  const banMsg = $("#ban-msg") || document.createElement("div");
  banMsg.id = "ban-msg";
  if (result.attempts === "permaBan") {
    banMsg.innerHTML = `Sorry, you're permanently banned from logging in.`;
  } else if (result.attempts === "tempBan") {
    banMsg.innerHTML = `
      Sorry, you've failed too many login attempts.
      Please try again later.
    `;
  } else if (result.attempts <= 4) {
    banMsg.innerHTML = `Only ${result.attempts} remaining.`;
  }
  const container = $("form");
  container.insertBefore(banMsg, $("#login"));
});

// Handle forgot password
const forgotPass = $("#forgot-password");
forgotPass.addEventListener("click", async () => {
  const formEl = $("form");
    setTimeout(() => {
      formEl.style.opacity = 0;
      formEl.addEventListener("transitionend", function handler(e) {
        if (e.propertyName === "opacity") {
          formForgotPassword(formEl);
          requestAnimationFrame(() => {
            formEl.style.opacity = 1;
          });
          formEl.removeEventListener("transitionend", handler);
        }
      });
    }, 500);
});

// Style login
function styleLogin(status, inputs) {
  if (status === "empty") return;

  // Remove existing styles
  const container = clearLogin(inputs);

  // Decide msg
  let msg;
  if (status === "missing") {
    msg = "Sorry, we're missing an input.";
  } else if (status === "fail") {
    msg = "Sorry, those login credentials are incorrect."
  }

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

  // Outline inputs
  for (const [key, value] of Object.entries(inputs)) {
    let target = value;
    if (key === "password")
      target = value.closest("#password-wrapper");
    target.style.outline = status !== "success" ? "1px solid red" : "1px solid var(--sky)";
  }
}

// Clear login styling
function clearLogin(inputs) {
  for (const [key, value] of Object.entries(inputs)) {
    if (value.id === "password")
      value.closest("#password-wrapper");
    value?.removeAttribute("style");
  }

  const container = inputs.emailUsername.closest("div");
  container.querySelector(".icon")?.remove();
  container.querySelector(".errMsg")?.remove();
  return container;
}

// Change login form for 2fa
function form2fa(form) {
  form.innerHTML = `
    <h1>Almost there!</h1>
    <p>Check your email, we've just sent you a 6 digit code to complete your login.</p>
    <div id="codes">
      <div class="relative" id="code-wrapper">
        <input
          type="text"
          id="code"
          name="code"
          placeholder="Enter the 6 digit code..."
          maxlength="6"
          pattern="\d{6}"
          oninput="this.value=this.value.replace(/\D/g,'').slice(0,6)">
      </div>
      <button type="button" id="resend">Resend</button>
    </div>
    <div id="staySignedIns-wrapper">
      <div id="staySignedIns">
        <input 
        type="checkbox" 
        id="staySignedIn"
        name="staySignedIn"
        checked>
        <label for="staySignedIn">Stay signed in?</label>
      </div>
      <button type="button" id="continue">Continue</button>
    </div>
  `;

  const resendBtn   = $("#resend");
  const continueBtn = $("#continue");
  
  resendBtn.addEventListener("click", async () => {
    const formData = new FormData($("form"));
    const result = await sendData("./fetch.php?post=resend-code", formData);

    if (result.status === "success") {
      // Disable button
      resendBtn.disabled = true;

      // Timer for sending
      let seconds = 5;
      resendBtn.innerHTML = `${seconds}`;
      const interval = setInterval(() => {
        seconds--;
        
        if (seconds > 0) {
          resendBtn.innerHTML = `${seconds}`;
        } else {
          clearInterval(interval);
          resendBtn.innerHTML = `Sent!`;
        }
      }, 1000);

      // Reset button
      setTimeout(() => {
        resendBtn.disabled = false;
        resendBtn.innerHTML = `Resend`;
      }, 8000);
    }
    
  });

  continueBtn.addEventListener("click", async () => {
    const formData = new FormData($("form"));
    const result = await sendData("./fetch.php?post=2fa", formData);
    const inputs = { code: $("#code") };
    for (const [key, value] of Object.entries(inputs)) {
      styleCode(result.status, value, result[key]);
    };
    if (result.status === "success") {
      const headline = $("form h1");
      headline.innerHTML = `Success!`;
      setTimeout(() => {
        location.href = "./dashboard.php";
      }, 1000);
    }
  });
}

function styleCode(status, input, msg) {
  // Remove existing styles
  const container = clearCode(input);

  // Check status
  if (status === "empty") return;

  // Add event listeners
  input.addEventListener("input", () => { 
    clearCode(input);
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
function clearCode(input) {
  const container = input.closest("div");
  container.querySelector(".icon")?.remove();
  container.querySelector(".errMsg")?.remove();
  input.closest("#password-wrapper")?.removeAttribute("style");
  input?.removeAttribute("style");
  return container;
}

// Change login form for 2fa
function formForgotPassword(form) {
  form.innerHTML = `
    <h1>Forgot Password?</h1>
    <p>Enter your account email below</p>
    <div id="emails">
      <label for="email">Email</label>
      <div class="relative">
        <input
          type="email"
          id="email"
          name="email"
          placeholder="Enter your email..."
          oninput="this.value = this.value.toLowerCase().replace(' ', '')">
      </div>
    </div>
    <button type="button" id="request">Send Reset Link</button>
  `;

  const emailInput = $("#email");
  const requestBtn = $("#request");

  requestBtn.addEventListener("click", async () => {
    const formData = new FormData($("form"));
    if (formData.get("email") === "") return;
    const result = await sendData("./fetch?post=password-request&page=login", formData);
    if (result.status === "empty") return;
    styleForgotPassword(result, emailInput);
    if (result.status === "success") {
      form.innerHTML = `
        <h1>Email Sent!</h1>
        <p>
          We've sent you an email to reset your password.
          Make sure to check your spam folder if it doesn't reach your inbox.
        </p>
      `;
    }
  });
}

function styleForgotPassword(result, input) {
  // Remove existing styles
  const container = clearForgotPassword(input);

  // Create error message
  const errMsg = document.createElement("div");
  errMsg.classList.add("errMsg");
  errMsg.innerHTML = result.email;
  container.append(errMsg);

  // Create icon
  const icon = document.createElement("img");
  icon.classList.add("icon");
  icon.src = result.email ? "./img/invalid.svg" : "./img/valid.svg";

  icon.addEventListener("mouseenter", () => {
    const setErr = container.querySelector(".errMsg");
    setErr.style.opacity = 1;
  });

  icon.addEventListener("mouseleave", () => {
    const setErr = container.querySelector(".errMsg");
    setErr.style.opacity = 0;
  });

  container.append(icon);

  // Outline inpu
  input.style.outline = result.status !== "success" ? "1px solid red" : "1px solid var(--sky)";
}

// Clear login styling
function clearForgotPassword(input) {
  input?.removeAttribute("style");
  const container = input.closest("div");
  container.querySelector(".icon")?.remove();
  container.querySelector(".errMsg")?.remove();
  return container;
}