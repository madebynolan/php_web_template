// Query selectors
export const $ = sel => document.querySelector(sel);
export const $$ = sel => document.querySelectorAll(sel);

// Send data
export async function sendData(url, data) {
  return fetch(url, {
    method: "POST",
    body: data
  })
  .then(res => res.json());
}

// Style password progress
export function styleProgress(bar, score) {
  bar.style.width = `${score}%`;
  bar.style.background = `hsl(${score}, 100%, 50%)`;
}

// Create popup
export function createPopup(html) {
  // Prevent multiple popups
  if ($("#popup")) return;
  
  // Create popup
  const body = $("body");
  const popup = document.createElement("div");
  popup.id = "popup";
  popup.innerHTML = html;

  // Blur background
  const wrapper = $(".wrapper");
  wrapper.classList.add("blur");

  // Add popup
  body.append(popup);

  // Center popup
  popup.style.top  = `calc(50% - ${popup.clientHeight / 2}px)`;
  popup.style.left = `calc(50% - ${popup.clientWidth / 2}px)`;

  // Handle close button
  const closeBtn = $("#close");
  closeBtn.addEventListener("click", () => {
    $("#popup")?.remove();
    wrapper.classList.remove("blur");
  });
}