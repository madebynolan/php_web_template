import { $, $$, sendData, createPopup } from "./func.js";

// Handle edit profile button
const editProfileBtn = $("#edit-profile");
editProfileBtn.addEventListener("click", () => {
  // Create form popup
  const user = JSON.parse($("#profile")?.dataset.user ?? "");
  const formHTML = `
    <form class="relative" id="update-form" novalidate>
      <span id="close">✖</span>
      <h1>Edit Profile</h1>
      <div class="separator"></div>
      <div id="names">
        <label for="name">Display Name</label>
        <div class="relative">
          <input
            type="text"
            id="name"
            name="name"
            value="${user.name ?? ""}"
            placeholder="Enter your name...">
        </div>
      </div>
      <div id="usernames">
        <label for="username">Username</label>
        <div class="relative">
          <input
            type="text"
            id="username"
            name="username"
            value="${user.username}"
            placeholder="Choose a username..."
            oninput="this.value = this.value.toLowerCase().replace(' ', '')">
        </div>
      </div>
      <div id="emails">
        <label for="email">Email</label>
        <div class="relative">
          <input
            type="email"
            id="email"
            name="email"
            value="${user.email}"
            placeholder="Your email address..."
            oninput="this.value = this.value.toLowerCase().replace(' ', '')">
        </div>
      </div>
      <div id="locations">
        <label for="location">Location</label>
        <div class="relative">
          <input
            type="text"
            id="location"
            name="location"
            value="${user.location ?? ""}"
            placeholder="Enter your country, state or city...">
        </div>
      </div>
      <div id="bios">
        <label for="bio">Bio</label>
        <div class="relative">
          <textarea
            id="bio"
            name="bio"
            maxlength="200"
            placeholder="Enter a bio...">${user.bio ?? ""}</textarea>
        </div>
      </div>
      <button type="button" id="update">Update</button>
    </form>
  `;
  createPopup(formHTML);

  // Handle profile form
  const updateProfileBtn = $("#update");
  updateProfileBtn.addEventListener("click", async () => {
    const form = new FormData($("form"));
    const inputs = {
      name: $("#name"),
      username: $("#username"),
      email: $("#email"),
      location: $("#location"),
      bio: $("#bio")
    };

    let result = await sendData("./fetch.php?post=update-user", form);
    for (const [key, value] of Object.entries(inputs)) {
      styleUpdateProfile(result.status, value, result[key]);
    };

    if (result.status === "success") {
      setTimeout(() => {
        location.reload();
      }, 1000);
    };
  });
});

// Handle password reset
const changePassword = $("#change-password");
changePassword.addEventListener("click", () => {
  const result = sendData("./fetch.php?post=password-request&page=profile");
  if (result.status === "success")
    location.href = `/change-password.php?token=${result.token}`;
});

// Style signup
function styleUpdateProfile(status, input, msg) {
  // Remove existing styles
  const container = clearUpdateProfile(input);

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
function clearUpdateProfile(input) {
  const container = input.closest("div");
  container.querySelector(".icon")?.remove();
  container.querySelector(".errMsg")?.remove();
  input.closest("#password-wrapper")?.removeAttribute("style");
  input?.removeAttribute("style");
  return container;
}
